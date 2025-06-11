<template>
  <Modal :show="show" @close="closeModal" maxWidth="5xl">
    <div class="sm:p-6">
      <h3 class="text-lg font-semibold mb-4">
        {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
      </h3>
      <div v-if="isChild && parentNota" class="mb-4 p-3 bg-green-100 rounded-md">
        <h4 class="text-sm font-medium text-gray-800 mb-1">Untuk Nota Dinas Induk:</h4>
        <p class="font-medium">No. Nota: {{ parentNota.nomor_nota }}</p>
        <p class="text-sm text-gray-600">Perihal: {{ parentNota.perihal }}</p>
        <p class="text-sm text-gray-600">Anggaran: {{ formatCurrency(parentNota.anggaran) }}</p>
      </div>

      <div
        v-if="Object.keys(form.errors).length > 0"
        class="mb-4 p-4 bg-red-50 border-l-4 border-red-500"
      >
        <div class="flex">
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
        <input type="hidden" v-model="form.skpd_id" name="skpd_id">
        <input type="hidden" v-model="form.id">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="nomor_nota" class="block text-sm font-medium text-gray-700">Nomor Nota<span class="text-red-600">*</span></label>
            <input
              type="text"
              v-model="form.nomor_nota"
              required
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
              ]"
            >
          </div>

          <div>
            <label for="tanggal_pengajuan" class="block text-sm font-medium text-gray-700">Tanggal Pengajuan<span class="text-red-600">*</span></label>
            <input
              type="date"
              v-model="form.tanggal_pengajuan"
              required
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
              ]"
            >
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="jenis" class="block text-sm font-medium text-gray-700">Jenis Nota<span class="text-red-600">*</span></label>
            <select
              v-model="form.jenis"
              required
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.jenis ? 'border-red-500' : 'border-gray-300'
              ]"
            >
              <option value="Pelaksanaan">Pelaksanaan</option>
              <option value="Perbup">Perbup</option>
              <option value="Lain-lain">Lain-lain</option>
              <option value="GU">GU</option>
              <option value="TU">TU</option>
              <option value="LS">LS</option>
            </select>
          </div>

          <div>
            <label for="anggaran" class="block text-sm font-medium text-gray-700">Anggaran (Rp)<span class="text-red-600">*</span></label>
            <input
              type="text"
              :value="formattedAnggaran"
              @input="updateAnggaran($event.target.value)"
              :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
              ]"
              placeholder="Contoh: 1.250.000"
            >
            <p v-if="form.errors.anggaran" class="mt-1 text-sm text-red-600">
              {{ form.errors.anggaran }}
            </p>
          </div>
        </div>

        <div class="mb-4">
          <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal<span class="text-red-600">*</span></label>
          <input
            type="text"
            v-model="form.perihal"
            required
            :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.perihal ? 'border-red-500' : 'border-gray-300'
            ]"
          >
        </div>

        <div class="mb-4">
          <label for="lampirans" class="block text-sm font-medium text-gray-700 mb-1">Lampiran (optional)</label>
          <input
            type="file"
            accept=".pdf"
            multiple
            @change="handleFileChange"
            class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
          >
          <p class="mt-1 text-xs text-gray-500">Format: PDF (maks. 3MB per file)</p>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100"
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

<script setup>
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
  show: Boolean,
  isEdit: Boolean,
  notaData: Object,
  isChild: Boolean,
  parentNota: Object,
  errors: Object,
  skpd: Object
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
  id: '',
  nomor_nota: '',
  perihal: '',
  anggaran: null,
  tanggal_pengajuan: '',
  jenis: 'Pelaksanaan',
  parent_ids: [],
  skpd_id: props.skpd?.id || null,
  lampirans: []
});

const formattedAnggaran = computed(() => {
  if (form.anggaran === null || form.anggaran === '') {
    return '';
  }
  const number = parseFloat(form.anggaran);
  if (isNaN(number)) {
    return '';
  }
  return number.toLocaleString('id-ID');
});

const formatCurrency = (value) => {
  if (value === null || value === undefined) return 'Rp0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(value);
};

const updateAnggaran = (value) => {
  let cleanedValue = value.replace(/Rp|\./g, '').replace(/,/g, '.');

  let parsedNumber = parseFloat(cleanedValue);

  if (cleanedValue === '' || isNaN(parsedNumber)) {
    form.anggaran = null;
  } else {
    form.anggaran = parsedNumber;
  }
};

const initForm = () => {
  if (props.isEdit && props.notaData) {
    form.id = props.notaData.id;
    form.nomor_nota = props.notaData.nomor_nota;
    form.perihal = props.notaData.perihal;
    form.anggaran = props.notaData.anggaran;
    form.tanggal_pengajuan = props.notaData.tanggal_pengajuan;
    form.jenis = props.notaData.jenis;
    form.parent_ids = props.notaData.dikaitkan_oleh ? props.notaData.dikaitkan_oleh.map(p => p.id) : [];
    form.skpd_id = props.notaData.skpd_id;
  } else if (props.isChild && props.parentNota) {
    form.reset();
    form.parent_ids = [props.parentNota.id];
    form.skpd_id = props.skpd?.id || null;
    form.anggaran = null;
  } else {
    form.reset();
    form.parent_ids = [];
    form.skpd_id = props.skpd?.id || null;
    form.anggaran = null;
  }
};

watch(
  () => props.show,
  (show) => {
    if (show) {
      initForm();
    }
  },
  { immediate: true }
);

const handleFileChange = (event) => {
  form.lampirans = event.target.files;
};

const closeModal = () => {
  form.reset();
  form.clearErrors();
  emit('close');
};

const handleSubmit = () => {
  const payload = {
    nomor_nota: form.nomor_nota,
    perihal: form.perihal,
    anggaran: form.anggaran,
    tanggal_pengajuan: form.tanggal_pengajuan,
    jenis: form.jenis,
    skpd_id: form.skpd_id,
    lampirans: form.lampirans,
    parent_ids: form.parent_ids
  };

  if (props.isEdit) {
    form.transform((data) => ({
      ...payload,
      _method: 'PUT'
    })).post(route('nota-skpd.update', form.id), {
      onSuccess: () => {
        closeModal();
        emit('success');
      },
      preserveScroll: true,
      forceFormData: true 
    });
  } else {
    form.transform(() => payload).post(route('nota-skpd.store'), {
      onSuccess: () => {
        closeModal();
        emit('success');
      },
      preserveScroll: true,
      forceFormData: true 
    });
  }
};
</script>