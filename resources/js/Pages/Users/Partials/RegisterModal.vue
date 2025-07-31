<script setup>
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close'])

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const submitForm = () => {
  form.post(route('register'), {
    onSuccess: () => {
      form.reset()
      emit('close')
    },
    preserveScroll: true
  })
}

const closeModal = () => {
  form.reset()
  emit('close')
}
</script>

<template>
  <Modal :show="show" @close="closeModal" maxWidth="2xl">
    <div class="bg-white p-4 sm:p-6 rounded-lg w-11/12 sm:w-full">
      <h3 class="text-lg font-semibold mb-4">Tambah Pengguna</h3>
      <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <InputLabel for="name" value="Name" />
            <TextInput id="name" type="text" v-model="form.name" class="mt-1 block w-full" autocomplete="name" />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div>
            <InputLabel for="email" value="Email" />
            <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" autocomplete="username" />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel for="password" value="Password" />
            <TextInput id="password" type="password" v-model="form.password" class="mt-1 block w-full"
              autocomplete="new-password" />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div>
            <InputLabel for="password_confirmation" value="Confirm Password" />
            <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation"
              class="mt-1 block w-full" autocomplete="new-password" />
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <SecondaryButton @click="closeModal" :disabled="form.processing">Batal</SecondaryButton>
            <PrimaryButton :disabled="form.processing">
              <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
              {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </PrimaryButton>
          </div>
        </div>
      </form>
    </div>
  </Modal>
</template>
