<template>
  <div class="bg-white rounded-xl p-5 shadow-md border border-gray-100 mb-4">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-lg font-semibold text-gray-900">Rekap Nota Dinas</h2>
      <div class="flex items-center gap-2">
        <font-awesome-icon icon="fas fa-filter" class="text-gray-400 text-sm"/>
        <select 
          v-model="tahun" 
          @change="loadRekap" 
          class="rounded-md border-gray-300 text-sm"
        >
          <option v-for="t in daftarTahun" :key="t" :value="t">{{ t }}</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div
        v-for="(item, index) in rekap"
        :key="item.jenis"
        class="rounded-xl p-2 shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative group"
      >
        <div 
          :class="[
            'absolute inset-0 opacity-20 -z-0',
            colors[index % colors.length].split(' ')[0],
            colors[index % colors.length].split(' ')[3]
          ]"
        ></div>
        <div class="relative z-10">
          <p class="text-xs font-medium text-gray-500">Jenis</p>
          <h3 class="text-lg font-semibold text-gray-900 mt-1">{{ item.jenis }}</h3>
          <div class="flex items-end justify-between mt-1">
            <p class="text-2xl font-bold text-gray-900">{{ item.jumlah }}</p>
            <div 
              :class="[
                'p-2 rounded-lg transition-all group-hover:scale-110',
                colors[index % colors.length]
              ]"
            >
              <font-awesome-icon icon="fas fa-file-lines" class="text-white text-lg"/>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const tahun = ref(new Date().getFullYear());
const daftarTahun = Array.from({ length: 5 }, (_, i) => tahun.value - i);
const rekap = ref([]);

const loadRekap = async () => {
  try {
    const response = await axios.get('/api/rekap-nota', {
      params: { tahun: tahun.value }
    });
    rekap.value = response.data.data;
  } catch (error) {
    console.error('Gagal memuat data:', error);
  }
};

onMounted(loadRekap);

const colors = [
  'bg-gradient-to-r from-rose-400 via-fuchsia-500 to-indigo-500',
  'bg-gradient-to-r from-emerald-400 to-cyan-400',
  'bg-gradient-to-r from-amber-400 to-orange-500',
  'bg-gradient-to-r from-violet-400 to-purple-500',
  'bg-gradient-to-r from-sky-400 to-blue-500',
  'bg-gradient-to-r from-lime-400 to-green-500',
  'bg-gradient-to-r from-pink-400 to-rose-500',
  'bg-gradient-to-r from-indigo-400 to-blue-500',
  'bg-gradient-to-r from-yellow-400 to-amber-500'
];
</script>
