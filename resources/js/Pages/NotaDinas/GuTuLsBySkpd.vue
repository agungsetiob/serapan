<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreateGuTuLsModal from './Partials/CreateGutulsModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import LampiranModal from './Partials/LampiranModal.vue';
import { formatNumber } from '@/Utils/formatters';
import DeleteNotaModal from '../NotaDinas/Partials/DeleteNotaModal.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';

const props = defineProps({
    skpd: Object,
    notaDinas: Object,
    search: String,
    parentNotes: {
        type: Array,
        default: () => []
    },
    tahun: {
        type: String,
        default: new Date().getFullYear().toString()
    }
});
 
const search = ref(props.search || '');
const flash = computed(() => usePage().props.flash || {}); 
const clearFlash = () => {
  flash.value.success = null;
};

const showModal = ref(false);
const editedNota = ref(null);
const deleteModalState = ref({
  show: false,
  notaDinas: null
});

const openCreateModal = () => {
    editedNota.value = null;
    showModal.value = true;
};

const openEditModal = (nota) => {
    const parentIds = nota.dikaitkan_oleh?.length
        ? [nota.dikaitkan_oleh[0].id]
        : [];

    editedNota.value = {
        ...nota,
        parent_ids: parentIds,
        lampirans: nota.lampirans ? nota.lampirans.map(l => ({...l})) : [],
    };

    showModal.value = true;
};

const handleDeleteNota = (nota) => {
  deleteModalState.value = {
    show: true,
    notaDinas: nota
  };
};

watch(search, (val) => {
    router.get(route('nota-dinas.nota-gutuls', props.skpd.id), { search: val }, { preserveState: true, replace: true });
});

const handleSuccess = () => {
    showModal.value = false;
    router.reload({ only: ['notaDinas'] });
};

const attachmentModalState = ref({
    show: false,
    notaId: null
});

const handleViewAttachment = (nota) => {
    attachmentModalState.value = {
        show: true,
        notaId: nota.id
    };
};

const handleCloseModal = (type) => {
    if (type === 'attachment') {
        attachmentModalState.value.show = false;
    }
    if (type === 'delete') {
        deleteModalState.value.show = false;
    }
};

// const expandedGroups = ref([]);

// const groupedNotas = computed(() => {
//     const groups = {};
//     props.notaDinas.data.forEach((nota) => {
//         const parent = nota.dikaitkan_oleh?.[0] || null;
//         const key = parent ? parent.id : 'no-parent';
//         if (!groups[key]) {
//             groups[key] = { parent, children: [] };
//         }
//         groups[key].children.push(nota);
//     });
//     return groups;
// });

// function toggleGroup(id) {
//     if (expandedGroups.value.includes(id)) {
//         expandedGroups.value = expandedGroups.value.filter(x => x !== id);
//     } else {
//         expandedGroups.value.push(id);
//     }
// }
const badgeClasses = (jenis) => {
  switch(jenis) {
    case 'GU': return `bg-yellow-100 text-yellow-800`;
    case 'TU': return `bg-indigo-100 text-indigo-800`;
    case 'LS': return `bg-red-100 text-red-800`;
    default: return `bg-gray-100 text-gray-800`;
  }
}
</script>

<template>
    <Head :title="`Nota GU/TU/LS - ${skpd.nama_skpd}`" />

    <AuthenticatedLayout>
        <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-lg rounded-xl p-6 sm:p-8 space-y-6">                    
                    <h2 class="text-2xl font-extrabold text-gray-900 leading-tight">
                        {{ skpd.nama_skpd }}
                    </h2>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <SearchInput v-model:search="search" class="w-full sm:w-auto flex-grow" />
                        <button @click="openCreateModal" class="w-full sm:w-auto bg-indigo-600 text-white px-5 py-2.5 rounded-md hover:bg-indigo-700 transition-colors duration-200 flex items-center justify-center gap-2 font-medium">
                           + Buat Nota
                        </button>
                    </div>

                    <div v-if="props.notaDinas.data.length === 0" class="text-center text-gray-500 py-10">
                        <p class="text-lg">Belum ada nota GU/TU/LS yang tercatat.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                        <div
                            v-for="nota in props.notaDinas.data"
                            :key="nota.id"
                            class="px-4 py-2 flex flex-col sm:flex-row justify-between items-start sm:items-center hover:bg-gray-50 transition-colors duration-150"
                        >
                            <div class="mb-2 sm:mb-0 flex-grow">
                                <h4 class="font-medium text-blue-700 text-lg">
                                    {{ nota.nomor_nota }} 
                                    <span 
                                        :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-2', badgeClasses(nota.jenis)]"
                                    >
                                        {{ nota.jenis }}
                                    </span>
                                </h4>
                                <p class="text-gray-700">{{ nota.perihal }}</p>
                                <p class="text-sm text-gray-500">Anggaran: <span class="text-green-500">Rp. {{ formatNumber(nota.anggaran) }}</span></p>

                                <div v-if="nota.parents && nota.parents.length > 0" class="mt-1 text-sm text-gray-600">
                                    <span class="font-semibold">Dari Nota:</span>
                                    <ul class="list-disc list-inside ml-4">
                                        <li v-for="parent in nota.parents" :key="parent.id" class="my-0.5">
                                            {{ parent.nomor_nota }} - {{ parent.perihal }} 
                                            <span class="text-xs text-red-500">(Sisa: Rp. {{ formatNumber(parent.sisa_anggaran) }})</span>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="mt-2 text-sm text-gray-500">
                                    Tidak dikaitkan oleh nota lain.
                                </div>
                            </div>
                            <div class="flex-shrink-0 flex items-center space-x-2 mt-3 sm:mt-0">
                                <Tooltip text="Lampiran" bgColor="bg-purple-600">
                                    <button @click="handleViewAttachment(nota)" class="text-purple-600 hover:bg-purple-100 py-1 px-2 rounded-md transition-colors duration-200">
                                        <font-awesome-icon :icon="['fas','paperclip']" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Edit" bgColor="bg-blue-600">
                                    <button @click="openEditModal(nota)" class="text-blue-600 hover:bg-blue-100 py-1 px-2 rounded-md transition-colors duration-200">
                                        <font-awesome-icon icon="edit" />
                                    </button>
                                </Tooltip>
                                <Tooltip text="Hapus" bgColor="bg-red-600">
                                    <button @click="handleDeleteNota(nota)" class="text-red-600 hover:bg-red-100 py-1 px-2 rounded-md transition-colors duration-200">
                                        <font-awesome-icon icon="trash-alt" />
                                    </button>
                                </Tooltip>
                            </div>
                        </div>
                    </div>

                    <Pagination v-if="notaDinas.last_page > 1"
                        :links="notaDinas.links"
                        :meta="{ from: notaDinas.from, to: notaDinas.to, total: notaDinas.total }"
                        class="mt-6"
                    />
                </div>
            </div>
        </div>

        <CreateGuTuLsModal
            :show="showModal"
            :isEdit="!!editedNota" 
            :notaData="editedNota"
            :skpd="skpd"
            :parent-notes="parentNotes"
            :tahun="tahun"
            @close="showModal = false"
            @success="handleSuccess"
        />
        <LampiranModal
            :show="attachmentModalState.show"
            :notaId="attachmentModalState.notaId"
            @close="() => handleCloseModal('attachment')"
        />
        <DeleteNotaModal
            :show="deleteModalState.show"
            :notaDinas="deleteModalState.notaDinas"
            @close="() => handleCloseModal('delete')"
            @success="handleSuccess"
        />  
    </AuthenticatedLayout>
</template>