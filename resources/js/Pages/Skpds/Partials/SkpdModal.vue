<script setup>
import { watch, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  show: Boolean,
  skpd: Object,
})

const emit = defineEmits(['close'])

const form = useForm({
  nama_skpd: '',
})

const isEdit = computed(() => !!props.skpd?.id)

watch(
  () => props.skpd,
  (val) => {
    if (val) {
      form.nama_skpd = val.nama_skpd || ''
    } else {
      form.reset()
    }
  },
  { immediate: true }
)

const submitForm = () => {
  if (isEdit.value && props.skpd?.id) {
    form.put(route('skpds.update', props.skpd.id), {
      onSuccess: () => emit('close'),
      preserveScroll: true,
    })
  } else {
    form.post(route('skpds.store'), {
      onSuccess: () => {
        form.reset();
        emit('close');
      },
      preserveScroll: true,
    })
  }
}

const closeModal = () => {
  form.reset();
  emit('close');
}
</script>

<template>
  <Modal :show="show" @close="closeModal" maxWidth="2xl">
    <div class="bg-white p-4 sm:p-6 rounded-lg w-11/12 sm:w-full">
      <h3 class="text-lg font-semibold mb-4">
        {{ isEdit ? 'Edit SKPD' : 'Tambah SKPD' }}
      </h3>
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Nama SKPD</label>
          <input v-model="form.nama_skpd" type="text" required
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2" />
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
    </div>
  </Modal>
</template>
