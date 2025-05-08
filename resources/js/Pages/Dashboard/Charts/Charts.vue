<template>
    <div class="space-y-6">
  
      <!-- Approved Nota Dinas per SKPD -->
      <!-- <div class="bg-gray-100 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
          <h3 class="font-semibold text-gray-700">Nota Dinas Disetujui per SKPD</h3>
        </div>
        <div class="p-4">
          <canvas id="approvedNotaDinasChart" class="h-64"></canvas>
        </div>
      </div> -->
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Total Nota Dinas per Year card -->
        <div class="bg-gray-100 rounded-lg shadow overflow-hidden">
          <div class="p-4 border-b">
            <h3 class="font-semibold text-gray-700">Nota Dinas per Tahun</h3>
          </div>
          <div class="p-4">
            <canvas id="notaDinasChart" class="h-64"></canvas>
          </div>
        </div>
        
        <!-- Nota Dinas by Stage -->
        <!-- <div class="bg-gray-100 rounded-lg shadow overflow-hidden">
          <div class="p-4 border-b">
            <h3 class="font-semibold text-gray-700">Nota Dinas Berdasarkan Tahap</h3>
          </div>
          <div class="p-4">
            <canvas id="notaDinasStageChart" class="h-64"></canvas>
          </div>
        </div> -->
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
  const approvedNotaDinasData = ref([]);
  
  // Fetch chart data
  onMounted(async () => {
      try {
          // const response = await axios.get('/nota-dinas-stage');
          // chartData.value = response.data;
  
          const notaResponse = await axios.get('/nota-per-year');
          notaDinasData.value = notaResponse.data;
  
          // const approvedResponse = await axios.get('/approved-nota-dinas');
          // approvedNotaDinasData.value = approvedResponse.data;
  
          // Nota Dinas by Stage
          // const ctx = document.getElementById('notaDinasStageChart').getContext('2d');
          // new Chart(ctx, {
          //     type: 'doughnut',
          //     data: {
          //         labels: chartData.value.map(item => item.tahap_saat_ini),
          //         datasets: [{
          //             label: 'Total Nota Dinas',
          //             data: chartData.value.map(item => item.total),
          //             backgroundColor: [
          //               '#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0'
          //             ],
          //             hoverOffset: 4
          //         }]
          //     },
          //     options: {
          //         responsive: true,
          //         maintainAspectRatio: false,
          //         plugins: {
          //             legend: {
          //               position: 'bottom'
          //             }
          //         }
          //     }
          // });
  
          // Total Nota Dinas per Year
          const ctx1 = document.getElementById('notaDinasChart').getContext('2d');
          new Chart(ctx1, {
              type: 'line',
              data: {
                  labels: notaDinasData.value.map(item => item.year),
                  datasets: [{
                      label: 'Total Nota Dinas',
                      data: notaDinasData.value.map(item => item.total),
                      backgroundColor: 'rgba(255, 99, 132, 0.5)',
                      borderColor: 'rgb(255, 99, 132)',
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
  
          // Approved Nota Dinas per SKPD
          // const ctx2 = document.getElementById('approvedNotaDinasChart').getContext('2d');
          // new Chart(ctx2, {
          //     type: 'bar',
          //     data: {
          //         labels: approvedNotaDinasData.value.map(item => `${item.nama_skpd}`),
          //         datasets: [{
          //             label: 'Total Nota Dinas',
          //             data: approvedNotaDinasData.value.map(item => item.total),
          //             backgroundColor: 'rgba(75, 192, 192, 0.5)',
          //             borderColor: 'rgba(75, 192, 192, 1)',
          //             borderWidth: 1,
          //         }]
          //     },
          //     options: {
          //         responsive: true,
          //         maintainAspectRatio: false,
          //         plugins: {
          //             legend: {
          //                 display: false
          //             }
          //         },
          //         scales: { 
          //           y: { 
          //               beginAtZero: true,
          //               ticks: {
          //                   precision: 0
          //               }
          //           },
          //           x:{
          //               ticks: {
          //                   display: false
          //               }
          //           }
          //         }
          //     }
          // });
  
      } catch (error) {
          console.error('Error fetching chart data:', error);
      }
  });
  </script>