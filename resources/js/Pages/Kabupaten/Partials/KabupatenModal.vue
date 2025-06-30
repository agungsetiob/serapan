<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  show: Boolean,
  kabupaten: Object
});

const emit = defineEmits(['close']);

const form = useForm({
  nama: 'Tanah Bumbu',
  tahun_anggaran: '',
});

watch(() => props.show, (show) => {
  if (show) {
    form.nama = 'Tanah Bumbu';
    form.tahun_anggaran = props.kabupaten?.tahun_anggaran
      ? String(props.kabupaten.tahun_anggaran)
      : String(new Date().getFullYear());
    form.pagu = props.kabupaten?.pagu !== null && props.kabupaten?.pagu !== undefined
      ? String(props.kabupaten.pagu)
      : '';
    form.clearErrors();
  }
}, { immediate: true });

const closeModal = () => {
  form.clearErrors();
  emit('close');
};

function submit() {
  if (props.kabupaten) {
    form.put(route('kabupaten.update', props.kabupaten.id), {
      onSuccess: () => closeModal()
    });
  } else {
    form.post(route('kabupaten.store'), {
      onSuccess: () => closeModal()
    });
  }
}
</script>

<template>
  <Modal :show="show" @close="closeModal">
    <div class="p-6">
      <h2 class="text-lg font-semibold mb-4">
        {{ kabupaten ? 'Edit Pagu Kabupaten' : 'Tambah Pagu Kabupaten' }}
      </h2>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <InputLabel value="Nama Anggaran" />
          <TextInput v-model="form.nama" class="w-full" />
          <InputError :message="form.errors.nama" />
        </div>

        <div class="mb-4">
          <InputLabel value="Tahun Anggaran" />
          <TextInput v-model="form.tahun_anggaran" type="number" class="w-full" readonly />
          <InputError :message="form.errors.tahun_anggaran" />
        </div>

        <div class="flex justify-end gap-2">
          <SecondaryButton @click="closeModal" :disabled="form.processing">
            Batal
          </SecondaryButton>
          <PrimaryButton :disabled="form.processing">
            <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
            {{ form.processing
              ? (kabupaten ? 'Mengupdate...' : 'Menyimpan...')
              : (kabupaten ? 'Update' : 'Simpan')
            }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>
</template>