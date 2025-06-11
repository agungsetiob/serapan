<template>
  <tr class="bg-gray-50">
    <td colspan="6" class="px-6 py-4">
      <div class="ml-8 border-l-2 border-red-200 pl-4">
        <h4 class="font-medium text-gray-700 mb-2 flex items-center gap-2">
          <font-awesome-icon :icon="['fas', 'file-circle-plus']" class="text-gray-500" />
          Nota Terkait
        </h4>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">No. Nota</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Perihal</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Anggaran</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Jenis</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="child in children"
              :key="child.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ child.nomor_nota }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                {{ child.perihal }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                {{ formatCurrency(child.anggaran) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                {{ formatDate(child.tanggal_pengajuan) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                <span :class="badgeClasses(child.jenis)">
                  {{ child.jenis }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium space-x-1">
                <button
                  @click="$emit('edit', child)"
                  class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition-colors"
                  title="Edit"
                >
                  <font-awesome-icon :icon="['fas', 'pen-to-square']" />
                </button>
                <button
                  @click="$emit('view-attachment', child)"
                  class="text-purple-600 hover:text-purple-800 p-1 rounded hover:bg-purple-50 transition-colors"
                  title="Lampiran"
                >
                  <font-awesome-icon :icon="['fas', 'paperclip']" />
                </button>
                <button
                  @click="$emit('delete', child)"
                  class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition-colors"
                  title="Hapus"
                >
                  <font-awesome-icon :icon="['fas', 'trash']" />
                </button>
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