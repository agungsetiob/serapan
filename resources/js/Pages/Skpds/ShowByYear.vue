<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KegiatanCard from './Partials/KegiatanCard.vue';
import SubKegiatanList from './Partials/SubKegiatanList.vue';

const props = defineProps({
  skpd: Object,
  tahunSelected: Number,
  rekap: Object,
  tahunTersedia: Object,
});
const selectedYear = ref(props.tahunSelected);
watch(
  () => props.tahunSelected,
  (newVal) => {
    selectedYear.value = newVal;
  }
);

function gantiTahun() {
  router.get(route('skpds.tahun', { 
    skpd: props.skpd.id, 
    tahun: selectedYear.value 
  }));
}

function formatNumber(value) {
  if (typeof value !== 'number') return '0,00';
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}

</script>

<template>
  <Head title="Rekap SKPD" />
  <AuthenticatedLayout>
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
      <div class="max-w-8xl mx-auto lg:px-6 pb-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold">
            Rekap: {{ skpd.nama_skpd }}
          </h2>
          <!-- Dropdown Tahun -->
          <div class="relative inline-flex items-center">
            <select 
              v-model="selectedYear"
              @change="gantiTahun"
              class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm hover:border-gray-400 transition-all duration-200"
            >
              <option 
                v-for="tahun in tahunTersedia" 
                :key="tahun" 
                :value="tahun"
                class="text-sm"
              >
                {{ tahun }}
              </option>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Rekap Anggaran SKPD Keseluruhan -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 lg:px-6">
        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-green-600">
          <font-awesome-icon 
            :icon="['fas', 'money-bill-wave']" 
            class="text-green-600 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Total Pagu SKPD</p>
            <p class="text-lg font-semibold text-red-600">
              Rp. {{ formatNumber(rekap.totalPagu) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-blue-700">
          <font-awesome-icon 
            :icon="['fas', 'money-bill-trend-up']" 
            class="text-blue-700 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Total Serapan SKPD</p>
            <p class="text-lg font-semibold text-green-700">
              Rp. {{ formatNumber(rekap.totalSerapan) }}
            </p>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center border-l-4 border-l-red-700">
          <font-awesome-icon 
            :icon="['fas', 'percent']" 
            class="text-red-700 text-2xl mr-3"
          />
          <div>
            <p class="text-sm text-gray-500">Persentase Serapan SKPD</p>
            <p class="text-lg font-semibold text-blue-700">
              {{ rekap.persentaseSerapan }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Programs List -->
      <div class="space-y-8 lg:px-6 pb-1">
        <div v-if="skpd.programs && skpd.programs.length > 0">
          <div
            v-for="program in skpd.programs"
            :key="program.id"
            class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 mb-4"
          >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-indigo-700 flex items-center">
                    <font-awesome-icon :icon="['fas', 'folder-open']" class="mr-2" />
                     {{ program.nama }}
                </h3>
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
                <div
                  v-for="kegiatan in program.kegiatans"
                  :key="kegiatan.id"
                  class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 mb-4"
                >
                  <KegiatanCard 
                    :kegiatan="kegiatan"
                    :showButtons="false"
                  />
                  
                  <!-- Sub Kegiatan List -->
                  <SubKegiatanList
                    :kegiatan="kegiatan"
                    :formatNumber="formatNumber"
                    :showButtons="false"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
