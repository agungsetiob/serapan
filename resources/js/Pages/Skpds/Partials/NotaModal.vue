<template>
  <Modal :show="show" @close="closeModal" maxWidth="5xl">
    <div class="sm:p-6">
      <h3 class="text-lg font-semibold mb-4">
        {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
      </h3>

      <div v-if="currentSubKegiatan" class="mb-4 p-3 bg-green-100 rounded-md">
        <h4 class="text-sm font-medium text-gray-700 mb-1">Untuk Sub Kegiatan:</h4>
        <p class="font-medium">{{ currentSubKegiatan.nama }}</p>
        <p class="text-sm text-gray-600">Pagu: Rp {{ formatRupiah(currentSubKegiatan.pagu) }}</p>
      </div>

      <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500">
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
        <input type="hidden" v-model="form.id">
        <input type="hidden" v-model="form.sub_kegiatan_id">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="nomor_nota" class="block text-sm font-medium text-gray-700">Nomor Nota<span
                class="text-red-600">*</span></label>
            <input type="text" v-model="form.nomor_nota" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
            ]">
          </div>

          <div>
            <label for="tanggal_pengajuan" class="block text-sm font-medium text-gray-700">Tanggal Pengajuan<span
                class="text-red-600">*</span></label>
            <input type="date" v-model="form.tanggal_pengajuan" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
            ]">
          </div>
        </div>

        <div class="mb-4">
          <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal<span
              class="text-red-600">*</span></label>
          <input type="text" v-model="form.perihal" :class="[
            'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
            form.errors.perihal ? 'border-red-500' : 'border-gray-300'
          ]">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div class="relative">
            <label for="anggaran" class="block text-sm font-medium text-gray-700">Anggaran (Rp)<span
                class="text-red-600">*</span></label>

            <div class="relative">
              <input type="text" :value="formattedAnggaran" @input="updateAnggaran($event.target.value)" :class="[
                'mt-1 block w-full border rounded-md px-3 py-2 pr-10 text-sm', // ← Tambahkan pr-10 agar tidak bentrok dengan tombol
                form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
              ]">
              <button v-if="form.anggaran !== null && form.anggaran !== ''" type="button" @click="form.anggaran = null"
                class="absolute right-2 top-[7px] text-gray-400 hover:text-gray-600 transition"
                aria-label="Clear amount">
                <font-awesome-icon icon="times" />
              </button>
            </div>
          </div>

          <div>
            <label for="jenis" class="block text-sm font-medium text-gray-700">Jenis Nota<span
                class="text-red-600">*</span></label>
            <select v-model="form.jenis" :class="[
              'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
              form.errors.jenis ? 'border-red-500' : 'border-gray-300'
            ]">
              <option value="" disabled>--Pilih jenis--</option>
              <option value="Pelaksanaan">Pelaksanaan</option>
              <option value="TU">TU</option>
              <option value="LS">LS</option>
            </select>
          </div>
        </div>
        <div class="mb-4">
          <label class="flex items-center space-x-1 cursor-pointer">
            <input type="checkbox" v-model="form.is_belanja_modal"
              class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 transition duration-150 ease-in-out" />
            <span class="text-sm text-gray-800 font-medium select-none">
              Tandai sebagai <span class="text-indigo-600 font-semibold">Belanja Modal</span>
            </span>
          </label>
        </div>
        <div>
          <label for="lampirans" class="block font-medium">Lampiran (opsional)</label>
          <input id="lampirans" type="file" accept=".pdf" multiple @change="handleFileChange"
            class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
          <p class="mt-1 text-xs text-gray-500">PDF (maks. 3MB per file)</p>

          <!-- Selected files display -->
          <div v-if="form.lampirans.length > 0" class="mt-2 space-y-2 mb-2">
            <div v-for="(file, index) in form.lampirans" :key="index"
              class="flex items-center justify-between p-2 bg-gray-100 rounded">
              <div class="flex items-center">
                <font-awesome-icon icon="file-pdf" class="text-red-500 mr-2" />
                <span class="text-sm truncate max-w-xs">{{ file.name }}</span>
                <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(file.size) }})</span>
              </div>
              <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700"
                aria-label="Remove file">
                <font-awesome-icon icon="times" />
              </button>
            </div>
          </div>
          <!-- Existing files in edit mode -->
          <div v-if="isEdit && existingLampiransDisplay.length" class="mt-2">
            <p class="text-sm font-medium mb-1">File yang sudah diunggah:</p>
            <div v-for="(file, index) in existingLampiransDisplay" :key="file.id"
              class="flex items-center justify-between p-2 bg-gray-100 rounded mb-2">
              <div class="flex items-center">
                <font-awesome-icon icon="file-pdf" class="text-red-500 mr-2" />
                <a href="#" target="_blank" class="text-sm text-blue-600">
                  {{ file.nama_file }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <SecondaryButton @click="closeModal" :disabled="form.processing">
            Batal
          </SecondaryButton>
          <PrimaryButton :disabled="form.processing">
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
import { watch, ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  show: Boolean,
  isEdit: Boolean,
  notaData: Object,
  subKegiatan: Object,
  errors: Object
});

