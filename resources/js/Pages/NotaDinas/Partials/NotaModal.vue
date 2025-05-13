<script setup>
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: Boolean,
  notaData: Object,
  subKegiatan: {
    type: Object,
    required: false,
  }
});

const emit = defineEmits(['close']);

const updateFormWithNotaData = () => {
};

watch(
  () => props.notaData,
  () => {
    updateFormWithNotaData();
  },
  { immediate: true }
);

const closeModal = () => {
  emit('close');
};

</script>

<template>
  <Modal :show="show" @close="closeModal" maxWidth="5xl">
    <div class="bg-white p-4 sm:p-6 rounded-lg">
      
      <!-- Sub Kegiatan Info -->
      <div class="mb-4 p-4 bg-gray-50 rounded-md border">
        <h4 class="text-sm font-medium text-gray-700 mb-1">Sub Kegiatan Terkait</h4>
        <p class="font-medium">{{ subKegiatan.nama }}</p>
        <p class="text-sm text-gray-600 mt-1">
          Pagu: Rp {{ new Intl.NumberFormat('id-ID').format(subKegiatan.pagu) }}
        </p>
      </div>
    </div>
  </Modal>
</template>
