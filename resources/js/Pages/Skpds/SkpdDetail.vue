<script setup>
import { useForm, Head, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';

const props = defineProps({
  skpd: Object,
});
const page = usePage();
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
  flash.value.success = null;
};

const formKegiatan = useForm({
  nama: '',
  pagu: '',
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
</script>

<template>
  <Head title="SKPD" />
  <AuthenticatedLayout>
    <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
    <div class="container mx-auto px-4 py-8">
      <!-- Header Section -->
      <div class="pt-6 sm:pt-16 mx-2 sm:px-2 mb-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white bg-blue-700 rounded-full px-3">
            {{ skpd.nama_skpd }}
          </h2>
          <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
            Tahun Anggaran: {{ new Date().getFullYear() }}
          </span>
        </div>
      </div>

      <!-- Add Kegiatan Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          Tambah Kegiatan Baru
        </h3>
        <form @submit.prevent="submitKegiatan" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Kegiatan
              </label>
              <input
                v-model="formKegiatan.nama"
                placeholder="Masukkan nama kegiatan"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <div v-if="formKegiatan.errors.nama" class="text-red-500 text-sm mt-1">
                {{ formKegiatan.errors.nama }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Pagu (Rp)
              </label>
              <input
                v-model="formKegiatan.pagu"
                placeholder="Masukkan pagu"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <div v-if="formKegiatan.errors.pagu" class="text-red-500 text-sm mt-1">
                {{ formKegiatan.errors.pagu }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tahun Anggaran
              </label>
              <input
                v-model="formKegiatan.tahun_anggaran"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
          <button
            type="submit"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                clip-rule="evenodd"
              />
            </svg>
            Tambah Kegiatan
          </button>
        </form>
      </div>

      <!-- Kegiatan List -->
      <div class="space-y-6">
        <div
          v-for="kegiatan in skpd.kegiatans"
          :key="kegiatan.id"
          class="bg-white rounded-lg shadow-md overflow-hidden"
        >
          <!-- Kegiatan Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">
                {{ kegiatan.nama }}
              </h3>
                <div class="flex flex-col md:flex-row md:items-center gap-2 mt-1 text-sm text-gray-600">
                    <div>
                        <span class="font-semibold">Pagu:</span> Rp. {{ formatNumber(kegiatan.pagu) }}
                    </div>
                    <div class="hidden md:block mx-2">|</div>
                    <div>
                        <span class="font-semibold">Serapan:</span> Rp {{ formatNumber(kegiatan.total_serapan) }}
                    </div>
                    <div class="hidden md:block mx-2">|</div>
                    <div class="flex items-center gap-1">
                        <span class="font-semibold">Persentase:</span>
                        <span class="text-blue-600 font-medium">{{ kegiatan.presentase_serapan }}%</span>
                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-blue-500 transition-all duration-500 ease-out"
                            :style="{ width: kegiatan.presentase_serapan + '%' }"
                        ></div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
              {{ kegiatan.sub_kegiatans.length }} Sub Kegiatan
            </span>
          </div>

          <!-- Sub Kegiatan List -->
          <div class="px-6 py-4">
            <h4 class="font-medium text-gray-700 mb-3 flex items-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-2 text-gray-500"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Daftar Sub Kegiatan
            </h4>

            <ul class="divide-y divide-gray-200 mb-4">
              <li
                v-for="sub in kegiatan.sub_kegiatans"
                :key="sub.id"
                class="py-3 flex justify-between items-center"
              >
                <div>
                    <span class="text-gray-800">{{ sub.nama }}</span>
                    <p class="text-sm text-gray-500">
                        Pagu: Rp {{ formatNumber(sub.pagu) }} |
                        Serapan: Rp {{ sub.total_serapan.toLocaleString('id-ID') }} |
                        <span class="text-blue-600 font-medium">{{ sub.presentase_serapan }}%</span>
                    </p>
                    <div class="mt-1 w-full h-1.5 bg-gray-200 rounded-full">
                        <div
                            class="h-full bg-blue-500 rounded-full transition-all duration-500 ease-out"
                            :style="{ width: sub.presentase_serapan + '%' }">
                        </div>
                    </div>
                </div>
                <span
                  class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"
                >
                  {{ sub.tahun_anggaran }}
                </span>
              </li>
            </ul>

            <!-- Tambah Sub Kegiatan Form (hanya dirender jika form sudah ada) -->
            <div
              class="bg-gray-50 p-4 rounded-lg"
              v-if="formSubKegiatan[kegiatan.id]"
            >
              <h5 class="text-sm font-medium text-gray-700 mb-3">
                Tambah Sub Kegiatan
              </h5>
              <form
                @submit.prevent="submitSubKegiatan(kegiatan.id)"
                class="space-y-3"
              >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div>
                    <input
                      v-model="formSubKegiatan[kegiatan.id].nama"
                      placeholder="Nama sub kegiatan"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <div v-if="formSubKegiatan[kegiatan.id].errors.nama" class="text-red-500 text-sm mt-1">
                      {{ formSubKegiatan[kegiatan.id].errors.nama }}
                    </div>
                  </div>
                  <div>
                    <input
                      v-model="formSubKegiatan[kegiatan.id].pagu"
                      placeholder="Pagu"
                      type="number"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <div v-if="formSubKegiatan[kegiatan.id].errors.pagu" class="text-red-500 text-sm mt-1">
                      {{ formSubKegiatan[kegiatan.id].errors.pagu }}
                    </div>
                  </div>
                  <div>
                    <input
                      v-model="formSubKegiatan[kegiatan.id].tahun_anggaran"
                      type="number"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                <button
                  type="submit"
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  Tambah Sub Kegiatan
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
