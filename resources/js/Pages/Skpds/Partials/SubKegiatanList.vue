<script setup>
import { ref } from 'vue';
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

const emit = defineEmits(['createNotaDinas', 'editNota', 'deleteNota', 'viewAttachment']);

// Track which sub-kegiatan's nota dinas are expanded
const expandedSubKegiatans = ref([]);

const toggleNotaDinas = (subKegiatanId) => {
  const index = expandedSubKegiatans.value.indexOf(subKegiatanId);
  if (index === -1) {
    expandedSubKegiatans.value.push(subKegiatanId);
  } else {
    expandedSubKegiatans.value.splice(index, 1);
  }
};

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const badgeClasses = (jenis) => {
  switch(jenis) {
    case 'Pelaksanaan': return `bg-yellow-100 text-yellow-800`;
    case 'TU': return `bg-indigo-100 text-indigo-800`;
    case 'LS': return `bg-red-100 text-red-800`;
    default: return `bg-gray-100 text-gray-800`;
  }
}
</script>

<template>
  <div class="px-6 py-4">
    <h4 class="font-medium text-gray-700 mb-3 flex items-center">
      <font-awesome-icon :icon="['fas', 'suitcase']" class="mr-1 text-red-500"/>
      Daftar Uraian Sub Kegiatan
    </h4>

    <ul class="mb-2">
      <li
        v-for="sub in kegiatan.sub_kegiatans"
        :key="sub.id"
        class="py-4 px-2 hover:bg-gray-100 rounded-lg transition-colors duration-150"
      >
        <div class="flex flex-col gap-3 rounded-md">
          <!-- Sub Kegiatan Header -->
          <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start gap-2">
                <button 
                  @click="toggleNotaDinas(sub.id)"
                  class="text-left text-blue-600 hover:text-green-600 font-medium"
                >
                  {{ sub.kode_rekening }} - {{ sub.nama }}
                  <font-awesome-icon 
                    :icon="['fas', expandedSubKegiatans.includes(sub.id) ? 'chevron-up' : 'chevron-down']" 
                    class="ml-2 text-sm"
                  />
                </button>
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
                  class="px-2 py-1 text-xs sm:text-sm rounded text-gray-600 hover:bg-gray-200"
                >
                  <font-awesome-icon :icon="['fas', 'file-circle-plus']" class="text-sm sm:text-base" />
                </button>
              </Tooltip>
              <Tooltip text="Edit" bgColor="bg-blue-500">
                <button 
                  @click="onEdit(sub, kegiatan)" 
                  class="px-2 py-1 text-xs sm:text-sm rounded text-blue-600 hover:bg-blue-200"
                >
                  <font-awesome-icon :icon="['fas', 'edit']" class="text-sm sm:text-base" />
                </button>
              </Tooltip>
              <Tooltip text="Hapus" bgColor="bg-red-500">
                <button 
                  @click="onDelete(sub, kegiatan)" 
                  class="px-2 py-1 text-xs sm:text-sm rounded text-red-600 hover:bg-red-100"
                >
                  <font-awesome-icon :icon="['fas', 'trash-can']" class="text-sm sm:text-base" />
                </button>
              </Tooltip>
            </div>
          </div>

          <!-- Nota Dinas List -->
          <div 
            v-if="expandedSubKegiatans.includes(sub.id)"
            class="mt-3 bg-gray-50 rounded-lg p-4 transition-all duration-300"
          >
            <div class="mb-4">
              <h5 class="text-sm font-medium text-gray-700 mb-2">
                Daftar Nota Dinas
              </h5>
              
              <div v-if="sub.nota_dinas && sub.nota_dinas.length" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium">
                        Nomor
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium">
                        Tanggal
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium">
                        Perihal
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium">
                        Anggaran
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium">
                        Jenis
                      </th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="nota in sub.nota_dinas" :key="nota.id" class=" hover:bg-gray-100">
                      <td class="px-4 py-3 whitespace-nowrap text-sm">
                        {{ nota.nomor_nota }}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-sm">
                        {{ formatDate(nota.tanggal_pengajuan) }}
                      </td>
                      <td class="px-4 py-3 text-sm">
                        {{ nota.perihal }}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-sm">
                        Rp {{ formatNumber(nota.anggaran) }}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <span 
                          :class="['inline-flex items-center px-1 rounded-full text-xs font-medium', badgeClasses(nota.jenis)]"
                        >
                          {{ nota.jenis }}
                        </span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                          <Tooltip text="Lampiran" bgColor="bg-purple-500">
                            <button 
                              @click="emit('viewAttachment', nota)" 
                              class="px-2 py-1 text-xs sm:text-sm rounded text-purple-600 hover:bg-purple-200"
                            >
                              <font-awesome-icon icon="paperclip" />
                            </button>
                          </Tooltip>
                          <Tooltip text="Edit" bgColor="bg-blue-400">
                            <button 
                              @click="emit('editNota', nota)" 
                              class="px-2 py-1 text-xs sm:text-sm rounded text-blue-600 hover:bg-blue-100"
                            >
                              <font-awesome-icon icon="edit" />
                            </button>
                          </Tooltip>
                          <Tooltip text="Hapus" bgColor="bg-red-500">
                            <button 
                              @click="emit('deleteNota', nota)" 
                              class="px-2 py-1 text-xs sm:text-sm rounded text-red-600 hover:bg-red-100"
                            >
                              <font-awesome-icon icon="trash"/>
                            </button>
                          </Tooltip>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-4 text-gray-500 text-sm">
                Belum ada nota dinas untuk uraian sub kegiatan ini.
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>

    <!-- Tambah Sub Kegiatan Form -->
    <div v-if="formSubKegiatan" class="bg-gray-50 p-4 rounded-lg">
      <h5 class="text-sm font-medium text-gray-700 mb-3">
        Tambah Uraian Sub Kegiatan
      </h5>
      <form @submit.prevent="onSubmit(kegiatan.id)" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
          <div>
            <TextInput v-model="formSubKegiatan.kode_rekening" class="w-full px-3 py-2 text-sm" placeholder="Kode Rekening"/>
            <InputError :message="formSubKegiatan.errors.kode_rekening" />
          </div>
          <div class="md:col-span-3">
            <TextInput v-model="formSubKegiatan.nama" class="w-full px-3 py-2 text-sm" placeholder="Uraian Sub Kegiatan"/>
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