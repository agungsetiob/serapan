<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreateGuTuLsModal from './Partials/CreateGutulsModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import LampiranModal from './Partials/LampiranModal.vue';
import DeleteNotaModal from '../NotaDinas/Partials/DeleteNotaModal.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';
import { formatNumber, formatDate } from '@/Utils/formatters';

const props = defineProps({
    skpd: Object,
    notaDinas: Object,
    search: String,
    parentNotes: Array,
    tahun: String,
    jenisOptions: Array,
    jenisSelected: String,
    is_belanja_modal: String,
    kegiatanOptions: Array,
    subKegiatanOptions: Array,
});

const search = ref(props.search || '');
const flash = computed(() => usePage().props.flash || {});
const clearFlash = () => { flash.value.success = null };

const showModal = ref(false);
const editedNota = ref(null);
const deleteModalState = ref({ show: false, notaDinas: null });
const attachmentModalState = ref({ show: false, notaId: null });

const openCreateModal = () => {
    editedNota.value = null;
    showModal.value = true;
};

const openEditModal = (nota) => {
    editedNota.value = {
        ...nota,
        parent_ids: nota.dikaitkan_oleh?.length ? [nota.dikaitkan_oleh[0].id] : [],
        lampirans: nota.lampirans ? nota.lampirans.map(l => ({ ...l })) : []
    };
    showModal.value = true;
};

const handleDeleteNota = (nota) => {
    deleteModalState.value = { show: true, notaDinas: nota };
};

const handleViewAttachment = (nota) => {
    attachmentModalState.value = { show: true, notaId: nota.id };
};

const handleCloseModal = (type) => {
    if (type === 'attachment') attachmentModalState.value.show = false;
    if (type === 'delete') deleteModalState.value.show = false;
};

const handleSuccess = () => {
    showModal.value = false;
    router.reload({ only: ['notaDinas'] });
};

watch(search, (val) => {
    router.get(route('nota-dinas.nota-gutuls', props.skpd.id), { search: val }, { preserveState: true, replace: true });
});

const badgeClasses = (jenis) => {
    const classes = {
        GU: 'bg-yellow-100 text-yellow-800',
        TU: 'bg-indigo-100 text-indigo-800',
        LS: 'bg-red-100 text-red-800'
    };
    return classes[jenis] || 'bg-gray-100 text-gray-800';
};

const jenis = ref(props.jenisSelected || '');

const handleJenisChange = (newJenis) => {
    jenis.value = newJenis;
    router.get(route('nota-dinas.nota-gutuls', props.skpd.id),
        {
            search: search.value,
            jenis: newJenis
        },
        { preserveState: true, replace: true }
    );
};
const isBelanjaModal = ref(props.is_belanja_modal || '');
const handleBelanjaModalChange = (checked) => {
    isBelanjaModal.value = checked ? 'true' : '';
    router.get(route('nota-dinas.nota-gutuls', props.skpd.id),
        {
            search: search.value,
            jenis: jenis.value,
            is_belanja_modal: isBelanjaModal.value,
        },
        { preserveState: true, replace: true }
    );
};
</script>

