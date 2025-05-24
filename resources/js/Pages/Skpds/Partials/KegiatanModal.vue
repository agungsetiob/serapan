<template>
    <Modal
      :show="showModal"
      :title="isEditing ? 'Edit Kegiatan' : 'Hapus Kegiatan'"
      maxWidth="lg"
      closeable
      @close="closeModal"
    >
      <template #default>
        <div class="p-6">
          <!-- Edit Form -->
          <form v-if="isEditing" @submit.prevent="updateKegiatan">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-medium mb-1">Nama Sub Kegiatan</label>
              <TextInput
                v-model="form.nama"
                class="w-full"
                placeholder="Masukkan nama kegiatan"
              />
              <InputError :message="form.errors.nama" />
            </div>
    
            <div class="flex justify-end gap-2 mt-6">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
              >
                Batal
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                :disabled="form.processing"
              >
                <span v-if="form.processing">
                  <font-awesome-icon icon="spinner" spin class="mr-2" />Menyimpan...
                </span>
                <span v-else>Simpan</span>
              </button>
            </div>
          </form>
    
          <!-- Delete Confirmation -->
          <div v-else>
            <div class="text-center">
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl"/>
              <div class="mt-2 text-gray-700">
                Hapus sub kegiatan
                <span class="font-semibold text-red-400">{{ form.nama }}</span>? 
                <br>Data yang dihapus tidak dapat dikembalikan.
              </div>
              <div class="flex justify-end gap-2 mt-6">
                <button
                  @click="closeModal"
                  type="button"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
                >
                  Batal
                </button>
                <button
                  @click="confirmDelete"
                  type="button"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">
                    <font-awesome-icon icon="spinner" spin class="mr-2" />Menghapus...
                  </span>
                  <span v-else>Ya, Hapus</span>
                </button>
              </div>
            </div>
          </div>
    
        </div>
      </template>
    </Modal>
</template>
    
<script setup>
  import { ref, watch } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import Modal from '@/Components/Modal.vue';
  import InputError from '@/Components/InputError.vue';
  import TextInput from '@/Components/TextInput.vue';
  
  const props = defineProps({
    show: Boolean,
    kegiatan: Object,
    isEditing: Boolean,
  });
  
  const emit = defineEmits(['close', 'success']);
  
  const showModal = ref(props.show);
  
  const form = useForm({
    id: null,
    nama: '',
  });
  
  watch(
    () => props.show,
    (val) => {
      showModal.value = val;
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
    showModal.value = false;
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
  