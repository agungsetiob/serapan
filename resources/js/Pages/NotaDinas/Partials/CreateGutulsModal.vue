<template>
    <Modal :show="show" @close="closeModal" max-width="5xl">
        <div class="sm:p-6 scrollbar-hide">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
            </h2>
            <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500">
                <p class="font-medium text-blue-800 text-bold">{{ skpd.nama_skpd }}</p>
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

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <input type="hidden" v-model="form.skpd_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nomor_nota" class="block font-medium">Nomor Nota<span
                                class="text-red-600">*</span></label>
                        <input id="nomor_nota" type="text" v-model="form.nomor_nota"
                            :aria-invalid="!!form.errors.nomor_nota"
                            :aria-describedby="form.errors.nomor_nota ? 'nomor_nota-error' : undefined" :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
                            ]">
                        <InputError :id="'nomor_nota-error'" :message="form.errors.nomor_nota" />
                    </div>

                    <div>
                        <label for="tanggal_pengajuan" class="block font-medium">Tanggal Pengajuan<span
                                class="text-red-600">*</span></label>
                        <input id="tanggal_pengajuan" type="date" v-model="form.tanggal_pengajuan"
                            :aria-invalid="!!form.errors.tanggal_pengajuan"
                            :aria-describedby="form.errors.tanggal_pengajuan ? 'tanggal_pengajuan-error' : undefined"
                            :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
                            ]">
                        <InputError :id="'tanggal_pengajuan-error'" :message="form.errors.tanggal_pengajuan" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis" class="block font-medium">Jenis Nota<span
                                class="text-red-600">*</span></label>
                        <select id="jenis" v-model="form.jenis" :aria-invalid="!!form.errors.jenis"
                            :aria-describedby="form.errors.jenis ? 'jenis-error' : undefined" :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.jenis ? 'border-red-500' : 'border-gray-300'
                            ]">
                            <option disabled value="">-- Pilih Jenis Nota --</option>
                            <option value="GU">GU</option>
                            <option value="TU">TU</option>
                            <option value="LS">LS</option>
                        </select>
                        <InputError :id="'jenis-error'" :message="form.errors.jenis" />
                    </div>

                    <div>
                        <label for="anggaran" class="block font-medium">Anggaran (Rp)<span
                                class="text-red-600">*</span></label>
                        <div class="relative mt-1">
                            <input id="anggaran" type="text" :value="formattedAnggaran"
                                @input="updateAnggaran($event.target.value)" :aria-invalid="!!form.errors.anggaran"
                                :aria-describedby="form.errors.anggaran ? 'anggaran-error' : undefined" :class="[
                                    'block w-full border rounded-md px-3 py-2 text-sm',
                                    form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
                                ]" />
                            <button v-if="form.anggaran !== null && form.anggaran !== ''" type="button"
                                @click="form.anggaran = null"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                aria-label="Clear amount">
                                <font-awesome-icon icon="times" />
                            </button>
                        </div>
                        <InputError :id="'anggaran-error'" :message="form.errors.anggaran" />
                    </div>
                </div>

                <div>
                    <label for="perihal" class="block font-medium">Perihal<span class="text-red-600">*</span></label>
                    <input id="perihal" v-model="form.perihal" type="text" :aria-invalid="!!form.errors.perihal"
                        :aria-describedby="form.errors.perihal ? 'perihal-error' : undefined" :class="[
                            'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                            form.errors.perihal ? 'border-red-500' : 'border-gray-300'
                        ]" />
                    <InputError :id="'perihal-error'" :message="form.errors.perihal" />
                </div>
                <div>
                    <input type="checkbox" v-model="form.is_belanja_modal"
                        class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 transition duration-150 ease-in-out" />
                    <span class="text-sm text-gray-800 font-medium select-none">
                        Tandai sebagai <span class="text-indigo-600 font-semibold">Belanja Modal</span>
                    </span>
                </div>

                <div>
                    <label for="lampirans" class="block font-medium">Lampiran (opsional)</label>
                    <input id="lampirans" type="file" accept=".pdf" multiple @change="handleFileChange"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">PDF (maks. 3MB per file)</p>

                    <!-- Selected files display -->
                    <div v-if="form.lampirans.length > 0" class="mt-2 space-y-2">
                        <div v-for="(file, index) in form.lampirans" :key="index"
                            class="flex items-center justify-between p-2 bg-gray-50 rounded">
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
                            class="flex items-center justify-between p-2 bg-gray-50 rounded mb-2">
                            <div class="flex items-center">
                                <font-awesome-icon icon="file-pdf" class="text-red-500 mr-2" />
                                <a href="#" target="_blank" class="text-sm text-blue-600 hover:underline">
                                    {{ file.nama_file }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Kegiatan -->
                <div class="mb-4">
                    <label for="kegiatan" class="block font-medium">Pilih Kegiatan</label>
                    <select id="kegiatan" v-model="selectedKegiatanId"
                        class="mt-1 block w-full border rounded-md px-3 py-2 text-sm border-gray-300">
                        <option value="">-- Pilih Kegiatan --</option>
                        <option v-for="keg in props.kegiatanOptions" :key="keg.id" :value="keg.id">
                            {{ keg.nama }}</option>
                    </select>
                </div>

                <!-- Sub Kegiatan -->
                <div v-if="selectedKegiatanId" class="mb-4">
                    <label for="sub_kegiatan" class="block font-medium">Pilih Sub Kegiatan</label>
                    <select id="sub_kegiatan" v-model="selectedSubKegiatanId"
                        class="mt-1 block w-full border rounded-md px-3 py-2 text-sm border-gray-300">
                        <option value="">-- Pilih Sub Kegiatan --</option>
                        <option v-for="sub in filteredSubKegiatan" :key="sub.id" :value="sub.id">{{
                            sub.nama }}</option>
                    </select>
                </div>

                <div>
                    <label v-if="parentNotes.length > 0" class="block font-medium">
                        {{ isEdit ? 'Nota Induk' : 'Pilih Nota Induk (Bisa lebih dari satu)' }}
                    </label>
                    <div v-if="parentNotesLoading" class="flex justify-center p-4">
                        <font-awesome-icon icon="spinner" spin class="text-blue-500 text-xl" />
                    </div>
                    <div v-else class="grid grid-cols-1 gap-4">
                        <!-- Search input for parent -->
                        <div class="mb-3">
                            <label for="parent_notes_search" class="sr-only">Cari Nota Induk</label>
                            <input v-if="parentNotes.length > 0" id="parent_notes_search" type="text"
                                v-model="parentSearchTerm" placeholder="Cari nomor nota atau perihal..."
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>

                        <!-- Filtered parent list -->
                        <div v-if="filteredParentNotes.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="nota in filteredParentNotes" :key="nota.id"
                                :aria-disabled="nota.sisa_anggaran <= 0" :class="['border rounded-md p-2 flex justify-between items-center transition',
                                    form.errors.parent_ids ? 'border-red-500' : 'border-gray-300',
                                    {
                                        'opacity-50 cursor-not-allowed': nota.sisa_anggaran <= 0,
                                        'cursor-pointer': nota.sisa_anggaran > 0,
                                        'bg-green-100 border-green-500': form.parent_ids.includes(nota.id),
                                        'hover:bg-gray-50': nota.sisa_anggaran > 0
                                    }]" @click="toggleParentNota(nota.id)">
                                <div>
                                    <p class="font-semibold">
                                        {{ nota.nomor_nota }} - {{ nota.perihal }}
                                        <span
                                            :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-2', badgeClasses(nota.jenis)]">
                                            {{ nota.jenis }}
                                        </span>
                                    </p>
                                    <p :class="['text-sm', nota.sisa_anggaran <= 0 ? 'text-red-500' : 'text-gray-500']">
                                        Sisa: Rp. {{ nota.sisa_anggaran.toLocaleString('id-ID') }}
                                        <span v-if="nota.is_belanja_modal"
                                            class="inline-block bg-blue-100 text-xs px-2 py-0.5 rounded-full">
                                            Belanja Modal
                                        </span>
                                    </p>
                                </div>
                                <font-awesome-icon v-if="form.parent_ids.includes(nota.id)" icon="check-circle"
                                    class="text-green-600 text-lg" />
                            </div>
                        </div>
                        <div v-else-if="parentSearchTerm.trim() !== ''" class="p-3 bg-red-50 rounded-md">
                            <p class="text-red-800 text-center">Tidak ada nota induk yang sesuai dengan pencarian Anda.
                            </p>
                        </div>
                    </div>
                    <div v-if="parentNotes.length === 0 && !parentNotesLoading" class="p-3 bg-red-50 rounded-md mt-4">
                        <p class="text-red-800 text-center">Belum ada nota dinas induk yang tersedia.
                        </p>
                    </div>
                    <InputError :message="form.errors.parent_ids" />
                </div>

                <div class="flex justify-end space-x-3">
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
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

