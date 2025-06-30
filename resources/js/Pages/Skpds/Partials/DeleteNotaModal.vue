<template>
  <Modal :show="show" @close="closeModal">
    <div class="text-center p-4">
        <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl fa-fade"/>
        <h2 class="text-lg font-medium text-gray-900">
            Konfirmasi Hapus Nota Dinas
        </h2>
        <p class="text-gray-600 mb-6">
            Hapus Nota Dinas <span class="font-semibold text-red-400">{{ notaDinas.nomor_nota }}</span>?
            <br>
            Anggaran sebesar Rp {{ formatCurrency(notaDinas.anggaran) }} akan dikembalikan ke sub kegiatan.
        </p>

        <div class="flex justify-end space-x-3">
            <SecondaryButton @click="closeModal">
              Batal
            </SecondaryButton>
            <DangerButton
              @click="confirmDelete"
              :disabled="isDeleting"
              >
              <font-awesome-icon v-if="isDeleting" icon="spinner" spin class="mr-2" />
              {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
            </DangerButton>
        </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  notaDinas: Object
});

const emit = defineEmits(['close']);

const isDeleting = ref(false);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value);
};

const closeModal = () => {
  emit('close');
};

const confirmDelete = async () => {
  isDeleting.value = true;
  try {
    await router.delete(route('nota-dinas.destroy', props.notaDinas.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
      },
      onFinish: () => {
        isDeleting.value = false;
      }
    });
  } catch (error) {
    isDeleting.value = false;
    console.error('Error deleting nota dinas:', error);
  }
};
</script>