<template>

    <Head :title="`Nota GU/TU/LS - ${skpd.nama_skpd}`" />
    <AuthenticatedLayout>
        <SuccessFlash :flash="flash" @clearFlash="clearFlash" />

        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto lg:px-6 pb-4 space-y-4">
                <div
                    class="bg-gradient-to-br from-indigo-600 to-blue-500 text-white rounded-lg p-6 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 class="text-3xl font-bold">{{ skpd.nama_skpd }}</h2>
                        <p class="text-sm mt-1 opacity-80">Tahun: {{ tahun }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 mt-4 space-y-4">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <SearchInput v-model:search="search" class="w-full sm:flex-1" placeholder="Cari no/perihal..."/>
                        <div class="flex items-center space-x-1">
                            <input id="belanjaModal" type="checkbox" :checked="isBelanjaModal === 'true'"
                                @change="e => handleBelanjaModalChange(e.target.checked)"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <label for="belanjaModal" class="text-sm text-gray-700">Belanja Modal</label>
                        </div>
                        <div>
                            <select v-model="jenis" @change="handleJenisChange(jenis)"
                                class="w-full sm:w-30 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Jenis</option>
                                <option v-for="item in jenisOptions" :key="item" :value="item">{{ item }}</option>
                            </select>
                        </div>
                        <div>
                            <button @click="openCreateModal"
                                class="w-full sm:w-auto px-3 py-2 bg-indigo-500 text-white text-sm font-medium rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                                + Buat Nota
                            </button>
                        </div>
                    </div>
                    <!-- NOTA LIST -->
                    <div v-if="notaDinas.data.length === 0" class="text-center text-gray-500 py-10">
                        <p class="text-lg">Belum ada nota GU/TU/LS yang tercatat.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="nota in notaDinas.data" :key="nota.id"
                            class="rounded-lg shadow-lg border hover:border-cyan-500 p-4 flex flex-col justify-between hover:shadow-md transition">

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-lg font-semibold text-indigo-700">
                                        {{ nota.nomor_nota }}
                                    </h4>
                                    <span
                                        :class="['text-xs px-2 py-0.5 rounded-full font-medium', badgeClasses(nota.jenis)]">
                                        {{ nota.jenis }}
                                    </span>
                                </div>
                                <p class="text-gray-700">{{ nota.perihal }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span class="text-green-600">Rp. {{ formatNumber(nota.anggaran) }}</span>
                                    <span class="text-gray-700 mx-2">|</span>
                                    <span class="text-gray-700">{{ formatDate(nota.tanggal_pengajuan) }}</span>
                                    <span v-if="nota.is_belanja_modal" class="text-gray-700 mx-2">|</span>
                                    <span v-if="nota.is_belanja_modal"
                                        class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full shadow-sm">
                                        Belanja Modal
                                    </span>
                                </p>
                                <!-- Parent Info -->
                                <div class="mt-2 text-sm text-gray-600">
                                    <span class="font-medium">Dari Nota:</span>
                                    <div v-if="nota.parents && nota.parents.length > 0" class="mt-1">
                                        <ul class="list-disc ml-4">
                                            <li v-for="parent in nota.parents" :key="parent.id">
                                                {{ parent.nomor_nota }} - {{ parent.perihal }}
                                                <span class="text-xs text-red-500">
                                                    (Sisa: Rp. {{ formatNumber(parent.sisa_anggaran) }})
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end mt-2 gap-1">
                                <Tooltip text="Lampiran" bgColor="bg-purple-600">
                                    <button @click="handleViewAttachment(nota)"
                                        class="text-purple-600 hover:bg-purple-100 p-2 rounded">
                                        <font-awesome-icon :icon="['fas', 'paperclip']" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Edit" bgColor="bg-blue-600">
                                    <button @click="openEditModal(nota)"
                                        class="text-blue-600 hover:bg-blue-100 p-2 rounded">
                                        <font-awesome-icon icon="edit" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Hapus" bgColor="bg-red-600">
                                    <button @click="handleDeleteNota(nota)"
                                        class="text-red-600 hover:bg-red-100 p-2 rounded">
                                        <font-awesome-icon icon="trash-alt" />
                                    </button>
                                </Tooltip>
                            </div>
                        </div>
                    </div>
                    <!-- PAGINATION -->
                    <Pagination v-if="notaDinas.last_page > 1" :links="notaDinas.links"
                        :meta="{ from: notaDinas.from, to: notaDinas.to, total: notaDinas.total }" class="mt-8" />
                </div>
            </div>
        </div>

        <!-- MODALS -->
        <CreateGuTuLsModal :show="showModal" :isEdit="!!editedNota" :notaData="editedNota" :skpd="skpd"
            :parent-notes="parentNotes" :tahun="tahun" :kegiatan-options="props.kegiatanOptions"
            :sub-kegiatan-options="props.subKegiatanOptions" @close="showModal = false" @success="handleSuccess" />

        <LampiranModal :show="attachmentModalState.show" :notaId="attachmentModalState.notaId"
            @close="() => handleCloseModal('attachment')" />

        <DeleteNotaModal :show="deleteModalState.show" :notaDinas="deleteModalState.notaDinas"
            @close="() => handleCloseModal('delete')" @success="handleSuccess" />
    </AuthenticatedLayout>
</template>
