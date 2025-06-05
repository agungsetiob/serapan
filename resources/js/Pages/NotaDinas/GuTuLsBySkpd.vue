<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';

const props = defineProps({
    skpd: Object,
    notaDinas: Object,
    search: String,
});

const search = ref(props.search || '');
watch(search, (val) => {
    router.get(route('skpds.nota-gutuls', props.skpd.id), { search: val }, { preserveState: true, replace: true });
});

const handleDelete = (id) => {
    if (confirm('Yakin ingin menghapus nota ini?')) {
        router.delete(route('nota-dinas.destroy', id));
    }
};
</script>

<template>
    <Head :title="`Nota GU/TU/LS - ${skpd.nama_skpd}`" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">
                            Nota GU/TU/LS - {{ skpd.nama_skpd }}
                        </h2>
                        <div class="w-full sm:w-96">
                            <SearchInput v-model:search="search" />
                        </div>
                    </div>
                    <Link
                        :href="route('nota-dinas.create-gutuls', skpd.id)"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                    >
                        + Tambah Nota GU/TU/LS
                    </Link>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4" v-if="notaDinas.data.length === 0">
                    <p class="text-gray-600 text-center">Belum ada nota GU/TU/LS</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="nota in notaDinas.data"
                        :key="nota.id"
                        class="bg-white p-4 shadow rounded-lg hover:shadow-md transition"
                        >
                        <div class="flex justify-between items-center">
                            <div>
                            <h3 class="text-lg font-bold text-blue-600">
                                {{ nota.nomor_nota }} - <span class="text-sm font-normal text-gray-500">{{ nota.jenis }}</span>
                            </h3>
                            <p class="text-gray-700">{{ nota.perihal }}</p>
                            <p class="text-sm text-gray-500">Anggaran: Rp{{ nota.anggaran.toLocaleString('id-ID') }}</p>

                            <!-- Tampilkan parent nota -->
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

                            <div class="flex flex-col gap-1 text-center">
                                <Link :href="route('nota-dinas.show', nota.id)" class="text-sm text-blue-600 hover:underline">Lampiran</Link>
                                <Link :href="route('nota-dinas.edit', nota.id)" class="text-sm text-yellow-600 hover:underline">Edit</Link>
                                <button @click="hapus(nota.id)" class="text-sm text-red-600 hover:underline">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <Pagination
                        :links="notaDinas.links"
                        :meta="{ from: notaDinas.from, to: notaDinas.to, total: notaDinas.total }"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
