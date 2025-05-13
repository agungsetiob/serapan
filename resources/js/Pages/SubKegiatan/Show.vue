<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Tooltip from '@/Components/Tooltip.vue';

const props = defineProps({
    subKegiatan: Object,
});

const notaDinas = props.subKegiatan.nota_dinas || [];

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <Head :title="`${subKegiatan.nama}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sub Kegiatan: {{ subKegiatan.nama }}
            </h2>
        </template>

        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Informasi Sub Kegiatan -->
                        <div class="mb-8 p-4 border-b">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                {{ subKegiatan.nama }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Pagu:</span> Rp {{ formatNumber(subKegiatan.pagu) }}
                                </div>
                                <div>
                                    <span class="font-medium">Serapan:</span> Rp {{ formatNumber(subKegiatan.total_serapan) }}
                                </div>
                                <div>
                                    <span class="font-medium">Presentase Serapan:</span>
                                    <span :class="{
                                        'text-green-600': subKegiatan.presentase_serapan >= 80,
                                        'text-yellow-600': subKegiatan.presentase_serapan >= 50 && subKegiatan.presentase_serapan < 80,
                                        'text-red-600': subKegiatan.presentase_serapan < 50
                                    }">
                                        {{ subKegiatan.presentase_serapan }}%
                                    </span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                <div
                                    class="h-2.5 rounded-full transition-all duration-500"
                                    :class="{
                                        'bg-green-500': subKegiatan.presentase_serapan >= 80,
                                        'bg-yellow-500': subKegiatan.presentase_serapan >= 50 && subKegiatan.presentase_serapan < 80,
                                        'bg-red-500': subKegiatan.presentase_serapan < 50
                                    }"
                                    :style="{ width: subKegiatan.presentase_serapan + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- Daftar Nota Dinas -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Daftar Nota Dinas
                                </h3>
                            </div>

                            <div v-if="notaDinas.length" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nomor
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Perihal
                                            </th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="nota in notaDinas" :key="nota.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ nota.nomor_nota }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(nota.tanggal_pengajuan) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ nota.perihal }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <Tooltip text="Lampiran" bgColor="bg-gray-500">
                                                        <button @click="openLampiranModal(nota)" class="px-2 py-1 text-xs sm:text-sm rounded text-gray-600 hover:bg-gray-200">
                                                        <font-awesome-icon icon="paperclip" />
                                                        </button>
                                                    </Tooltip>
                                                    <Tooltip text="Edit" bgColor="bg-blue-400">
                                                        <button @click="openEditModal(nota)" class="px-2 py-1 text-xs sm:text-sm rounded text-blue-400 hover:bg-blue-100">
                                                        <font-awesome-icon icon="edit" />
                                                        </button>
                                                    </Tooltip>
                                                    <Tooltip text="Hapus" bgColor="bg-red-500">
                                                        <button @click="onDelete(nota)" class="px-2 py-1 text-xs sm:text-sm rounded text-red-600 hover:bg-red-100">
                                                            <font-awesome-icon icon="trash-can"/>
                                                        </button>
                                                    </Tooltip>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                Belum ada nota dinas untuk sub kegiatan ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
