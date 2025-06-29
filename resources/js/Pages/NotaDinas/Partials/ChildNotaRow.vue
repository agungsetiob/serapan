<template>
  <tr>
    <td colspan="6" class="py-4">
      <div class="ml-8 border-l-2 border-red-400 pl-4">
        <h4 class="font-medium text-blue-700 mb-2 flex items-center gap-2">
          Nota Terkait
          <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500 text-white">
            {{ children.length }} Nota
          </span>
        </h4>
        <table class="table-auto w-full">
          <thead class="bg-gray-200">
            <tr class="text-left">
              <th class="px-4 py-2">No. Nota</th>
              <th class="px-4 py-2">Perihal</th>
              <th class="px-4 py-2">Anggaran</th>
              <th class="px-4 py-2">Tanggal</th>
              <th class="px-4 py-2">Jenis</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="child in children"
              :key="child.id"
              class="even:bg-gray-100"
            >
              <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                {{ child.nomor_nota }}
              </td>
              <td class="px-4 py-2 text-sm">
                {{ child.perihal }}
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                {{ formatCurrency(child.anggaran) }}
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                {{ formatDate(child.tanggal_pengajuan) }}
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                <span :class="badgeClasses(child.jenis)">
                  {{ child.jenis }}
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm font-medium space-x-1">
                <Tooltip text="Edit" bgColor="bg-blue-500">
                  <button
                    @click="$emit('edit', child)"
                    class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-200 transition-colors"
                  >
                    <font-awesome-icon :icon="['fas', 'pen-to-square']" />
                  </button>
                </Tooltip>
                <Tooltip text="Lampiran" bgColor="bg-purple-500">
                  <button
                    @click="$emit('view-attachment', child)"
                    class="text-purple-600 hover:text-purple-800 px-2 py-1 rounded hover:bg-purple-200 transition-colors"
                  >
                    <font-awesome-icon :icon="['fas', 'paperclip']" />
                  </button>
                </Tooltip>
                <Tooltip text="Hapus" bgColor="bg-red-500">
                  <button
                    @click="$emit('delete', child)"
                    class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-200 transition-colors"
                  >
                    <font-awesome-icon :icon="['fas', 'trash']" />
                  </button>
                </Tooltip>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </td>
  </tr>
</template>

<script setup>
import { formatCurrency, formatDate } from '@/Utils/formatters';
import Tooltip from '@/Components/Tooltip.vue';

const props = defineProps({
  children: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['edit', 'view-attachment', 'delete']);

const badgeClasses = (jenis) => {
  const base = 'px-2 py-1 rounded-full text-xs font-medium';
  switch(jenis) {
    case 'GU': return `${base} bg-yellow-100 text-yellow-800`;
    case 'TU': return `${base} bg-indigo-100 text-indigo-800`;
    case 'LS': return `${base} bg-red-100 text-red-800`;
    default: return `${base} bg-gray-100 text-gray-800`;
  }
};
</script>