<template>
  <div class="space-y-6">
    <!-- SKPD serapan tertinggi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-4 border-b">
        <h3 class="font-semibold text-gray-700">10 SKPD dengan Serapan Tertinggi</h3>
      </div>
      <div class="p-4">
        <canvas id="topSkpdChart" class="h-96"></canvas>
      </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Total Nota Dinas per Year card -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
          <h3 class="font-semibold text-gray-700">Nota Dinas per Tahun</h3>
        </div>
        <div class="p-4">
          <canvas id="notaDinasChart" class="h-64"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

// Data refs
const chartData = ref([]);
const notaDinasData = ref([]);
const topSkpdData = ref([]);

// Fetch chart data
onMounted(async () => {
  try {
    // nota dinas chart data
    const notaResponse = await axios.get('/api/nota-per-year');
    notaDinasData.value = notaResponse.data;

    //top SKPD data
    const skpdResponse = await axios.get('/api/skpd/top-serapan');
    topSkpdData.value = skpdResponse.data;

    // Top SKPD Chart
    const ctx2 = document.getElementById('topSkpdChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: topSkpdData.value.map(item => item.nama_skpd),
            datasets: [{
                label: 'Presentase Serapan (%)',
                data: topSkpdData.value.map(item => item.presentase_serapan),
                backgroundColor: '#ff6384',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.x}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                },
                y: {
                    ticks: {
                        autoSkip: false
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0)',
                    }
                }
            }
        }
    });

    // nota dinas chart
    const ctx1 = document.getElementById('notaDinasChart').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: notaDinasData.value.map(item => item.year),
        datasets: [{
          label: 'Total Nota Dinas',
          data: notaDinasData.value.map(item => item.total),
          backgroundColor: '#ffce56',
          borderColor: '#ffce56',
          borderWidth: 3,
          tension: 0.1,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: { 
          y: { 
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          } 
        }
      }
    });

  } catch (error) {
    console.error('Error fetching chart data:', error);
  }
});
</script>