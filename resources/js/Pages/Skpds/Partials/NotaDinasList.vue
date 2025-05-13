<script setup>
import { ref } from 'vue';
import Tooltip from '@/Components/Tooltip.vue';

const props = defineProps({
    notaDinas: {
        type: Array,
        default: () => []
    },
    subKegiatan: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['edit', 'delete', 'view-attachment']);

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const handleEdit = (nota) => {
    emit('edit', nota);
};

const handleDelete = (nota) => {
    emit('delete', nota);
};

const handleViewAttachment = (nota) => {
    emit('view-attachment', nota);
};
</script>

<template>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nilai
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ formatNumber(nota.nilai) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <Tooltip text="Lampiran" bgColor="bg-gray-500">
                                    <button 
                                        @click="handleViewAttachment(nota)" 
                                        class="px-2 py-1 text-xs sm:text-sm rounded text-gray-600 hover:bg-gray-200"
                                    >
                                        <font-awesome-icon icon="paperclip" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Edit" bgColor="bg-blue-400">
                                    <button 
                                        @click="handleEdit(nota)" 
                                        class="px-2 py-1 text-xs sm:text-sm rounded text-blue-400 hover:bg-blue-100"
                                    >
                                        <font-awesome-icon icon="edit" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Hapus" bgColor="bg-red-500">
                                    <button 
                                        @click="handleDelete(nota)" 
                                        class="px-2 py-1 text-xs sm:text-sm rounded text-red-600 hover:bg-red-100"
                                    >
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
</template>