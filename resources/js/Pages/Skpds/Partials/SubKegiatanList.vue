<script setup>
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Tooltip from '@/Components/Tooltip.vue';

const props = defineProps({
  kegiatan: Object,
  formSubKegiatan: Object,
  formatNumber: Function,
  onSubmit: Function,
  onEdit: Function,
  onDelete: Function
});
const emit = defineEmits(['createNotaDinas']);

</script>

<template>
  <div class="px-6 py-4">
    <h4 class="font-medium text-gray-700 mb-3 flex items-center">
      <font-awesome-icon :icon="['fas', 'suitcase']" class="mr-1 text-red-500"/>
      Daftar Sub Kegiatan
    </h4>

    <ul class="mb-2">
      <li
        v-for="sub in kegiatan.sub_kegiatans"
        :key="sub.id"
        class="py-4 px-2 hover:bg-gray-50 rounded-lg transition-colors duration-150"
      >
        <div class="flex flex-col sm:flex-row justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start gap-2">
              <span class="text-gray-800 font-medium truncate">{{ sub.nama }}</span>
            </div>

            <div class="mt-1 text-sm text-gray-600 space-y-1">
              <div class="flex flex-wrap gap-x-3 gap-y-1">
                <span>Pagu: Rp {{ formatNumber(sub.pagu) }}</span>
                <span>Serapan: Rp {{ formatNumber(sub.total_serapan) }}</span>
                <span class="font-medium" :class="{
                  'text-green-600': sub.presentase_serapan >= 80,
                  'text-yellow-600': sub.presentase_serapan >= 50 && sub.presentase_serapan < 80,
                  'text-red-600': sub.presentase_serapan < 50
                }">
                  {{ sub.presentase_serapan }}%
                </span>
              </div>

              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="{
                    'bg-green-500': sub.presentase_serapan >= 80,
                    'bg-yellow-500': sub.presentase_serapan >= 50 && sub.presentase_serapan < 80,
                    'bg-red-500': sub.presentase_serapan < 50
                  }"
                  :style="{ width: sub.presentase_serapan + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3 sm:gap-2">
            <Tooltip text="Nota Dinas" bgColor="bg-gray-500">
              <button 
                @click="emit('createNotaDinas', sub)"
                class="text-gray-600 hover:text-gray-800 px-1 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-gray-500"
              >
                <font-awesome-icon :icon="['fas', 'file-circle-plus']" class="text-sm sm:text-base" />
              </button>
            </Tooltip>
            <Tooltip text="Edit" bgColor="bg-blue-500">
              <button 
                @click="onEdit(sub, kegiatan)" 
                class="text-blue-600 hover:text-blue-800 px-1 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500"
              >
                <font-awesome-icon :icon="['fas', 'pen-to-square']" class="text-sm sm:text-base" />
              </button>
            </Tooltip>
            <Tooltip text="Hapus" bgColor="bg-red-500">
              <button 
                @click="onDelete(sub, kegiatan)" 
                class="text-red-600 hover:text-red-800 px-1 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500"
              >
                <font-awesome-icon :icon="['fas', 'trash-can']" class="text-sm sm:text-base" />
              </button>
            </Tooltip>
          </div>
        </div>
      </li>
    </ul>

    <!-- Tambah Sub Kegiatan Form -->
    <div v-if="formSubKegiatan" class="bg-gray-50 p-4 rounded-lg">
      <h5 class="text-sm font-medium text-gray-700 mb-3">
        Tambah Sub Kegiatan
      </h5>
      <form @submit.prevent="onSubmit(kegiatan.id)" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
          <div class="md:col-span-2">
            <TextInput v-model="formSubKegiatan.nama" class="w-full px-3 py-2 text-sm" placeholder="Nama Sub Kegiatan"/>
            <InputError :message="formSubKegiatan.errors.nama" />
          </div>
          <div>
            <TextInput v-model="formSubKegiatan.pagu" class="w-full px-3 py-2 text-sm" placeholder="Pagu"/>
            <InputError :message="formSubKegiatan.errors.pagu" />
          </div>
          <div>
            <TextInput v-model="formSubKegiatan.tahun_anggaran" class="w-full px-3 py-2 text-sm" readonly />
          </div>
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            class="inline-flex px-3 py-1.5 border text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
