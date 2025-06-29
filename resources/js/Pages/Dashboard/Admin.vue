<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Charts from './Charts/Charts.vue';
import RekapNotaDinas from './Partials/RekapNotaDinas.vue';

const props = defineProps({
    presentaseSerapan: Number,
    totalSkpds: Number,
    notaDinas: Number,
    totalSerapan: Number,
});

</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-20 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="mb-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Dashboard</h1>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- SKPD Card -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-l-4 border-l-red-500 border-gray-100 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">SKPD</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalSkpds }}</p>
                            </div>
                            <div class="bg-red-50 p-3 rounded-lg group-hover:bg-red-100 transition-colors">
                                <font-awesome-icon icon="fas fa-building" class="text-red-500 text-xl"/>
                            </div>
                        </div>
                    </div>

                    <!-- Nota Dinas Card -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-l-4 border-l-indigo-500 border-gray-100 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nota Dinas</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ notaDinas }}</p>
                            </div>
                            <div class="bg-indigo-50 p-3 rounded-lg group-hover:bg-indigo-100 transition-colors">
                                <font-awesome-icon icon="fas fa-file-zipper" class="text-indigo-500 text-xl"/>
                            </div>
                        </div>
                    </div>

                    <!-- Total Serapan Card -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-l-4 border-l-orange-500 border-gray-100 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Serapan</p>
                                <p class="text-xl font-bold text-gray-800 mt-1">Rp. {{ new Intl.NumberFormat('id-ID').format(totalSerapan) }}</p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-lg group-hover:bg-orange-100 transition-colors">
                                <font-awesome-icon :icon="['fas', 'wallet']" class="text-orange-500 text-xl"/>
                            </div>
                        </div>
                    </div>

                    <!-- Persentase Serapan Card -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-l-4 border-l-green-500 border-gray-100 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Persentase Serapan</p>
                                <div class="flex items-end mt-1">
                                    <p class="text-2xl font-bold text-gray-800">{{ presentaseSerapan }}%</p>
                                </div>
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg group-hover:bg-green-100 transition-colors">
                                <font-awesome-icon icon="fas fa-square-check" class="text-green-500 text-xl"/>
                            </div>
                        </div>
                    </div>
                </div>
                <Charts class="mb-4"/>
                <RekapNotaDinas/>
            </div>
        </div>
    </AuthenticatedLayout>
</template>