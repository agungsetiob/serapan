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
      
      <!-- Kabupaten Serapan per Year card -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
          <h3 class="font-semibold text-gray-700">Serapan Kabupaten per Tahun</h3>
        </div>
        <div class="p-4">
          <canvas id="kabupatenChart" class="h-64"></canvas>
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
const kabupatenData = ref([]);

// Format number to IDR currency
const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num);
};

// Fetch chart data
onMounted(async () => {
  try {
    // nota dinas chart data
    const notaResponse = await axios.get('/api/nota-per-year');
    notaDinasData.value = notaResponse.data;

    // top SKPD data
    const skpdResponse = await axios.get('/api/skpd/top-serapan');
    topSkpdData.value = skpdResponse.data;

    // kabupaten serapan data
    const kabupatenResponse = await axios.get('/api/kabupaten-serapan');
    kabupatenData.value = kabupatenResponse.data;

    // Top SKPD Chart
    const ctx2 = document.getElementById('topSkpdChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: topSkpdData.value.map(item => item.nama_skpd),
            datasets: [{
                label: 'Persentase Serapan (%)',
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
                    display: true
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

    // Nota dinas chart
    const ctx1 = document.getElementById('notaDinasChart').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: notaDinasData.value.map(item => item.year),
        datasets: [{
          label: 'Total Nota Dinas',
          data: notaDinasData.value.map(item => item.total),
          backgroundColor: '#cc65fe',
          borderColor: '#cc65fe',
          borderWidth: 2,
          tension: 0.1,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
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

    // Kabupaten serapan chart
    const ctx3 = document.getElementById('kabupatenChart').getContext('2d');
    new Chart(ctx3, {
      type: 'bar',
      data: {
        labels: kabupatenData.value.labels,
        datasets: [
          {
            label: 'Total Serapan (Rp)',
            data: kabupatenData.value.total_serapan,
            backgroundColor: '#36a2eb',
            borderColor: '#36a2eb',
            borderWidth: 1,
            yAxisID: 'y',
            order:1
          },
          {
            label: 'Persentase Serapan (%)',
            data: kabupatenData.value.presentase_serapan,
            backgroundColor: '#ffce56',
            borderColor: '#ffce56',
            borderWidth: 7,
            type: 'line',
            yAxisID: 'y1',
            order:0
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    let value = context.formattedValue;

                    if (label.includes('Total')) {
                        return `${label}: Rp ${formatNumber(context.raw)}`;
                    } else if (label.includes('Persentase')) {
                        return `${label}: ${value}%`;
                    }
                    return label + ': ' + value;
                }
            }
          },
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'Total Serapan (Rp)'
            },
            ticks: {
              callback: function(value) {
                return formatNumber(value);
              }
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
              display: true,
              text: 'Persentase (%)'
            },
            min: 0,
            max: 100,
            grid: {
              drawOnChartArea: false,
            },
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          },
          x: {
            title: {
              display: true,
              text: 'Tahun Anggaran'
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