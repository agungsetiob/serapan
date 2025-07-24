<script setup>
import { useForm, Head, usePage, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';
import ErrorFlash from '@/Components/ErrorFlash.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SubKegiatanModal from './Partials/SubKegiatanModal.vue';
import KegiatanCard from './Partials/KegiatanCard.vue';
import SubKegiatanList from './Partials/SubKegiatanList.vue';
import KegiatanModal from './Partials/KegiatanModal.vue';
import NotaModal from './Partials/NotaModal.vue';
import LampiranModal from '../NotaDinas/Partials/LampiranModal.vue';
import DeleteNotaModal from './Partials/DeleteNotaModal.vue';
import ProgramModal from './Partials/ProgramModal.vue';
import DeleteProgramModal from './Partials/DeleteProgramModal.vue';
import { formatNumber } from '@/Utils/formatters';

const props = defineProps({
  skpd: Object,
  tahunSelected: Number,
  rekap: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
  flash.value.success = null;
  flash.value.error = null;
};

const formKegiatan = useForm({
  program_id: null,
  nama: '',
  tahun_anggaran: new Date().getFullYear()
});

function submitKegiatan() {
  if (!formKegiatan.program_id) {
    alert('Pilih program terlebih dahulu.');
    return;
  }
  formKegiatan.post(route('kegiatans.store', { skpd: props.skpd.id }), {
    preserveScroll: true,
    onSuccess: () => {
      formKegiatan.reset('program_id', 'nama');
      formKegiatan.program_id = null;
      handleSuccess();
    }

  });
}

const formSubKegiatan = ref({});

watch(
  () => props.skpd.programs,
  (newPrograms) => {
    if (newPrograms && Array.isArray(newPrograms)) {
      newPrograms.forEach(program => {
        if (program.kegiatans && Array.isArray(program.kegiatans)) {
          program.kegiatans.forEach((kegiatan) => {
            if (!formSubKegiatan.value[kegiatan.id]) {
              formSubKegiatan.value[kegiatan.id] = useForm({
                kode_rekening: '',
                nama: '',
                pagu: '',
                tahun_anggaran: new Date().getFullYear(),
              });
            }
          });
        }
      });
    }
  },
  { immediate: true, deep: true }
);

function submitSubKegiatan(kegiatanId) {
  formSubKegiatan.value[kegiatanId].post(route('subkegiatans.store', kegiatanId), {
    preserveScroll: true,
    onSuccess: () => {
      formSubKegiatan.value[kegiatanId].reset();
      handleSuccess();
    }
  });
}

const programModalState = ref({
  show: false,
  isEditing: false,
  program: null,
});

const deleteProgramModalState = ref({
  show: false,
  program: null,
});

const modalState = ref({
  show: false,
  isEditing: false,
  subKegiatan: null,
  kegiatan: null
});

const modalKegiatan = ref({
  show: false,
  isEditing: false,
  kegiatan: null,
});

const notaModalState = ref({
  show: false,
  isEditing: false,
  subKegiatan: null,
  notaDinas: null
});

const attachmentModalState = ref({
  show: false,
  notaId: null
});

const deleteModalState = ref({
  show: false,
  notaDinas: null
});

const handleCreateProgram = () => {
  programModalState.value = {
    show: true,
    isEditing: false,
    program: null,
  };
};

const editProgram = (program) => {
  programModalState.value = {
    show: true,
    isEditing: true,
    program,
  };
};

const deleteProgram = (program) => {
  deleteProgramModalState.value = {
    show: true,
    program,
  };
};

const editKegiatan = (kegiatan) => {
  modalKegiatan.value = {
    show: true,
    isEditing: true,
    kegiatan,
  };
};

const deleteKegiatan = (kegiatan) => {
  modalKegiatan.value = {
    show: true,
    isEditing: false,
    kegiatan,
  };
};

const editSubKegiatan = (sub, kegiatan) => {
  modalState.value = {
    show: true,
    isEditing: true,
    subKegiatan: sub,
    kegiatan: kegiatan
  };
};

const deleteSubKegiatan = (sub, kegiatan) => {
  modalState.value = {
    show: true,
    isEditing: false,
    subKegiatan: sub,
    kegiatan: kegiatan
  };
};

const handleCreateNota = (subKegiatan) => {
  notaModalState.value = {
    show: true,
    isEditing: false,
    subKegiatan: subKegiatan,
    notaDinas: null
  };
};

const handleEditNota = (nota, subKegiatan) => {
  notaModalState.value = {
    show: true,
    isEditing: true,
    subKegiatan: subKegiatan,
    notaDinas: nota
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
    case 'program':
      programModalState.value.show = false;
      break;
    case 'deleteProgram':
      deleteProgramModalState.value.show = false;
      break;
    case 'nota':
      notaModalState.value.show = false;
      break;
    case 'attachment':
      attachmentModalState.value.show = false;
      attachmentModalState.value.notaId = null;
      break;
    case 'delete':
      deleteModalState.value.show = false;
      break;
    default:
      break;
  }
};

const handleSuccess = () => {
  router.reload({ only: ['skpd'] });
};
</script>

<template>

  <Head title="Capaian SKPD" />
  <AuthenticatedLayout>
    <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
    <ErrorFlash :flash="flash" @clearFlash="clearFlash" />
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2 space-y-4">
      <div class="max-w-8xl mx-auto lg:px-6">
        <div
          class="bg-gradient-to-br from-indigo-600 to-blue-500 text-white rounded-lg p-6 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div>
            <h2 class="text-2xl font-bold">{{ skpd.nama_skpd }}</h2>
          </div>
          <Link :href="route('skpds.tahun', { skpd: skpd.id, tahun: new Date().getFullYear() })"
            class="bg-blue-200 text-blue-800 hover:text-white text-sm font-medium px-3 py-1 rounded-full hover:bg-blue-800 transition-colors">
          Histori
          </Link>
        </div>
      </div>

      <!-- Rekap Anggaran SKPD Keseluruhan -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:px-6">
        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-green-600">
          <font-awesome-icon :icon="['fas', 'money-bill-wave']" class="text-green-600 text-2xl mr-3" />
          <div>
            <p class="text-sm text-gray-500">Total Pagu SKPD</p>
            <p class="text-lg font-semibold text-red-600">
              Rp. {{ formatNumber(rekap.totalPagu) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-blue-700">
          <font-awesome-icon :icon="['fas', 'money-bill-trend-up']" class="text-blue-700 text-2xl mr-3" />
          <div>
            <p class="text-sm text-gray-500">Total Serapan SKPD</p>
            <p class="text-lg font-semibold text-green-700">
              Rp. {{ formatNumber(rekap.totalSerapan) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-red-700">
          <font-awesome-icon :icon="['fas', 'percent']" class="text-red-700 text-2xl mr-3" />
          <div>
            <p class="text-sm text-gray-500">Persentase Serapan SKPD</p>
            <p class="text-lg font-semibold text-blue-700">
              {{ formatNumber(rekap.persentaseSerapan) }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Add Kegiatan Card -->
      <div class="max-w-8xl mx-auto lg:px-6">
        <div class="bg-white rounded-lg shadow-md p-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
              Tambah Kegiatan Baru
            </h3>
            <button @click="handleCreateProgram"
              class="inline-flex items-center px-2 py-1.5 bg-green-500 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
              <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" />
              Tambah Program
            </button>
          </div>
          <form @submit.prevent="submitKegiatan" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <InputLabel for="program_id" value="Pilih Program" />
                <select id="program_id" v-model="formKegiatan.program_id"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  required>
                  <option :value="null" selected disabled>-- Pilih Program --</option>
                  <option v-for="program in skpd.programs" :key="program.id" :value="program.id">
                    {{ program.nama }}
                  </option>
                </select>
                <InputError :message="formKegiatan.errors.program_id" />
              </div>
              <div class="md:col-span-1">
                <InputLabel for="nama-kegiatan" value="Nama Kegiatan" />
                <TextInput id="nama-kegiatan" v-model="formKegiatan.nama" class="w-full"
                  placeholder="Masukkan nama kegiatan" />
                <InputError :message="formKegiatan.errors.nama" />
              </div>

              <div>
                <InputLabel for="tahun-anggaran-kegiatan" value="Tahun Anggaran" />
                <TextInput id="tahun-anggaran-kegiatan" v-model="formKegiatan.tahun_anggaran" class="w-full" readonly />
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit" :disabled="formKegiatan.processing || !formKegiatan.program_id"
                class="inline-flex items-center px-3 py-1.5 bg-indigo-500 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Programs List -->
      <div class="space-y-8 lg:px-6 pb-1">
        <div v-if="skpd.programs && skpd.programs.length > 0">
          <div v-for="program in skpd.programs" :key="program.id"
            class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xl font-bold text-indigo-700 flex items-center">
                <font-awesome-icon :icon="['fas', 'folder-open']" class="mr-2" />
                {{ program.nama }}
              </h3>
              <div class="flex space-x-2">
                <button @click="editProgram(program)"
                  class="inline-flex items-center px-3 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-white text-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-colors">
                  <font-awesome-icon :icon="['fas', 'edit']" class="mr-1" /> Edit
                </button>
                <button @click="deleteProgram(program)"
                  class="inline-flex items-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-white text-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                  <font-awesome-icon :icon="['fas', 'trash']" class="mr-1" /> Hapus
                </button>
              </div>
            </div>

            <!-- Rekapitulasi per Program -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-white shadow rounded-lg p-3 flex items-center border-l-4 border-l-green-500">
                <font-awesome-icon :icon="['fas', 'coins']" class="text-green-500 text-xl mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Pagu Program</p>
                  <p class="text-md font-semibold text-red-500">
                    Rp. {{ formatNumber(program.pagu) }}
                  </p>
                </div>
              </div>
              <div class="bg-white shadow rounded-lg p-3 flex items-center border-l-4 border-l-blue-600">
                <font-awesome-icon :icon="['fas', 'sack-dollar']" class="text-blue-600 text-xl mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Serapan Program</p>
                  <p class="text-md font-semibold text-green-600">
                    Rp. {{ formatNumber(program.total_serapan) }}
                  </p>
                </div>
              </div>
              <div class="bg-white shadow rounded-lg p-3 flex items-center border-l-4 border-l-red-600">
                <font-awesome-icon :icon="['fas', 'chart-pie']" class="text-red-600 text-xl mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Persentase Serapan Program</p>
                  <p class="text-md font-semibold text-blue-600">
                    {{ formatNumber(program.presentase_serapan) }}%
                  </p>
                </div>
              </div>
            </div>

            <!-- Kegiatan List dalam Program -->
            <div class="space-y-6">
              <div v-if="program.kegiatans && program.kegiatans.length > 0">
                <div v-for="kegiatan in program.kegiatans" :key="kegiatan.id"
                  class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 mb-4">
                  <KegiatanCard :kegiatan="kegiatan" @edit="editKegiatan" @delete="deleteKegiatan" />

                  <!-- Sub Kegiatan List -->
                  <SubKegiatanList :kegiatan="kegiatan" :formSubKegiatan="formSubKegiatan[kegiatan.id]"
                    :formatNumber="formatNumber" :onSubmit="submitSubKegiatan" :onEdit="editSubKegiatan"
                    :onDelete="deleteSubKegiatan" @create-nota-dinas="handleCreateNota" @edit-nota="handleEditNota"
                    @delete-nota="handleDeleteNota" @view-attachment="handleViewAttachment" />
                </div>
              </div>
              <div v-else class="text-gray-500 text-center py-4">
                Tidak ada kegiatan untuk program ini
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-gray-600 text-center py-8 text-lg bg-white rounded-lg shadow-md">
          Tidak ada program yang tersedia untuk SKPD ini
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ProgramModal :show="programModalState.show" :isEditing="programModalState.isEditing"
      :program="programModalState.program" :skpdId="skpd.id" @close="() => handleCloseModal('program')"
      @success="handleSuccess" />
    <DeleteProgramModal :show="deleteProgramModalState.show" :program="deleteProgramModalState.program"
      @close="() => handleCloseModal('deleteProgram')" @success="handleSuccess" />

    <KegiatanModal :show="modalKegiatan.show" :isEditing="modalKegiatan.isEditing" :kegiatan="modalKegiatan.kegiatan"
      @close="modalKegiatan.show = false" @success="handleSuccess" />

    <SubKegiatanModal :show="modalState.show" :subKegiatan="modalState.subKegiatan" :isEditing="modalState.isEditing"
      :kegiatan="modalState.kegiatan" @close="modalState.show = false" @success="handleSuccess" />

    <NotaModal :show="notaModalState.show" :isEdit="notaModalState.isEditing" :notaData="notaModalState.notaDinas"
      :subKegiatan="notaModalState.subKegiatan || notaModalState.notaDinas?.sub_kegiatan"
      @close="() => handleCloseModal('nota')" @success="handleSuccess" />
    <LampiranModal :show="attachmentModalState.show" :notaId="attachmentModalState.notaId"
      @close="() => handleCloseModal('attachment')" />
    <DeleteNotaModal :show="deleteModalState.show" :notaDinas="deleteModalState.notaDinas"
      @close="() => handleCloseModal('delete')" />
  </AuthenticatedLayout>
</template>
