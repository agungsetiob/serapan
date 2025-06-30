<template>
  <Modal :show="showModal" :title="isEditing ? 'Edit Uraian Sub Kegiatan' : 'Hapus Uraian Sub Kegiatan'" maxWidth="2xl"
    closeable @close="closeModal">
    <template #default>
      <div class="p-6">
        <!-- Edit Form -->
        <form v-if="isEditing" @submit.prevent="updateSubKegiatan">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-medium mb-1">Kode Rekening</label>
            <TextInput v-model="form.kode_rekening" class="w-full" placeholder="Masukkan kode rekening" />
            <InputError :message="form.errors.kode_rekening" />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-medium mb-1">Nama Uraian Sub Kegiatan</label>
            <TextInput v-model="form.nama" class="w-full" placeholder="Masukkan uraian sub kegiatan" />
            <InputError :message="form.errors.nama" />
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-medium mb-1">Pagu</label>
            <TextInput v-model="formattedPagu" class="w-full" placeholder="Masukkan pagu" type="text"
              @input="updatePagu($event.target.value)" />
            <InputError :message="form.errors.pagu" />
          </div>

          <div class="flex justify-end gap-2">
            <SecondaryButton @click="closeModal" :disabled="form.processing">
              Batal
            </SecondaryButton>
            <PrimaryButton :disabled="form.processing">
              <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
              {{ form.processing ? 'Mengupdate...' : 'Update' }}
            </PrimaryButton>
          </div>
        </form>

        <!-- Delete Confirmation -->
        <div v-else>
          <div class="text-center">
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500 text-4xl fa-fade" />
            <div class="mt-2 text-gray-700">
              Hapus uraian sub kegiatan <span class="font-semibold text-red-400">{{ form.nama }}</span>?
              <br> Data yang dihapus tidak dapat dikembalikan.
            </div>

            <div class="flex justify-end gap-2">
              <SecondaryButton @click="closeModal" :disabled="form.processing">
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
import { ref, watch, computed } from 'vue';
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
  subKegiatan: Object,
  isEditing: Boolean,
});

const emit = defineEmits(['close', 'success']);

const showModal = ref(props.show);
const form = useForm({
  id: null,
  kode_rekening: '',
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
      form.kode_rekening = subKegiatan.kode_rekening ?? '';
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

const formattedPagu = computed({
  get() {
    if (form.pagu === null || form.pagu === '') {
      return '';
    }
    return parseInt(form.pagu, 10).toLocaleString('id-ID');
  },
  set(newValue) {
    const cleanedValue = newValue.replace(/\D/g, '');
    form.pagu = cleanedValue;
  },
});
const updatePagu = (value) => {
  const cleanedValue = value.replace(/\D/g, '');
  form.pagu = cleanedValue;
};

</script>