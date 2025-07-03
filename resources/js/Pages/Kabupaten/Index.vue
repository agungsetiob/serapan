<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import KabupatenModal from "@/Pages/Kabupaten/Partials/KabupatenModal.vue";
import Tooltip from '@/Components/Tooltip.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';
import { formatNumber } from '@/Utils/formatters';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
  flash.value.success = null;
};

const props = defineProps({
    kabupatens: Object
});

const isModalOpen = ref(false);
const selectedKabupaten = ref(null);

function openModal(kabupaten = null) {
    selectedKabupaten.value = kabupaten;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedKabupaten.value = null;
}
</script>

<template>
    <Head title="Kabupaten" />

    <AuthenticatedLayout>
        <SuccessFlash :flash="flash" @clearFlash="clearFlash" />

        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Pagu</h2>
                        <button
                            @click="openModal()"
                            class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-500 text-white text-sm sm:text-base font-medium rounded hover:bg-indigo-700"
                        >
                            + Buat Pagu
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="px-4 py-2">Nama Kabupaten</th>
                                    <th class="px-4 py-2">Tahun</th>
                                    <th class="px-4 py-2">Pagu</th>
                                    <th class="px-4 py-2">Total Serapan</th>
                                    <th class="px-4 py-2">Persentase Serapan</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="kabupaten in kabupatens.data" :key="kabupaten.id" class="hover:bg-gray-100">
                                    <td class="px-4 py-2">{{ kabupaten.nama }}</td>
                                    <td class="px-4 py-2">{{ kabupaten.tahun_anggaran }}</td>
                                    <td class="px-4 py-2">Rp. {{ formatNumber(kabupaten.pagu) }}</td>
                                    <td class="px-4 py-2">Rp. {{ formatNumber(kabupaten.total_serapan) }}</td>
                                    <td class="px-4 py-2">{{ formatNumber(kabupaten.presentase_serapan) }}%</td>
                                    <td class="px-4 py-2">
                                        <Tooltip text="Edit Kabupaten" bgColor="bg-blue-500">
                                            <button
                                                @click="openModal(kabupaten)"
                                                class="px-2 py-1 text-sm sm:text-lg font-semibold rounded transition text-blue-600 hover:bg-blue-100"
                                            >
                                                <font-awesome-icon icon="edit" />
                                            </button>
                                        </Tooltip>
                                    </td>
                                </tr>
                                <tr v-if="kabupatens.data.length === 0">
                                    <td colspan="6" class="px-4 py-2 text-center">Belum ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        v-if="kabupatens.last_page > 1"
                        :links="kabupatens.links"
                        :meta="{ from: kabupatens.from, to: kabupatens.to, total: kabupatens.total }"
                    />
                </div>
            </div>
        </div>

        <KabupatenModal
            :show="isModalOpen"
            :kabupaten="selectedKabupaten"
            @close="closeModal"
        />
    </AuthenticatedLayout>
</template>