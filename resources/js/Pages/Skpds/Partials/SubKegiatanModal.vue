<template>
    <Modal
      :show="showModal"
      :title="isEditing ? 'Edit Sub Kegiatan' : 'Hapus Sub Kegiatan'"
      maxWidth="4xl"
      closeable
      @close="closeModal"
    >
      <template #default>
        <div class="p-6">
          <!-- Edit Form -->
          <form v-if="isEditing" @submit.prevent="updateSubKegiatan">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-medium mb-1">Nama Sub Kegiatan</label>
              <TextInput v-model="form.nama" class="w-full" placeholder="Masukkan nama sub kegiatan" />
              <InputError :message="form.errors.nama" />
            </div>
  
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-medium mb-1">Pagu</label>
              <TextInput v-model="form.pagu" class="w-full" placeholder="Masukkan pagu" type="number" />
              <InputError :message="form.errors.pagu" />
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
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>Simpan</span>
              </button>
            </div>
          </form>
  
          <!-- Delete Confirmation -->
          <div v-else>
            <div class="text-center">
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl fa-fade"/>
              <div class="mt-2 text-sm text-gray-700">
                Hapus sub kegiatan <span class="font-semibold text-red-400">{{ form.nama }}</span>? Data yang dihapus tidak dapat dikembalikan.
              </div>
  
              <div class="flex justify-end gap-2 mt-6">
                <button
                  @click="closeModal"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
                >
                  Batal
                </button>
                <button
                  @click="confirmDelete"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Menghapus...</span>
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
    subKegiatan: Object,
    isEditing: Boolean,
  });
  
  const emit = defineEmits(['close', 'success']);
  
  const showModal = ref(props.show);
  const form = useForm({
    id: null,
    nama: '',
    pagu: 0,
    kegiatan_id: null,
    tahun_anggaran: '',
  });
  
  watch(() => props.show, (val) => {
    showModal.value = val;
  });

  watch(
    [() => props.show, () => props.subKegiatan],
    ([show, subKegiatan]) => {
      if (show && subKegiatan) {
        form.id = subKegiatan.id ?? null;
        form.nama = subKegiatan.nama ?? '';
        form.pagu = subKegiatan.pagu ?? 0;
        form.kegiatan_id = subKegiatan.kegiatan_id ?? props.kegiatan?.id ?? null;
        form.tahun_anggaran = subKegiatan.tahun_anggaran ?? '';
      }
    },
    { immediate: true }
  );

  const closeModal = () => {
    showModal.value = false;
    form.reset();
    emit('close');
  };
  
    const updateSubKegiatan = () => {
      form.put(route('kegiatans.subkegiatans.update', { kegiatan: props.kegiatan.id, subkegiatan: form.id }), {
            preserveScroll: true,
            onSuccess: () => {
            emit('success', 'Sub kegiatan berhasil diperbarui');
            closeModal();
            },
        });
    };

    const confirmDelete = () => {
      form.delete(route('kegiatans.subkegiatans.destroy', { kegiatan: props.kegiatan.id, subkegiatan: form.id }), {
            preserveScroll: true,
            onSuccess: () => {
            emit('success', 'Sub kegiatan berhasil dihapus');
            closeModal();
            },
        });
    };

</script>
  