const emit = defineEmits(['close']);

const form = useForm({
  id: '',
  nomor_nota: '',
  perihal: '',
  anggaran: '',
  tanggal_pengajuan: '',
  sub_kegiatan_id: '',
  jenis: '',
  is_belanja_modal: false,
  lampirans: []
});

const currentSubKegiatan = ref(null);

const formatRupiah = (number) => {
  if (number === null || number === undefined) {
    return '';
  }
  return new Intl.NumberFormat('id-ID').format(number);
};

const formattedAnggaran = computed(() => {
  if (form.anggaran === null || form.anggaran === '') {
    return '';
  }
  const number = parseInt(form.anggaran, 10);
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
    form.anggaran = props.notaData.anggaran;
    form.tanggal_pengajuan = props.notaData.tanggal_pengajuan;
    form.jenis = props.notaData.jenis;
    form.sub_kegiatan_id = props.notaData.sub_kegiatan_id;
    form.is_belanja_modal = Boolean(props.notaData.is_belanja_modal);
    currentSubKegiatan.value = props.subKegiatan;
  } else if (props.subKegiatan) {
    form.sub_kegiatan_id = props.subKegiatan.id;
    //form.anggaran = props.subKegiatan.pagu;
    currentSubKegiatan.value = props.subKegiatan;
  }
};

// Watch for changes in props
watch(
  () => props.show,
  (show) => {
    if (show) {
      initForm();
    }
  },
  { immediate: true }
);

watch(
  () => props.notaData,
  (newNotaData) => {
    if (newNotaData && props.isEdit) {
      form.anggaran = newNotaData.anggaran;
    }
  },
  { deep: true }
);

watch(
  () => props.subKegiatan,
  (newSubKegiatan) => {
    if (newSubKegiatan && !props.isEdit) {
      form.sub_kegiatan_id = newSubKegiatan.id;
      //form.anggaran = newSubKegiatan.pagu;
    }
  },
  { deep: true }
);

const handleFileChange = (event) => {
  //form.lampirans = event.target.files;
  const files = Array.from(event.target.files);
  const validFiles = files.filter(file => {
    if (file.size > 3 * 1024 * 1024) {
      form.setError('lampirans', 'Ukuran file melebihi 3MB per file.');
      return false;
    }
    return true;
  });

  if (validFiles.length > 0) {
    form.lampirans = [...form.lampirans, ...validFiles];
  }
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
    sub_kegiatan_id: form.sub_kegiatan_id,
    is_belanja_modal: form.is_belanja_modal,
    lampirans: form.lampirans
  };

  if (props.isEdit) {
    form.transform((data) => ({
      ...payload,
      _method: 'PUT'
    })).post(route('nota-dinas.update', form.id), {
      onSuccess: () => {
        closeModal();
        router.reload({ only: ['skpd'] });
      },
      preserveScroll: true,
    });
  } else {
    form.transform(() => payload).post(route('nota-dinas.store'), {
      onSuccess: () => {
        closeModal();
        router.reload({ only: ['skpd'] });
      },
      preserveScroll: true,
    });
  }
};

const removeFile = (index) => {
  form.lampirans.splice(index, 1);
};
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
const existingLampiransDisplay = computed(() => {
  if (!props.isEdit || !props.notaData || !props.notaData.lampirans) {
    return [];
  }
  return props.notaData.lampirans.map(lampiran => ({
    id: lampiran.id,
    nama_file: lampiran.nama_file,
    size: lampiran.size
  }));
});
</script>