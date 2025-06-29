<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RekapNotaProgress from './Partials/RekapNotaProgress.vue';
import RekapCharts from './Partials/RekapCharts.vue';
import DistribusiSubTreemap from './Partials/DistribusiSubTreemap.vue';

const props = defineProps({
  skpd: Object,
  tahunDipilih: Number,
  tahunTersedia: Array,
  rekap: Array
});

const selectedTahun = ref(props.tahunDipilih);
const maxJumlah = Math.max(...props.rekap.map(r => r.jumlah), 1);

const progressColor = (jenis) => {
  const map = {
    'Pelaksanaan': 'bg-indigo-500',
    'GU': 'bg-amber-500',
    'TU': 'bg-green-500',
    'LS': 'bg-pink-500',
    'Perda': 'bg-blue-600',
    'Perbup': 'bg-teal-500',
    'SK': 'bg-purple-500',
    'Rekomendasi': 'bg-yellow-500',
    'Surat': 'bg-sky-500',
    'Telaah': 'bg-red-500',
    'Edaran': 'bg-emerald-500',
    'Instruksi': 'bg-cyan-500',
  };
  return map[jenis] || 'bg-gray-400';
};

watch(selectedTahun, (tahun) => {
  router.get(route('skpds.rekap-nota', props.skpd.id), { tahun }, { preserveScroll: true });
});
</script>

<template>
  <Head :title="`Rekap - ${skpd.nama_skpd}`" />
  <AuthenticatedLayout>
    <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
      <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">

        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
            <h1 class="text-2xl font-semibold text-gray-900">Rekap Nota Dinas</h1>
            <Link
              :href="route('skpds.tahun', { skpd: skpd.id, tahun: new Date().getFullYear() })"
              class="bg-blue-200 text-blue-800 hover:text-white font-medium px-3 py-1 rounded-full hover:bg-blue-500 transition-colors"
            >
              Histori
            </Link>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
            <p class="text-lg font-medium text-gray-800">{{ skpd.nama_skpd }}</p>
            <select v-model="selectedTahun" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
              <option v-for="t in tahunTersedia" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>

          <div v-if="rekap.length">
            <RekapNotaProgress :rekap="rekap" :maxJumlah="maxJumlah" :progressColor="progressColor" />
          </div>
          <div v-else class="text-center text-gray-500 mt-10">
            <svg class="mx-auto w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>Tidak ada data nota untuk tahun {{ selectedTahun }}.</p>
          </div>
        </div>

        <RekapCharts :skpdId="skpd.id" :selectedTahun="selectedTahun" />
        <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
          <DistribusiSubTreemap :skpdId="skpd.id" :selectedTahun="selectedTahun" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
