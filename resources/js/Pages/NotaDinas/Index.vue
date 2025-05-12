<script setup>
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Tooltip from '@/Components/Tooltip.vue';
import NotaModal from './Partials/NotaModal.vue';
import LampiranModal from './Partials/LampiranModal.vue';
import DeleteModal from './Partials/DeleteModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';

const props = defineProps({
  notas: Object,
  userRole: String,
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
const clearFlash = () => (flash.value.success = null);

const search = ref('');
watch(search, (val) => {
  router.get(route('nota-dinas.index'), { search: val }, { preserveState: true, replace: true });
});

const isNotaModalOpen = ref(false);
const isEditMode = ref(false);
const selectedNota = ref(null);
const isLampiranModalOpen = ref(false);
const showDeleteModal = ref(false);

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;
}

function openCreateModal() {
  isEditMode.value = false;
  selectedNota.value = {};
  isNotaModalOpen.value = true;
}

function openEditModal(nota) {
  isEditMode.value = true;
  selectedNota.value = nota;
  isNotaModalOpen.value = true;
}

function closeNotaModal() {
  isNotaModalOpen.value = false;
}

function openLampiranModal(nota) {
  selectedNota.value = nota;
  isLampiranModalOpen.value = true;
}

function closeLampiranModal() {
  isLampiranModalOpen.value = false;
}

function openDeleteModal(nota) {
  selectedNota.value = nota;
  showDeleteModal.value = true;
}

function closeDeleteModal() {
  selectedNota.value = null;
  showDeleteModal.value = false;
}
</script>

<template>
  <Head title="Daftar Nota Dinas" />
  <AuthenticatedLayout>
    <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Nota Dinas</h2>
          </div>

          <div class="space-y-3">
            <SearchInput v-model:search="search" />
            <div v-for="nota in notas.data" :key="nota.id" class="border rounded-lg p-4 hover:shadow-md transition">
              <div class="grid grid-cols-2 md:grid-cols-12 gap-4">
                <div class="md:col-span-2">
                  <div class="text-xs text-gray-500">Nomor</div>
                  <p class="font-medium break-all">{{ nota.nomor_nota }}</p>
                </div>
                <div class="md:col-span-3">
                  <div class="text-xs text-gray-500">Perihal</div>
                  <div class="font-medium">{{ nota.perihal }}</div>
                </div>
                <div class="md:col-span-3">
                  <div class="text-xs text-gray-500">SKPD</div>
                  <div class="font-medium">{{ nota.sub_kegiatan.kegiatan.skpd.nama_skpd }}</div>
                </div>
                <div class="md:col-span-1">
                  <div class="text-xs text-gray-500">Anggaran</div>
                  <div class="font-medium">{{ nota.anggaran }}</div>
                </div>
                <div class="md:col-span-1">
                  <div class="text-xs text-gray-500">Tanggal</div>
                  <div>{{ formatDate(nota.tanggal_pengajuan) }}</div>
                </div>
                <div class="flex justify-end gap-2 md:col-span-2 items-center">
                  <Tooltip text="List Lampiran" bgColor="bg-gray-500">
                    <button @click="openLampiranModal(nota)" class="px-2 py-1 text-xs sm:text-sm font-semibold rounded border text-gray-600 hover:bg-gray-200">
                      <font-awesome-icon icon="paperclip" />
                    </button>
                  </Tooltip>
                  <Tooltip text="Edit Nota Dinas" bgColor="bg-blue-500">
                    <button @click="openEditModal(nota)" class="px-2 py-1 text-xs sm:text-sm font-semibold rounded border text-blue-400 hover:bg-blue-100">
                      <font-awesome-icon icon="edit" />
                    </button>
                  </Tooltip>
                  <Tooltip text="Delete Nota Dinas" bgColor="bg-red-500">
                    <button @click="openDeleteModal(nota)" class="px-2 py-1 text-xs sm:text-sm font-semibold rounded border text-red-500 hover:bg-red-100">
                      <font-awesome-icon icon="trash" />
                    </button>
                  </Tooltip>
                </div>
              </div>
            </div>

            <div v-if="notas.data.length === 0" class="border rounded-lg p-4 text-center text-red-500">
              Tidak ada nota dinas
            </div>
          </div>

          <Pagination :links="notas.links" :meta="{ from: notas.from, to: notas.to, total: notas.total }" />
        </div>
      </div>
    </div>

    <NotaModal
      :show="isNotaModalOpen"
      :isEdit="isEditMode"
      :notaData="selectedNota"
      @close="closeNotaModal"
    />

    <LampiranModal
      :show="isLampiranModalOpen"
      :notaId="selectedNota?.id"
      @close="closeLampiranModal"
    />

    <DeleteModal
      :show="showDeleteModal"
      :nota="selectedNota"
      @close="closeDeleteModal"
    />
  </AuthenticatedLayout>
</template>
