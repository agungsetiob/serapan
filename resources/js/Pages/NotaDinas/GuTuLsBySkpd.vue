<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreateGuTuLsModal from './Partials/CreateGutulsModal.vue';
import Tooltip from '@/Components/Tooltip.vue';

const props = defineProps({
    skpd: Object,
    notaDinas: Object,
    search: String,
    parentNotes: {
        type: Array,
        default: () => []
    },
    tahun: {
        type: String,
        default: new Date().getFullYear().toString()
    }
});

const search = ref(props.search || '');
const showModal = ref(false);
const editedNota = ref(null);

const openCreateModal = () => {
    editedNota.value = null;
    showModal.value = true;
};

const openEditModal = (nota) => {
    editedNota.value = nota;
    showModal.value = true;
};


watch(search, (val) => {
    router.get(route('skpds.nota-gutuls', props.skpd.id), { search: val }, { preserveState: true, replace: true });
});

const handleDelete = (id) => {
    if (confirm('Yakin ingin menghapus nota ini?')) {
        router.delete(route('nota-dinas.destroy', id));
    }
};

const handleSuccess = () => {
    showModal.value = false;
    router.reload({ only: ['notaDinas'] });
};
</script>

<template>
    <Head :title="`Nota GU/TU/LS - ${skpd.nama_skpd}`" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">

                <!-- Card Utama -->
                <div class="bg-white shadow rounded-lg p-6 space-y-6">
                    
                    <!-- Header -->
                    <h2 class="text-2xl font-bold text-gray-800">
                        Nota GU/TU/LS - {{ skpd.nama_skpd }}
                    </h2>

                    <!-- Pencarian & Tambah Nota (sejajar) -->
                    <div class="flex justify-between items-center">
                        <SearchInput v-model:search="search" class="flex-grow" />
                        <button @click="openCreateModal" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                            + Buat Nota
                        </button>
                    </div>

                    <!-- Daftar Nota -->
                    <div v-if="notaDinas.data.length === 0" class="text-center text-gray-600">
                        Belum ada nota GU/TU/LS
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="nota in notaDinas.data"
                            :key="nota.id"
                            class="border border-gray-300 p-4 rounded-lg hover:shadow-md transition flex justify-between items-center"
                        >
                            <div>
                                <h3 class="text-lg font-bold text-blue-600">
                                    {{ nota.nomor_nota }} - <span class="text-sm font-normal text-gray-500">{{ nota.jenis }}</span>
                                </h3>
                                <p class="text-gray-700">{{ nota.perihal }}</p>
                                <p class="text-sm text-gray-500">Anggaran: Rp{{ nota.anggaran.toLocaleString('id-ID') }}</p>

                                <!-- Tampilkan Parent Nota -->
                                <div v-if="nota.dikaitkan_oleh && nota.dikaitkan_oleh.length > 0" class="mt-2 text-sm text-gray-600">
                                    <p class="font-semibold">Untuk Nota:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li v-for="parent in nota.dikaitkan_oleh" :key="parent.id">
                                            <span class="font-medium">{{ parent.nomor_nota }} - {{ parent.perihal }}</span>
                                            <span v-if="parent.sub_kegiatan"> - {{ parent.sub_kegiatan.nama }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="px-4 py-2 whitespace-nowrap text-sm font-medium space-x-1">
                                <Tooltip text="Lampiran" bgColor="bg-purple-600">
                                    <Link :href="route('nota-dinas.show', nota.id)" class="text-purple-600 px-2 py-1 rounded hover:bg-purple-200 transition-colors">
                                        <font-awesome-icon :icon="['fas', 'paperclip']" />
                                    </Link>
                                </Tooltip>

                                <Tooltip text="Edit" bgColor="bg-blue-600">
                                    <button @click="openEditModal(nota)" class="text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                                        <font-awesome-icon icon="edit" />
                                    </button>
                                </Tooltip>

                                <Tooltip text="Hapus" bgColor="bg-red-600">
                                    <button @click="handleDelete(nota.id)" class="text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors">
                                        <font-awesome-icon icon="trash-alt" />
                                    </button>
                                </Tooltip>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <Pagination
                        :links="notaDinas.links"
                        :meta="{ from: notaDinas.from, to: notaDinas.to, total: notaDinas.total }"
                    />
                </div>
            </div>
        </div>

        <CreateGuTuLsModal
            :show="showModal"
            :isEdit="!!editedNota" 
            :notaData="editedNota"
            :skpd="skpd"
            :parent-notes="parentNotes"
            :tahun="tahun"
            @close="showModal = false"
            @success="handleSuccess"
        />
    </AuthenticatedLayout>
</template>
