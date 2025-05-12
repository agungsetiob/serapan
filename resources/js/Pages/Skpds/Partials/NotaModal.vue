<script setup>
import { watch, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: Boolean,
  isEdit: Boolean,
  notaData: Object,
  errors: Object,
  subKegiatan: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

const form = useForm({
  id: '',
  nomor_nota: '',
  perihal: '',
  anggaran: '',
  tanggal_pengajuan: '',
  sub_kegiatan_id: props.subKegiatan?.id || '',
  lampirans: []
});

const updateFormWithNotaData = () => {
  form.id = props.notaData?.id || '';
  form.nomor_nota = props.notaData?.nomor_nota || '';
  form.perihal = props.notaData?.perihal || '';
  form.anggaran = props.notaData?.anggaran || '';
  form.tanggal_pengajuan = props.notaData?.tanggal_pengajuan;
  form.sub_kegiatan_id = props.notaData?.sub_kegiatan_id || props.subKegiatan?.id || '';
};

watch(
  () => props.notaData,
  () => {
    updateFormWithNotaData();
  },
  { immediate: true }
);

watch(
  () => props.subKegiatan,
  (newSubKegiatan) => {
    if (newSubKegiatan && !props.isEdit) {
      form.sub_kegiatan_id = newSubKegiatan.id;
      form.anggaran = newSubKegiatan.pagu;
    }
  },
  { immediate: true }
);

const handleFileChange = (event) => {
  form.lampirans = event.target.files;
};

const closeModal = () => {
  form.reset();
  emit('close');
};

const handleSubmit = () => {
  if (props.isEdit) {
    form.put(route('nota-dinas.update', form.id), {
      onSuccess: () => {
        closeModal();
        router.reload({ only: ['notas'] });
      },
      preserveScroll: true,
    });
  } else {
    form.post(route('nota-dinas.store'), {
      onSuccess: () => {
        closeModal();
        router.reload({ only: ['notas'] });
      },
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Modal :show="show" @close="closeModal" maxWidth="5xl">
    <div class="bg-white p-4 sm:p-6 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">
        {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
      </h3>
      
      <!-- Sub Kegiatan Info -->
      <div v-if="subKegiatan && !isEdit" class="mb-4 p-3 bg-gray-50 rounded-md">
        <h4 class="text-sm font-medium text-gray-700 mb-1">Untuk Sub Kegiatan:</h4>
        <p class="font-medium">{{ subKegiatan.nama }}</p>
        <p class="text-sm text-gray-600">Pagu: Rp {{ subKegiatan.pagu?.toLocaleString('id-ID') }}</p>
      </div>
      
      <!-- Error Messages -->
      <div
        v-if="Object.keys(form.errors).length > 0"
        class="mb-4 p-4 bg-red-50 border-l-4 border-red-500"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-500"/>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Terdapat {{ Object.keys(form.errors).length }} kesalahan yang harus diperbaiki
            </h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc pl-5 space-y-1">
                <li v-for="(error, field) in form.errors" :key="field">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <form @submit.prevent="handleSubmit">
        <input type="hidden" v-model="form.id">
        <input type="hidden" v-model="form.sub_kegiatan_id">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="nomor_nota" class="block text-sm font-medium text-gray-700">Nomor Nota *</label>
            <input
              type="text"
              v-model="form.nomor_nota"
              required
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
              ]"
            >
            <p v-if="form.errors.nomor_nota" class="mt-1 text-sm text-red-600">
              {{ form.errors.nomor_nota }}
            </p>
          </div>

          <div>
            <label for="tanggal_pengajuan" class="block text-sm font-medium text-gray-700">Tanggal Pengajuan *</label>
            <input
              type="date"
              v-model="form.tanggal_pengajuan"
              required
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
              ]"
            >
            <p v-if="form.errors.tanggal_pengajuan" class="mt-1 text-sm text-red-600">
              {{ form.errors.tanggal_pengajuan }}
            </p>
          </div>
        </div>

        <div class="mb-4">
          <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal *</label>
          <input
            type="text"
            v-model="form.perihal"
            required
            :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.perihal ? 'border-red-500' : 'border-gray-300'
            ]"
          >
          <p v-if="form.errors.perihal" class="mt-1 text-sm text-red-600">
            {{ form.errors.perihal }}
          </p>
        </div>

        <div class="mb-4">
          <label for="anggaran" class="block text-sm font-medium text-gray-700">Anggaran (Rp)</label>
          <input
            type="number"
            v-model="form.anggaran"
            :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
            ]"
          >
          <p v-if="form.errors.anggaran" class="mt-1 text-sm text-red-600">
            {{ form.errors.anggaran }}
          </p>
        </div>

        <div class="mb-4">
          <label for="lampirans" class="block text-sm font-medium text-gray-700 mb-1">Lampiran (optional)</label>
          <input
            type="file"
            accept=".pdf,.doc,.docx,.xls,.xlsx"
            multiple
            @change="handleFileChange"
            :class="[
              'block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100',
              form.errors['lampirans.*'] ? 'border-red-500' : 'border-gray-300'
            ]"
          >
          <p class="mt-1 text-xs text-gray-500">Format: PDF (maks. 5MB per file)</p>
          <p v-if="form.errors['lampirans.*']" class="mt-1 text-sm text-red-600">
            {{ form.errors['lampirans.*'] }}
          </p>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="form.processing">
              <font-awesome-icon icon="spinner" spin class="mr-2" /> Menyimpan...
            </span>
            <span v-else>
              {{ isEdit ? 'Update' : 'Simpan' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>