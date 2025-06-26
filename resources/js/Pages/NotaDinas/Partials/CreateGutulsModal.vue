<template>
    <Modal :show="show" @close="closeModal" max-width="5xl">
        <div class="sm:p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
            </h2>
            <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500">
                <p class="font-medium text-blue-800 text-bold">{{ skpd.nama_skpd }}</p>
            </div>

            <div
                v-if="Object.keys(form.errors).length > 0"
                class="mb-4 p-4 bg-red-50 border-l-4 border-red-500"
            >
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
                        <label for="nomor_nota" class="block font-medium">Nomor Nota<span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            v-model="form.nomor_nota"
                            :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.nomor_nota ? 'border-red-500' : 'border-gray-300'
                            ]"
                        >
                        <InputError :message="form.errors.nomor_nota" />
                    </div>

                    <div>
                        <label for="tanggal_pengajuan" class="block font-medium">Tanggal Pengajuan<span class="text-red-600">*</span></label>
                        <input
                            type="date"
                            v-model="form.tanggal_pengajuan"
                            :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.tanggal_pengajuan ? 'border-red-500' : 'border-gray-300'
                            ]"
                        >
                        <InputError :message="form.errors.tanggal_pengajuan" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis" class="block font-medium">Jenis Nota<span class="text-red-600">*</span></label>
                        <select v-model="form.jenis" :class="[
                            'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                            form.errors.jenis ? 'border-red-500' : 'border-gray-300'
                            ]"
                        >
                            <option disabled value="">-- Pilih Jenis Nota --</option>
                            <option value="GU">GU</option>
                            <option value="TU">TU</option>
                            <option value="LS">LS</option>
                        </select>
                        <InputError :message="form.errors.jenis" />
                    </div>

                    <div>
                        <label for="anggaran" class="block font-medium">Anggaran (Rp)<span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            :value="formattedAnggaran"
                            @input="updateAnggaran($event.target.value)"
                            :class="[
                                'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                                form.errors.anggaran ? 'border-red-500' : 'border-gray-300'
                            ]"
                        />
                        <InputError :message="form.errors.anggaran" />
                    </div>
                </div>

                <div>
                    <label for="perihal" class="block font-medium">Perihal<span class="text-red-600">*</span></label>
                    <input v-model="form.perihal" type="text" 
                        :class="[
                        'mt-1 block w-full border rounded-md px-3 py-2 text-sm',
                        form.errors.perihal ? 'border-red-500' : 'border-gray-300'
                        ]"
                    />
                    <InputError :message="form.errors.perihal" />
                </div>
                <div>
                    <label for="lampirans" class="block font-medium">Lampiran (opsional)</label>
                    <input
                        type="file"
                        accept=".pdf"
                        multiple
                        @change="handleFileChange"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    >
                    <p class="mt-1 text-xs text-gray-500">PDF (maks. 3MB per file)</p>
                </div>

                <div>
                    <label v-if="parentNotes.length > 0" class="block font-medium">
                        {{ isEdit ? 'Nota Induk' : 'Pilih Nota Induk (Bisa lebih dari satu)' }}
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="nota in parentNotes"
                            :key="nota.id"
                            :class="['border rounded-md p-2 flex justify-between items-center transition',
                            form.errors.parent_ids ? 'border-red-500' : 'border-gray-300',
                            { 
                                'opacity-50 cursor-not-allowed': nota.sisa_anggaran <= 0,
                                'cursor-pointer': !isEdit && nota.sisa_anggaran > 0,
                                'bg-green-100 border-green-500': form.parent_ids.includes(nota.id) 
                            }]"
                            @click="!isEdit && nota.sisa_anggaran > 0 && toggleParentNota(nota.id)"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{ nota.nomor_nota }} - {{ nota.perihal }}
                                    <span 
                                        :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-2', badgeClasses(nota.jenis)]"
                                    >
                                        {{ nota.jenis }}
                                    </span>
                                </p>
                                <p :class="['text-sm text-gray-500', { 'text-red-500': nota.sisa_anggaran <= 0 }]">
                                    Sisa: Rp. {{ nota.sisa_anggaran.toLocaleString('id-ID') }}
                                </p>
                            </div>
                            <font-awesome-icon 
                                v-if="form.parent_ids.includes(nota.id)" 
                                icon="check-circle" 
                                class="text-green-600 text-lg" 
                            />
                        </div>
                    </div>
                    <div v-if="parentNotes.length === 0" class="p-3 bg-red-50 rounded-md">
                        <p class="text-red-800 text-center">Belum ada nota dinas</p>
                    </div>
                    <InputError :message="form.errors.parent_ids" />
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <SecondaryButton @click="closeModal">Batal</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        <span v-if="form.processing"><font-awesome-icon icon="spinner" spin class="mr-2" /> Menyimpan...</span>
                        <span v-else>{{ isEdit ? 'Update' : 'Simpan' }}</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    isEdit: Boolean,
    notaData: Object,
    parentNotes: Array,
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
});

const formattedAnggaran = computed(() => {
    if (!form.anggaran) return '';
    return parseFloat(form.anggaran).toLocaleString('id-ID');
});

const updateAnggaran = (value) => {
    form.anggaran = value.replace(/\D/g, '');
};

const handleSubmit = () => {
    const payload = { ...form };
    if (props.isEdit) {
        payload._method = 'PUT';
        form.put(route('update-gutuls', form.id), {
            onSuccess: () => {
                closeModal();
                emit('success');
            }
        });
    } else {
        form.post(route('store-gutuls'), {
            onSuccess: () => {
                closeModal();
                emit('success');
            }
        });
    }
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

watch(
    () => props.show,
    (show) => {
        if (show && props.isEdit && props.notaData) {
            Object.assign(form, props.notaData);
            // Set parent_ids from the notaData if it exists
            if (props.notaData.parents) {
                form.parent_ids = props.notaData.parents.map(parent => parent.id);
            }
        }
    },
    { immediate: true }
);

const toggleParentNota = (notaId) => {
    const index = form.parent_ids.indexOf(notaId);
    if (index === -1) {
        form.parent_ids.push(notaId);
    } else {
        form.parent_ids.splice(index, 1);
    }
};
const badgeClasses = (jenis) => {
  switch(jenis) {
    case 'Pelaksanaan': return `bg-yellow-100 text-yellow-800`;
    case 'TU': return `bg-indigo-100 text-indigo-800`;
    case 'LS': return `bg-red-100 text-red-800`;
    default: return `bg-gray-100 text-gray-800`;
  }
}
</script>
