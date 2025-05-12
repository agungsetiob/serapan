<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Charts from './Charts/Charts.vue';

const props = defineProps({
    presentaseSerapan: Number,
    totalSkpds: Number,
    notaDinas: Number,
    totalSerapan: Number
});

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-20 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">

                    <!-- Jumlah SKPD -->
                    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow-md">
                        <font-awesome-icon icon="fas fa-building" class="text-3xl text-red-700"/>
                        <p class="text-lg font-medium mt-2">SKPD</p>
                        <p class="text-2xl font-bold">{{ totalSkpds }}</p>
                    </div>

                    <!-- Jumlah Nota Dinas -->
                    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow-md">
                        <font-awesome-icon icon="fas fa-file-zipper" class="text-3xl text-indigo-600"/>
                        <p class="text-lg font-medium mt-2">Nota Dinas</p>
                        <p class="text-2xl font-bold">{{ notaDinas }}</p>
                    </div>

                    <!-- total serapan -->
                    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow-md">
                        <font-awesome-icon :icon="['fas', 'wallet']" class="text-3xl text-orange-500"/>
                        <p class="text-lg font-medium mt-2">Total Serapan</p>
                        <p class="text-2xl font-bold">Rp. {{ new Intl.NumberFormat('id-ID').format(totalSerapan) }}</p>
                    </div>

                    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow-md">
                        <font-awesome-icon icon="fas fa-square-check" class="text-3xl text-green-700"/>
                        <p class="text-lg font-medium mt-2">Presentase Serapan</p>
                        <p class="text-2xl font-bold">{{ presentaseSerapan }}%</p>
                    </div>
                </div>
                <div class="pt-6">
                    <Charts/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>