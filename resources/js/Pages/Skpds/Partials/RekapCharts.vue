<template>
  <div class="grid gap-6 md:grid-cols-2">
    <!-- Donut Chart Rekap per Jenis -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-4 border-b">
        <h3 class="font-semibold text-gray-700">Rekap Nota Dinas per Jenis</h3>
      </div>
      <div class="p-4">
        <canvas ref="rekapjenisChart" class="h-96"></canvas>
      </div>
    </div>

    <!-- Line Chart Tren per Bulan -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-4 border-b">
        <h3 class="font-semibold text-gray-700">Tren Pengajuan per Bulan</h3>
      </div>
      <div class="p-4">
        <canvas ref="trenChart" class="h-64"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const props = defineProps({
  skpdId: Number,
  selectedTahun: Number
});

const rekapjenisChart = ref(null);
const trenChart = ref(null);
const rekapJenisInstance = ref(null);
const trenInstance = ref(null);

const jenisColors = {
  Pelaksanaan: '#4f46e5', GU: '#f59e0b', TU: '#10b981', LS: '#ec4899',
  Perda: '#2563eb', Perbup: '#14b8a6', SK: '#8b5cf6', Rekomendasi: '#eab308',
  Surat: '#0ea5e9', Telaah: '#ef4444', Edaran: '#34d399', Instruksi: '#22d3ee'
};

// 🟡 DONUT CHART
const drawDonutChart = async () => {
  const { data } = await axios.get(route('skpds.api.rekap-nota-per-jenis', props.skpdId), {
    params: { tahun: props.selectedTahun }
  });

  const dataset = data.rekap || [];

  if (rekapJenisInstance.value) rekapJenisInstance.value.destroy();

  rekapJenisInstance.value = new Chart(rekapjenisChart.value, {
    type: 'doughnut',
    data: {
      labels: dataset.map(i => i.jenis),
      datasets: [{
        data: dataset.map(i => i.jumlah),
        backgroundColor: dataset.map(i => jenisColors[i.jenis] || '#9ca3af'),
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.label}: ${ctx.raw} nota`
          }
        }
      }
    }
  });
};

// 📈 LINE CHART
const drawLineChart = async () => {
  const { data } = await axios.get(route('skpds.api.tren-nota-per-bulan', props.skpdId), {
    params: { tahun: props.selectedTahun }
  });

  const labels = data.data.map(d => new Date(0, d.bulan - 1).toLocaleString('id-ID', { month: 'short' }));
  const jumlah = data.data.map(d => d.jumlah);

  if (trenInstance.value) trenInstance.value.destroy();

  trenInstance.value = new Chart(trenChart.value, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Jumlah Nota Dinas',
        data: jumlah,
        backgroundColor: '#4f46e5',
        borderColor: '#4f46e5',
        pointBorderColor: '#0284c7',
        borderWidth: 1.5,
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.raw} nota`
          }
        }
      }
    }
  });
};

// Trigger awal dan on tahun berubah
onMounted(() => {
  drawDonutChart();
  drawLineChart();
});
watch(() => props.selectedTahun, () => {
  drawDonutChart();
  drawLineChart();
});
</script>
