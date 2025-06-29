<!-- components/RekapNotaProgress.vue -->
<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <div
      v-for="item in rekap"
      :key="item.jenis"
      class="bg-white p-4 border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition duration-200"
    >
      <div class="flex justify-between items-center mb-2">
        <span class="text-base font-semibold text-gray-800 capitalize">{{ item.jenis }}</span>
        <span class="text-sm text-gray-500">{{ item.jumlah }} Nota Dinas</span>
      </div>
      <div class="relative w-full bg-gray-200 h-3 rounded-full overflow-hidden">
        <div
          class="h-full transition-all duration-500"
          :class="progressColor(item.jenis)"
          :style="{ width: `${(item.jumlah / maxJumlah * 100).toFixed(1)}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  rekap: Array,
  maxJumlah: Number,
  progressColor: Function
});
</script>
