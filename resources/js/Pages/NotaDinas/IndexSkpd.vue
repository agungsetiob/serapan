<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';

const search = ref('');

// Debounce the search input to prevent too many requests
let searchTimeout = null;
watch(search, (val) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(route('nota-skpd.index'), { search: val }, { preserveState: true, replace: true });
    }, 300); // Wait for 300ms after the user stops typing
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

    <Head title="Daftar SKPD Nota Dinas" />

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

                    <div v-if="skpds.data.length > 0"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link v-for="skpd in skpds.data" :key="skpd.id" :href="route('nota-skpd.show', skpd.id)"
                            preserve-scroll
                            class="block bg-white rounded-xl shadow-md p-6 transform transition duration-300 hover:scale-105 hover:shadow-xl group border border-gray-200 hover:border-blue-300">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition duration-300">
                                    <font-awesome-icon :icon="['fas', 'building']"
                                        class="text-2xl text-blue-600 group-hover:text-blue-700 transition duration-300" />
                                </div>
                                <div>
                                    <p
                                        class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition duration-300">
                                        {{ skpd.nama_skpd }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="py-12 text-center bg-white rounded-xl shadow-md mt-8">
                        <font-awesome-icon :icon="['fas', 'box-open']" class="text-6xl text-gray-300 mb-4" />
                        <p class="text-xl font-medium text-gray-600">
                            Tidak ada SKPD ditemukan
                        </p>
                        <p class="mt-2 text-gray-500">
                            Coba sesuaikan pencarian Anda atau tambahkan SKPD baru.
                        </p>
                    </div>

                    <div class="mt-4">
                        <Pagination v-if="skpds.last_page > 1" :links="skpds.links"
                            :meta="{ from: skpds.from, to: skpds.to, total: skpds.total }" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>