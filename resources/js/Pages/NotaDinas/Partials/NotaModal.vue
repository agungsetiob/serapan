<template>
  <Modal :show="show" @close="closeModal" maxWidth="5xl">
    <div class="sm:p-6">
      <h3 class="text-lg font-semibold mb-4">
        {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
      </h3>
      <div v-if="isChild && parentNota" class="mb-4 p-3 bg-green-100 rounded-md">
        <h4 class="text-sm font-medium text-gray-800 mb-1">Untuk Nota Dinas:</h4>
        <p class="font-medium">No. Nota: {{ parentNota.nomor_nota }}</p>
        <p class="text-sm text-gray-600">Perihal: {{ parentNota.perihal }}</p>
        <p class="text-sm text-gray-600">Anggaran: Rp. {{ formatNumber(parentNota.anggaran) }}</p>
        <p class="text-sm text-red-700">Sisa Anggaran: Rp. {{ formatNumber(parentNota.sisa_anggaran) }}</p>
      </div>

      <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500">
        <div class="flex">
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Terdapat {{ Object.keys(form.errors).length }} kesalahan
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
            <label for="nomor_nota" class="block font-medium">Nomor Nota<span class="text-red-600">*</span></label>
            <input type="text" v-model="form.nomor_nota" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
            ]">
          </div>

          <div>
            <label for="tanggal_pengajuan" class="block font-medium">Tanggal Pengajuan<span
                class="text-red-600">*</span></label>
            <input type="date" v-model="form.tanggal_pengajuan" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
            ]">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="jenis" class="block font-medium">Jenis Nota<span class="text-red-600">*</span></label>
            <select v-if="form.parent_ids.length === 0" v-model="form.jenis" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.jenis ? 'border-red-500' : 'border-gray-300'
            ]">
              <option value="" disabled>--Pilih jenis--</option>
              <option value="Perda">Perda</option>
              <option value="Perbup">Perbup</option>
              <option value="SK">SK</option>
              <option value="Rekomendasi">Rekomendasi</option>
              <option value="Surat">Surat</option>
              <option value="Telaah">Telaah Staf</option>
              <option value="Edaran">Surat Edaran</option>
              <option value="Instruksi">Instruksi</option>
            </select>
            <select v-else v-model="form.jenis" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.jenis ? 'border-red-500' : 'border-gray-300'
            ]">
              <option value="" disabled>--Pilih jenis--</option>
              <option value="GU">GU</option>
              <option value="TU">TU</option>
              <option value="LS">LS</option>
            </select>
          </div>

          <div>
            <label for="anggaran" class="block font-medium">Anggaran (Rp)</label>
            <input type="text" :value="formattedAnggaran" @input="updateAnggaran($event.target.value)" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm bg-gray-100',
              form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
            ]" readonly>
          </div>
        </div>

        <div class="mb-4">
          <label for="perihal" class="block font-medium">Perihal<span class="text-red-600">*</span></label>
          <input type="text" v-model="form.perihal" :class="[
            'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
            form.errors.perihal ? 'border-red-500' : 'border-gray-300'
          ]">
        </div>

        <div class="mb-4">
          <label for="lampirans" class="block font-medium">Lampiran (opsional)</label>
          <input type="file" accept=".pdf" multiple @change="handleFileChange"
            class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
          <p class="mt-1 text-xs text-gray-500">PDF (maks. 3MB per file)</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <SecondaryButton @click="closeModal" :disabled="form.processing">
            Batal
          </SecondaryButton>
          <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            <font-awesome-icon v-if="form.processing" icon="spinner" spin class="mr-2" />
            {{ form.processing
              ? (isEdit ? 'Mengupdate...' : 'Menyimpan...')
              : (isEdit ? 'Update' : 'Simpan')
            }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { formatNumber } from '@/Utils/formatters';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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
  jenis: '',
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
const updateAnggaran = (value) => {
  const cleanedValue = value.replace(/\D/g, '');
  form.anggaran = cleanedValue;
};

const initForm = () => {
  if (props.isEdit && props.notaData) {
    form.id = props.notaData.id;
    form.nomor_nota = props.notaData.nomor_nota;
    form.perihal = props.notaData.perihal;
    form.anggaran = props.notaData.anggaran ?? 0;
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