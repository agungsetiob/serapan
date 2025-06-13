<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreateGuTuLsModal from './Partials/CreateGutulsModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import LampiranModal from './Partials/LampiranModal.vue';
import { formatCurrency } from '@/Utils/formatters';

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
const showModal = ref(false);
const editedNota = ref(null);

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
    };

    showModal.value = true;
};

watch(search, (val) => {
    router.get(route('nota-dinas.nota-gutuls', props.skpd.id), { search: val }, { preserveState: true, replace: true });
});

const handleDelete = (id) => {
    if (confirm('Yakin ingin menghapus nota ini?')) {
        router.delete(route('nota-dinas.destroy', id));
    }
};

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
};

const expandedGroups = ref([]);

const groupedNotas = computed(() => {
    const groups = {};
    props.notaDinas.data.forEach((nota) => {
        const parent = nota.dikaitkan_oleh?.[0] || null;
        const key = parent ? parent.id : 'no-parent';
        if (!groups[key]) {
            groups[key] = { parent, children: [] };
        }
        groups[key].children.push(nota);
    });
    return groups;
});

function toggleGroup(id) {
    if (expandedGroups.value.includes(id)) {
        expandedGroups.value = expandedGroups.value.filter(x => x !== id);
    } else {
        expandedGroups.value.push(id);
    }
}
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
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-lg rounded-xl p-6 sm:p-8 space-y-6">                    
                    <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Nota GU/TU/LS - {{ skpd.nama_skpd }}
                    </h2>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <SearchInput v-model:search="search" class="w-full sm:w-auto flex-grow" />
                        <button @click="openCreateModal" class="w-full sm:w-auto bg-indigo-600 text-white px-5 py-2.5 rounded-md hover:bg-indigo-700 transition-colors duration-200 flex items-center justify-center gap-2 font-medium">
                           + Buat Nota
                        </button>
                    </div>

                    <div v-if="Object.keys(groupedNotas).length === 0" class="text-center text-gray-500 py-10">
                        <p class="text-lg">Belum ada nota GU/TU/LS yang tercatat.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="(group, key) in groupedNotas"
                            :key="key"
                            class="border border-gray-200 rounded-lg overflow-hidden shadow-sm"
                        >
                            <button
                                @click="toggleGroup(key)"
                                class="w-full flex justify-between items-center px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors duration-200 cursor-pointer"
                            >
                                <div class="flex items-center gap-3">
                                    <font-awesome-icon
                                        :icon="expandedGroups.includes(key) ? 'chevron-down' : 'chevron-up'"
                                        class="text-gray-500"
                                    />
                                    <span class="font-semibold text-gray-800 text-left">
                                        <template v-if="group.parent">
                                            {{ group.parent.nomor_nota }} — {{ group.parent.perihal }} - Anggaran: {{ formatCurrency(group.parent.anggaran) }}
                                            <span class="text-sm text-red-600 font-normal ml-1">(Sisa: {{ formatCurrency(group.parent.sisa_anggaran) }})</span>
                                        </template>
                                        <template v-else>
                                            Nota Tanpa Relasi Parent
                                        </template>
                                    </span>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500 text-white">{{ group.children.length }} Nota</span>
                            </button>

                            <transition name="fade">
                                <div v-show="expandedGroups.includes(key)" class="divide-y divide-gray-200 bg-white">
                                    <div
                                        v-for="nota in group.children"
                                        :key="nota.id"
                                        class="px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        <div class="mb-2 sm:mb-0">
                                            <h4 class="font-medium text-blue-700 text-lg">
                                                {{ nota.nomor_nota }} 
                                                <span 
                                                    :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-2', badgeClasses(nota.jenis)]"
                                                >
                                                    {{ nota.jenis }}
                                                </span>
                                            </h4>
                                            <p class="text-gray-700 mt-1">{{ nota.perihal }}</p>
                                            <p class="text-sm text-gray-500 mt-1">Anggaran: {{ formatCurrency(nota.anggaran) }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Tooltip text="Lampiran" bgColor="bg-purple-600">
                                                <button @click="handleViewAttachment(nota)" class="text-purple-600 hover:bg-purple-100 py-1 px-2 transition-colors duration-200">
                                                    <font-awesome-icon :icon="['fas','paperclip']" />
                                                </button>
                                            </Tooltip>
                                            <Tooltip text="Edit" bgColor="bg-blue-600">
                                                <button @click="openEditModal(nota)" class="text-blue-600 hover:bg-blue-100 py-1 px-2 transition-colors duration-200">
                                                    <font-awesome-icon icon="edit" />
                                                </button>
                                            </Tooltip>
                                            <Tooltip text="Hapus" bgColor="bg-red-600">
                                                <button @click="handleDelete(nota.id)" class="text-red-600 hover:bg-red-100 py-1 px-2 transition-colors duration-200">
                                                    <font-awesome-icon icon="trash-alt" />
                                                </button>
                                            </Tooltip>
                                        </div>
                                    </div>
                                </div>
                            </transition>
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
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease-in-out, max-height 0.3s ease-in-out;
    overflow: hidden;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    max-height: 0;
}
.fade-enter-to, .fade-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>