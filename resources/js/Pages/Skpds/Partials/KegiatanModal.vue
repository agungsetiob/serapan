<template>
  <Modal :show="show" :title="isEditing ? 'Edit Kegiatan' : 'Hapus Kegiatan'" maxWidth="2xl" closeable
    @close="closeModal">
    <template #default>
      <div class="p-6">
        <!-- Edit Form -->
        <form v-if="isEditing" @submit.prevent="updateKegiatan">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-medium mb-1">Nama Kegiatan</label>
            <TextInput v-model="form.nama" class="w-full" placeholder="Masukkan nama kegiatan" />
            <InputError :message="form.errors.nama" />
          </div>

          <div class="flex justify-end gap-2">
            <SecondaryButton @click="closeModal" :disabled="form.processing">
              Batal
            </SecondaryButton>
            <PrimaryButton :disabled="form.processing">
              <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
              {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </PrimaryButton>
          </div>
        </form>

        <!-- Delete Confirmation -->
        <div v-else>
          <div class="text-center">
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl" />
            <div class="mt-2 text-gray-700">
              Hapus sub kegiatan
              <span class="font-semibold text-red-400">{{ form.nama }}</span>?
              <br>Data yang dihapus tidak dapat dikembalikan.
            </div>
            <div class="flex justify-end gap-2">
              <SecondaryButton @click="closeModal">
                Batal
              </SecondaryButton>
              <DangerButton
                @click="confirmDelete"
                :disabled="form.processing"
                >
                <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
                {{ form.processing ? 'Menghapus...' : 'Hapus' }}
              </DangerButton>
            </div>
          </div>
        </div>

      </div>
    </template>
  </Modal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
  show: Boolean,
  kegiatan: Object,
  isEditing: Boolean,
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
  id: null,
  nama: '',
});

watch(
  () => props.show,
  (val) => {
    if (val && props.kegiatan) {
      form.id = props.kegiatan.id || null;
      form.nama = props.kegiatan.nama || '';
    } else if (!val) {
      form.reset();
    }
  },
  { immediate: true }
);

const closeModal = () => {
  emit('close');
};

const updateKegiatan = () => {
  form.put(route('kegiatans.update', { kegiatan: form.id }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('success', 'Kegiatan berhasil diperbarui');
      closeModal();
    },
  });
};

const confirmDelete = () => {
  form.delete(route('kegiatans.destroy', { kegiatan: form.id }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('success', 'Kegiatan berhasil dihapus');
      closeModal();
    },
  });
};
</script>