const parentNotes = ref([]);
const parentNotesLoading = ref(false);

const props = defineProps({
    show: Boolean,
    isEdit: Boolean,
    notaData: Object,
    //parentNotes: Array,
    skpd: Object,
    //parentNotesLoading: Boolean,
    kegiatanOptions: Array,
    subKegiatanOptions: Array
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
    is_belanja_modal: false,
    lampirans: [],
    deleted_files: []
});

const existingLampiransDisplay = ref([]);
const parentSearchTerm = ref('');

const formattedAnggaran = computed(() => {
    if (form.anggaran === null || form.anggaran === '') return '';
    return parseFloat(form.anggaran).toLocaleString('id-ID');
});

const updateAnggaran = (value) => {
    const digitsOnly = value.replace(/\D/g, '');
    form.anggaran = digitsOnly ? parseInt(digitsOnly) : null;
};

const handleFileChange = (event) => {
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

const handleSubmit = () => {
    const payload = {
        nomor_nota: form.nomor_nota,
        perihal: form.perihal,
        anggaran: form.anggaran,
        tanggal_pengajuan: form.tanggal_pengajuan,
        jenis: form.jenis,
        skpd_id: form.skpd_id,
        lampirans: form.lampirans,
        is_belanja_modal: form.is_belanja_modal,
        parent_ids: form.parent_ids
    };

    if (props.isEdit) {
        form.transform((data) => ({
            ...payload,
            _method: 'PUT'
        })).post(route('update-gutuls', form.id), {
            onSuccess: () => {
                closeModal();
                emit('success');
            },
            preserveScroll: true,
            forceFormData: true
        });
    } else {
        form.transform(() => payload).post(route('store-gutuls'), {
            onSuccess: () => {
                closeModal();
                emit('success');
            },
            preserveScroll: true,
            forceFormData: true
        });
    }
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    form.deleted_files = [];
    form.lampirans = [];
    existingLampiransDisplay.value = [];
    parentSearchTerm.value = '';
    emit('close');
};

const toggleParentNota = (notaId) => {
    const selectedNota = parentNotes.value.find(n => n.id === notaId);

    if (selectedNota && selectedNota.sisa_anggaran > 0) {
        const index = form.parent_ids.indexOf(notaId);
        if (index === -1) {
            form.parent_ids.push(notaId);
        } else {
            form.parent_ids.splice(index, 1);
        }
    }
};

const badgeClasses = (jenis) => {
    switch (jenis) {
        case 'GU': return 'bg-yellow-100 text-yellow-800';
        case 'TU': return 'bg-indigo-100 text-indigo-800';
        case 'LS': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-200 text-gray-800';
    }
};
const initializeForm = () => {
    form.reset();
    form.clearErrors();
    form.deleted_files = [];
    form.lampirans = [];
    existingLampiransDisplay.value = [];
    parentSearchTerm.value = '';

    if (props.isEdit && props.notaData) {
        form.id = props.notaData.id;
        form.nomor_nota = props.notaData.nomor_nota;
        form.perihal = props.notaData.perihal;
        form.anggaran = props.notaData.anggaran;
        form.tanggal_pengajuan = props.notaData.tanggal_pengajuan;
        form.jenis = props.notaData.jenis;
        form.skpd_id = props.notaData.skpd_id;
        form.is_belanja_modal = Boolean(props.notaData.is_belanja_modal);
        form.parent_ids = props.notaData.parents?.map(parent => parent.id) || [];
        existingLampiransDisplay.value = props.notaData.lampirans?.map(l => ({ ...l })) || [];
    } else {
        form.skpd_id = props.skpd?.id || null;
    }
};
watch(
    [() => props.show, () => props.notaData],
    ([show, notaData]) => {
        if (show) {
            initializeForm();
        }
    },
    { immediate: true, deep: true }
);

// watch(
//     () => props.show,
//     (show) => {
//         if (show) {
//             form.reset();
//             form.clearErrors();
//             form.deleted_files = [];
//             form.lampirans = [];
//             existingLampiransDisplay.value = [];
//             parentSearchTerm.value = '';

//             if (props.isEdit && props.notaData) {
//                 form.id = props.notaData.id;
//                 form.nomor_nota = props.notaData.nomor_nota;
//                 form.perihal = props.notaData.perihal;
//                 form.anggaran = props.notaData.anggaran;
//                 form.tanggal_pengajuan = props.notaData.tanggal_pengajuan;
//                 form.jenis = props.notaData.jenis;
//                 form.skpd_id = props.notaData.skpd_id;
//                 form.is_belanja_modal = Boolean(props.notaData.is_belanja_modal);

//                 if (props.notaData.parents) {
//                     form.parent_ids = props.notaData.parents.map(parent => parent.id);
//                 }

//                 if (props.notaData.lampirans) {
//                     existingLampiransDisplay.value = props.notaData.lampirans.map(l => ({ ...l }));
//                 }

//             } else {
//                 form.skpd_id = props.skpd?.id || null;
//             }
//         }
//     },
//     { immediate: true }
// );

// watch(
//     () => props.notaData,
//     (newNotaData) => {
//         if (props.show && props.isEdit && newNotaData) {
//             form.id = newNotaData.id;
//             form.nomor_nota = newNotaData.nomor_nota;
//             form.perihal = newNotaData.perihal;
//             form.anggaran = newNotaData.anggaran;
//             form.tanggal_pengajuan = newNotaData.tanggal_pengajuan;
//             form.jenis = newNotaData.jenis;
//             form.skpd_id = newNotaData.skpd_id;
//             form.is_belanja_modal = Boolean(newNotaData.is_belanja_modal);

//             if (newNotaData.parents) {
//                 form.parent_ids = newNotaData.parents.map(parent => parent.id);
//             }
//             if (newNotaData.lampirans) {
//                 existingLampiransDisplay.value = newNotaData.lampirans.map(l => ({ ...l }));
//             }
//         }
//     },
//     { deep: true }
// );

const selectedKegiatanId = ref('');
const selectedSubKegiatanId = ref('');

const filteredSubKegiatan = computed(() =>
    props.subKegiatanOptions?.filter(sk => sk.kegiatan_id === selectedKegiatanId.value) || []
);

const filteredParentNotes = computed(() => {
    let result = parentNotes.value;

    if (selectedSubKegiatanId.value) {
        result = result.filter(nota => nota.sub_kegiatan_id === selectedSubKegiatanId.value);
    }

    if (parentSearchTerm.value) {
        const searchTermLower = parentSearchTerm.value.toLowerCase();
        result = result.filter(nota =>
            nota.nomor_nota.toLowerCase().includes(searchTermLower) ||
            nota.perihal.toLowerCase().includes(searchTermLower)
        );
    }

    return result;
});

watch(selectedSubKegiatanId, async (id) => {
    if (id) {
        parentNotesLoading.value = true;
        try {
            const { data } = await axios.get(route('nota-induk.index', props.skpd.id), {
                params: { tahun: props.tahun, sub_kegiatan_id: id }
            });
            parentNotes.value = data;
        } catch (err) {
            console.error('Gagal mengambil parent notes:', err);
        } finally {
            parentNotesLoading.value = false;
        }
    } else {
        parentNotes.value = [];
    }
});
</script>
