<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';

const search = ref('');

let searchTimeout = null;
watch(search, (val) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('nota-dinas.index'), { search: val }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const props = defineProps({
    skpds: Object,
    initialSearch: {
        type: String,
        default: '',
    },
});

if (props.initialSearch) {
    search.value = props.initialSearch;
}
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

                    <div v-if="skpds.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="skpd in skpds.data"
                            :key="skpd.id"
                            :href="route('nota-dinas.nota-gutuls', skpd.id)"
                            preserve-scroll
                            class="block border-gray-200 border rounded-lg shadow-lg p-6 hover:shadow-xl group transform transition duration-300 hover:scale-105 hover:border-red-300"
                        >
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-red-100 rounded-lg group-hover:bg-red-200 transition duration-300">
                                    <font-awesome-icon :icon="['fas', 'file-zipper']" class="text-2xl text-red-500" />
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-gray-800 group-hover:text-red-600 transition">
                                        {{ skpd.nama_skpd }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ skpd.kode_skpd }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="col-span-full text-center text-gray-500">
                        Belum ada SKPD
                    </div>

                    <div class="mt-10">
                        <Pagination
                            v-if="skpds.last_page > 1"
                            :links="skpds.links"
                            :meta="{ from: skpds.from, to: skpds.to, total: skpds.total }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
