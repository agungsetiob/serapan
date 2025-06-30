<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  show: Boolean,
  isEditing: Boolean,
  program: Object,
  skpdId: Number,
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
  nama: '',
  tahun_anggaran: new Date().getFullYear(),
});

// Watch props.program untuk mengisi form saat mode edit
watch(() => props.program, (newProgram) => {
  if (props.isEditing && newProgram) {
    form.nama = newProgram.nama;
    form.tahun_anggaran = newProgram.tahun_anggaran;
  } else {
    form.reset();
    form.tahun_anggaran = new Date().getFullYear();
  }
}, { immediate: true });

function submitProgram() {
  if (props.isEditing) {
    form.put(route('programs.update', props.program.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('success');
        closeModal();
      },
    });
  } else {
    if (!props.skpdId) {
      console.error('SKPD ID tidak tersedia untuk membuat program baru.');
      return;
    }
    form.post(route('programs.store', { skpd: props.skpdId }), {
      preserveScroll: true,
      onSuccess: () => {
        emit('success');
        closeModal();
      },
    });
  }
}

const closeModal = () => {
  form.reset();
  form.clearErrors();
  emit('close');
};
</script>

<template>
  <Modal :show="show" @close="closeModal">
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900 mb-4">
        {{ isEditing ? 'Edit Program' : 'Tambah Program Baru' }}
      </h2>

      <form @submit.prevent="submitProgram">
        <div class="mb-4">
          <InputLabel for="nama-program" value="Nama Program" />
          <TextInput
            id="nama-program"
            v-model="form.nama"
            type="text"
            class="mt-1 block w-full"
            placeholder="Masukkan nama program"
          />
          <InputError :message="form.errors.nama" class="mt-2" />
        </div>

        <div class="mb-6">
          <InputLabel for="tahun-anggaran-program" value="Tahun Anggaran" />
          <TextInput
            id="tahun-anggaran-program"
            v-model="form.tahun_anggaran"
            type="number"
            class="mt-1 block w-full"
            readonly
          />
          <InputError :message="form.errors.tahun_anggaran" class="mt-2" />
        </div>

        <div class="flex justify-end gap-2">
          <SecondaryButton @click="closeModal" :disabled="form.processing">
            Batal
          </SecondaryButton>
          <PrimaryButton :disabled="form.processing">
            <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
            {{ form.processing
              ? (isEditing ? 'Mengupdate...' : 'Menyimpan...')
              : (isEditing ? 'Update' : 'Simpan')
            }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>
</template>

