<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';

const search = ref('');
watch(search, (val) => {
  router.get(route('nota-dinas.index'), { search: val }, { preserveState: true, replace: true });
});

const props = defineProps({
    skpds: Object,
});
</script>

<template>
    <Head title="Nota Dinas" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar SKPD</h2>
                        <div class="w-full sm:w-2/3">
                            <SearchInput v-model:search="search" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="skpd in skpds.data"
                            :key="skpd.id"
                            class="bg-gray-100 rounded-lg shadow-lg p-6 flex items-center gap-4 hover:shadow-xl transition duration-300"
                        >
                            <font-awesome-icon :icon="['fas', 'file-zipper']" class="text-3xl text-red-500" />
                            <Link
                                :href="route('nota-dinas.nota-gutuls', skpd.id)"
                                preserve-scroll
                                class="text-blue-600 font-semibold hover:text-green-600"
                                >
                                {{ skpd.nama_skpd }}
                            </Link>
                            <span class="text-sm text-gray-500">{{ skpd.kode_skpd }}</span> 

                        </div>
                        <div v-if="skpds.data.length === 0" class="col-span-full text-center text-gray-500">
                            Belum ada SKPD
                        </div>
                    </div>

                    <div class="mt-8">
                        <Pagination v-if="skpds.last_page > 1"
                            :links="skpds.links"
                            :meta="{ from: skpds.from, to: skpds.to, total: skpds.total }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
