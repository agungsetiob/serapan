<template>
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
      <h3 class="font-semibold text-gray-700">Distribusi Anggaran Sub Kegiatan</h3>
    </div>
    <div class="p-4">
      <apexchart
        width="100%"
        height="400"
        type="treemap"
        :options="chartOptions"
        :series="chartSeries"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import ApexCharts from 'vue3-apexcharts';

const props = defineProps({
  skpdId: Number,
  selectedTahun: Number
});

const chartSeries = ref([]);
const chartOptions = ref({
  legend: { show: false },
  chart: {
    type: 'treemap',
    toolbar: { show: false }
  },
  plotOptions: {
    treemap: {
      distributed: true,
      enableShades: true
    }
  },
  dataLabels: {
    enabled: true,
    style: { fontSize: '13px' },
    formatter: (val, { seriesIndex, dataPointIndex, w }) =>
      w.config.series[seriesIndex].data[dataPointIndex].x
  },
  tooltip: {
    y: {
      formatter: val => `Rp${val.toLocaleString('id-ID')}`
    }
  }
});

const loadData = async () => {
  const { data } = await axios.get(route('skpds.api.distribusi-sub-kegiatan', props.skpdId), {
    params: { tahun: props.selectedTahun }
  });

  chartSeries.value = [{
    data: data.data.map(item => ({
      x: item.nama,
      y: parseFloat(item.pagu)
    }))
  }];
};

onMounted(loadData);
watch(() => props.selectedTahun, loadData);
</script>

<script>
export default {
  components: {
    apexchart: ApexCharts
  }
};
</script>
