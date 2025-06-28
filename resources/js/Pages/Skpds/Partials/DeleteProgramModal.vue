<script setup>
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  program: Object,
});

const emit = defineEmits(['close', 'success']);

const confirmDeleteProgram = () => {
  if (props.program) {
    router.delete(route('programs.destroy', props.program.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('success');
        emit('close');
      },
      onError: (errors) => {
        console.error("Error deleting program:", errors);
        emit('close');
      }
    });
  }
};

const closeModal = () => {
  emit('close');
};
</script>

<template>
  <Modal :show="show" @close="closeModal">
    <div class="p-4 text-center">
      <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl fa-fade"/>
      <h2 class="text-lg font-medium text-gray-900">
        Konfirmasi Hapus
      </h2>

      <p class="mt-1 text-sm text-gray-600">
        Apakah Anda yakin ingin menghapus program <span class="text-red-500 font-semibold">{{ program?.nama }}</span>?
      </p>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="closeModal">Batal</SecondaryButton>
        <DangerButton @click="confirmDeleteProgram" class="ml-3">Hapus</DangerButton>
      </div>
    </div>
  </Modal>
</template>

