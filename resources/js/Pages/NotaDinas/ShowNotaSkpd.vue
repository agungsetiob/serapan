<template>
  <Head :title="`${skpd.nama_skpd}`" />

  <AuthenticatedLayout>
    <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">        
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">{{ skpd.nama_skpd }}</h2>
            </div>
            <div class="py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <SearchInput v-model:search="search" class="flex-grow" />
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-40">
                        <select
                        v-model="tahun"
                        @change="handleTahunChange(tahun)"
                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                        <option
                            v-for="tahunOption in tahunOptions"
                            :key="tahunOption"
                            :value="tahunOption"
                        >
                            {{ tahunOption }}
                        </option>
                        </select>
                    </div>

                    <button
                        @click="handleCreateNota()"
                        class="inline-flex items-center justify-center w-full sm:w-auto px-3 py-2 bg-indigo-500 text-white text-sm font-medium rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm"
                    >
                        + Buat Nota
                    </button>
                </div>
            </div>

          <div class="overflow-x-auto">
            <table class="table-auto w-full">
              <thead class="bg-gray-200">
                <tr class="text-left">
                    <th class="px-4 py-2">No. Nota</th>
                    <th class="px-4 py-2">Perihal</th>
                    <!-- <th class="px-4 py-2">Anggaran</th> -->
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Jenis</th>
                    <th></th>
                </tr>
              </thead>
              <tbody>
                <template v-for="nota in filteredNotaDinas" :key="nota.id">
                  <tr class="even:bg-gray-100">
                    <td class="px-4 py-2 text-sm font-semibold">
                      {{ nota.nomor_nota }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                      {{ nota.perihal }}
                    </td>
                    <!-- <td class="px-4 py-2 whitespace-nowrap text-sm">
                      {{ formatCurrency(nota.anggaran) }}
                    </td> -->
                    <td class="px-4 py-2 text-sm">
                      {{ formatDate(nota.tanggal_pengajuan) }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                      <span :class="badgeClasses(nota.jenis)">
                        {{ nota.jenis }}
                      </span>
                    </td>
                    <td class="px-4 py-2 text-sm font-medium space-x-1">
                      <Tooltip text="Nota Dinas" bgColor="bg-green-500">
                        <button
                          @click="handleCreateNota(true, nota)"
                          class="text-green-600 px-2 py-1 rounded hover:bg-green-200 transition-colors"
                        >
                          <font-awesome-icon :icon="['fas', 'file-circle-plus']" />
                        </button>
                      </Tooltip>
                      <Tooltip text="Edit" bgColor="bg-blue-500">
                        <button
                          @click="handleEditNota(nota)"
                          class="text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors"
                        >
                          <font-awesome-icon :icon="['fas', 'pen-to-square']" />
                        </button>
                      </Tooltip>
                      <Tooltip text="Lampiran" bgColor="bg-purple-500">
                        <button
                          @click="handleViewAttachment(nota)"
                          class="text-purple-600 px-2 py-1 rounded hover:bg-purple-200 transition-colors"
                        >
                          <font-awesome-icon :icon="['fas', 'paperclip']" />
                        </button>
                      </Tooltip>
                      <Tooltip text="Hapus" bgColor="bg-red-500">
                        <button
                          @click="handleDeleteNota(nota)"
                          class="text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors"
                        >
                          <font-awesome-icon :icon="['fas', 'trash']" />
                        </button>
                      </Tooltip>
                      <Tooltip :text="expandedNotas.includes(nota.id) ? 'Tutup nota terkait' : 'Buka nota terkait'" bgColor="bg-gray-500">
                        <button
                          v-if="nota.terkait && nota.terkait.length > 0"
                          @click="toggleExpand(nota.id)"
                          class="p-1 rounded hover:bg-gray-300 transition-colors"
                        >
                          <font-awesome-icon 
                            :icon="expandedNotas.includes(nota.id) ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" 
                          />
                        </button>
                      </Tooltip>
                    </td>
                  </tr>

                  <ChildNotaRow
                    v-if="expandedNotas.includes(nota.id) && nota.terkait && nota.terkait.length > 0"
                    :children="nota.terkait"
                    @edit="handleEditNota"
                    @view-attachment="handleViewAttachment"
                    @delete="handleDeleteNota"
                  />
                </template>

                <tr v-if="filteredNotaDinas.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                      <p class="text-sm">Tidak ada nota dinas untuk SKPD ini pada tahun {{ tahun }}.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-4">
            <Pagination v-if="notaDinas.last_page > 1"
              :links="notaDinas.links"
              :meta="{ from: notaDinas.from, to: notaDinas.to, total: notaDinas.total }" 
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <NotaModal
      :show="notaModalState.show"
      :isEdit="notaModalState.isEditing"
      :notaData="notaModalState.notaDinas"
      :skpd="notaModalState.skpd"
      :isChild="notaModalState.isChild"
      :parentNota="notaModalState.parentNota" 
      @close="() => handleCloseModal('nota')"
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

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import NotaModal from './Partials/NotaModal.vue';
import DeleteNotaModal from '../NotaDinas/Partials/DeleteNotaModal.vue';
import LampiranModal from './Partials/LampiranModal.vue';
import ChildNotaRow from './Partials/ChildNotaRow.vue';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import SearchInput from '@/Components/SearchInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';

const props = defineProps({
  skpd: Object,
  notaDinas: Object,
  tahunOptions: Array,
  tahunSelected: Number,
});

const search = ref('');
watch(search, (val) => {
  router.get(route('nota-skpd.show', { nota_skpd: props.skpd.id }), { search: val }, { preserveState: true, replace: true });
});

const flash = computed(() => usePage().props.flash || {}); 
const clearFlash = () => {
  flash.value.success = null;
};

const tahun = ref(props.tahunSelected);
const expandedNotas = ref([]);

const notaModalState = ref({
  show: false,
  isEditing: false,
  notaDinas: null,
  isChild: false,
  parentNota: null,
  skpd: props.skpd
});

const deleteModalState = ref({
  show: false,
  notaDinas: null
});

const attachmentModalState = ref({
  show: false,
  notaId: null
});

const filteredNotaDinas = computed(() => {
  return props.notaDinas.data.filter(nota => !nota.dikaitkan_oleh || nota.dikaitkan_oleh.length === 0);
});

const badgeClasses = (jenis) => {
  const base = 'px-2 py-1 rounded-full text-xs font-medium';
  switch(jenis) {
    case 'Perda': return `${base} bg-blue-100 text-blue-800`;
    case 'Perbup': return `${base} bg-green-100 text-green-800`;
    case 'SK': return `${base} bg-purple-100 text-purple-800`;
    case 'Rekomendasi': return `${base} bg-yellow-100 text-yellow-800`;
    case 'Surat': return `${base} bg-orange-100 text-orange-800`;
    case 'Telaah': return `${base} bg-gray-200 text-gray-800`;
    case 'Instruksi': return `${base} bg-pink-100 text-pink-800`;
    case 'Edaran': return `${base} bg-teal-100 text-teal-800`;
    default: return `${base} bg-gray-100 text-gray-800`;
  }
};

const handleTahunChange = (newTahun) => {
  tahun.value = newTahun;
  router.get(route('nota-skpd.show', props.skpd.id),
    { search: search.value, tahun: newTahun },
    { preserveState: true, replace: true }
  );
};

const toggleExpand = (notaId) => {
  const index = expandedNotas.value.indexOf(notaId);
  if (index === -1) {
    expandedNotas.value.push(notaId);
  } else {
    expandedNotas.value.splice(index, 1);
  }
};

const handleCreateNota = (isChild = false, parentNota = null) => {
  notaModalState.value = {
    show: true,
    isEditing: false,
    notaDinas: null,
    isChild,
    parentNota,
    skpd: props.skpd
  };
};

const handleEditNota = (nota) => {
  notaModalState.value = {
    show: true,
    isEditing: true,
    notaDinas: nota,
    isChild: (nota.dikaitkan_oleh && nota.dikaitkan_oleh.length > 0),
    parentNota: (nota.dikaitkan_oleh && nota.dikaitkan_oleh.length > 0) ? nota.dikaitkan_oleh[0] : null,
    skpd: props.skpd
  };
};

const handleViewAttachment = (nota) => {
  attachmentModalState.value = {
    show: true,
    notaId: nota.id
  };
};

const handleDeleteNota = (nota) => {
  deleteModalState.value = {
    show: true,
    notaDinas: nota
  };
};

const handleCloseModal = (modalType) => {
  switch (modalType) {
    case 'nota':
      notaModalState.value.show = false;
      notaModalState.value.isChild = false;
      notaModalState.value.parentNota = null;
      break;
    case 'attachment':
      attachmentModalState.value.show = false;
      attachmentModalState.value.notaId = null;
      break;
    case 'delete':
      deleteModalState.value.show = false;
      break;
  }
};

const handleSuccess = () => {
  const currentlyExpanded = [...expandedNotas.value];
  expandedNotas.value = [];

  router.reload({
    only: ['notaDinas', 'skpd'],
    preserveState: true,
    onSuccess: () => {
      expandedNotas.value = props.notaDinas.data
        .filter(nota => currentlyExpanded.includes(nota.id))
        .map(nota => nota.id);
    }
  });
};
</script>