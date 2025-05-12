<template>
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">
            {{ kegiatan.nama }}
          </h3>
          <div class="flex flex-col md:flex-row md:items-center gap-2 mt-1 text-sm text-gray-600">
            <div>
              <span class="font-semibold">Pagu:</span>
              Rp. {{ formatNumber(kegiatan.pagu) }}
            </div>
            <div class="hidden md:block mx-2">|</div>
            <div>
              <span class="font-semibold">Serapan:</span>
              Rp {{ formatNumber(kegiatan.total_serapan) }}
            </div>
            <div class="hidden md:block mx-2">|</div>
            <div class="flex items-center gap-1">
              <span class="font-semibold">Persentase:</span>
              <span class="text-green-600 font-medium">{{ kegiatan.presentase_serapan }}%</span>
              <div class="w-48 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full bg-green-500 transition-all duration-500 ease-out"
                  :style="{ width: kegiatan.presentase_serapan + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="text-right space-y-1">
          <div>
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
              {{ kegiatan.tahun_anggaran }}
            </span>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
              {{ kegiatan.sub_kegiatans.length }} Sub Kegiatan
            </span>
          </div>
          <div class="flex justify-end gap-2 mt-2">
            <Tooltip text="Edit" bgColor="bg-blue-500">
              <button
                @click="editKegiatan(kegiatan)"
                class="text-blue-600 hover:text-blue-800 p-1"
              >
                <font-awesome-icon :icon="['far', 'edit']" class="text-sm sm:text-base" />
              </button>
            </Tooltip>
            <Tooltip text="Hapus" bgColor="bg-red-500">
              <button
                @click="deleteKegiatan(kegiatan)"
                class="text-red-600 hover:text-red-800 p-1"
              >
                <font-awesome-icon :icon="['far', 'trash-can']" class="text-sm sm:text-base" />
              </button>
            </Tooltip>
          </div>
        </div>
    </div>
</template>
  
<script setup>
  import Tooltip from '@/Components/Tooltip.vue';
  defineProps({
    kegiatan: Object
  });
    
  const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
  };

const emit = defineEmits(['edit', 'delete']);

const editKegiatan = (kegiatan) => {
  emit('edit', kegiatan);
};

const deleteKegiatan = (kegiatan) => {
  emit('delete', kegiatan);
};

</script>
  