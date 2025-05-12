<script setup>
import { useForm, Head, usePage, Link, router } from '@inertiajs/vue3';
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
  nama: '',
  tahun_anggaran: new Date().getFullYear()
});

function submitKegiatan() {
  formKegiatan.post(route('kegiatans.store', props.skpd.id), {
    preserveScroll: true,
    onSuccess: () => formKegiatan.reset()
  });
}

const formSubKegiatan = ref({});

// Watch perubahan pada daftar kegiatan agar setiap kegiatan baru mendapatkan form sub kegiatan
watch(
  () => props.skpd.kegiatans,
  (newKegiatans) => {
    if (newKegiatans && Array.isArray(newKegiatans)) {
      newKegiatans.forEach((kegiatan) => {
        if (!formSubKegiatan.value[kegiatan.id]) {
          formSubKegiatan.value[kegiatan.id] = useForm({
            nama: '',
            pagu: '',
            tahun_anggaran: new Date().getFullYear()
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
    onSuccess: () => formSubKegiatan.value[kegiatanId].reset()
  });
}
function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

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
const showNotaModal = ref(false);
const selectedSubKegiatan = ref(null);

// Handle the emitted event from SubKegiatanList
const handleCreateNota = (subKegiatan) => {
  selectedSubKegiatan.value = subKegiatan;
  showNotaModal.value = true;
  console.log('Modal should open for:', subKegiatan);
};

// Handle modal close
const handleCloseModal = () => {
  showNotaModal.value = false;
};

const handleSuccess = (message) => {
  router.reload({ only: ['skpd'] });
};
</script>

<template>
  <Head title="SKPD" />
  <AuthenticatedLayout>
    <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
    <ErrorFlash :flash="flash" @clearFlash="clearFlash" />
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
      <!-- Header Section -->
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-6 pb-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white bg-blue-700 rounded-full px-3">
            {{ skpd.nama_skpd }}
          </h2>
          <Link
            :href="route('skpds.tahun', { skpd: skpd.id, tahun: new Date().getFullYear() })"
            class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full hover:bg-blue-200 transition-colors"
          >
            Histori
          </Link>
        </div>
      </div>
      <!-- Rekap Anggaran -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 sm:px-6 lg:px-6">
        <div class="bg-white shadow rounded-lg p-4 flex items-center">
          <font-awesome-icon 
            :icon="['fas', 'money-bill-wave']" 
            class="text-green-600 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Total Pagu</p>
            <p class="text-lg font-semibold text-red-600">
              Rp. {{ new Intl.NumberFormat('id-ID').format(rekap.totalPagu) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center">
          <font-awesome-icon 
            :icon="['fas', 'money-bill-trend-up']" 
            class="text-blue-700 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Total Serapan</p>
            <p class="text-lg font-semibold text-green-700">
              Rp. {{ new Intl.NumberFormat('id-ID').format(rekap.totalSerapan) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center">
          <font-awesome-icon 
            :icon="['fas', 'percent']" 
            class="text-red-700 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Persentase Serapan</p>
            <p class="text-lg font-semibold text-blue-700">
              {{ rekap.persentaseSerapan }}%
            </p>
          </div>
        </div>
      </div>
      <!-- Add Kegiatan Card -->
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
        <div class="bg-white rounded-lg shadow-md p-4 mb-8">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Tambah Kegiatan Baru
          </h3>
          <form @submit.prevent="submitKegiatan" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2">
                <InputLabel value="Nama Kegiatan" />
                <TextInput v-model="formKegiatan.nama" class="w-full" placeholder="Masukkan nama kegiatan"/>
                <InputError :message="formKegiatan.errors.nama" />
              </div>

              <div>
                <InputLabel value="Tahun Anggaran" />
                <TextInput v-model="formKegiatan.tahun_anggaran" class="w-full" readonly/>
              </div>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
              >
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Kegiatan List -->
      <div class="space-y-6 lg:px-6 pb-4">
        <div
          v-for="kegiatan in skpd.kegiatans"
          :key="kegiatan.id"
          class="bg-white rounded-lg shadow-md overflow-hidden"
        >
          <KegiatanCard :kegiatan="kegiatan" @edit="editKegiatan"
          @delete="deleteKegiatan"/>
          <!-- Sub Kegiatan List -->
          <SubKegiatanList
            :kegiatan="kegiatan"
            :formSubKegiatan="formSubKegiatan[kegiatan.id]"
            :formatNumber="formatNumber"
            :onSubmit="submitSubKegiatan"
            :onEdit="editSubKegiatan"
            :onDelete="deleteSubKegiatan"
            @create-nota-dinas="handleCreateNota"
          />
        </div>
      </div>
    </div>
    <KegiatanModal
      :show="modalKegiatan.show"
      :isEditing="modalKegiatan.isEditing"
      :kegiatan="modalKegiatan.kegiatan"
      @close="modalKegiatan.show = false"
      @success="handleSuccess"
    />
    <SubKegiatanModal
      :show="modalState.show"
      :subKegiatan="modalState.subKegiatan"
      :isEditing="modalState.isEditing"
      :kegiatan="modalState.kegiatan"
      @close="modalState.show = false"
      @success="handleSuccess"
    />
    <NotaModal
      :show="showNotaModal"
      :subKegiatan="selectedSubKegiatan"
      @close="handleCloseModal"
    />
  </AuthenticatedLayout>
</template>
