<template>
    <Modal :show="show" @close="closeModal" max-width="5xl">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                {{ isEdit ? 'Edit Nota Dinas' : 'Buat Nota Dinas' }}
            </h2>
            <div v-if="isChild && parentNota" class="mb-4 p-3 bg-green-100 rounded-md">
                <h4 class="text-sm font-medium text-gray-800 mb-1">Untuk SKPD:</h4>
                <p class="font-medium text-bold">{{ skpd.nama_skpd }}</p>
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

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <input type="hidden" v-model="form.skpd_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold">Nomor Nota</label>
                        <input v-model="form.nomor_nota" type="text" class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500" />
                        <InputError :message="form.errors.nomor_nota" />
                    </div>

                    <div>
                        <label class="block font-semibold">Tanggal Pengajuan</label>
                        <input v-model="form.tanggal_pengajuan" type="date" class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500" />
                        <InputError :message="form.errors.tanggal_pengajuan" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold">Jenis Nota</label>
                        <select v-model="form.jenis" class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500">
                            <option disabled value="">-- Pilih Jenis Nota --</option>
                            <option value="Pelaksanaan">Pelaksanaan</option>
                            <option value="Perbup">Perbup</option>
                            <option value="Lain-lain">Lain-lain</option>
                            <option v-if="form.parent_ids.length > 0" value="GU">GU</option>
                            <option v-if="form.parent_ids.length > 0" value="TU">TU</option>
                            <option v-if="form.parent_ids.length > 0" value="LS">LS</option>
                        </select>
                        <InputError :message="form.errors.jenis" />
                    </div>

                    <div>
                        <label class="block font-semibold">Anggaran (Rp)</label>
                        <input
                            type="text"
                            :value="formattedAnggaran"
                            @input="updateAnggaran($event.target.value)"
                            class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <InputError :message="form.errors.anggaran" />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold">Pilih Nota Induk (Pelaksanaan/Perbup/Lain-lain)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="nota in parentNotes"
                            :key="nota.id"
                            :class="['border border-gray-300 rounded-lg p-4 flex justify-between items-center cursor-pointer transition hover:bg-gray-100', { 'opacity-50 cursor-not-allowed': nota.sisa_anggaran <= 0 }, { 'bg-indigo-100 border-indigo-500': form.parent_ids.length > 0 && form.parent_ids[0] === nota.id }]"
                            @click="selectParentNota(nota.id)"
                        >
                            <div>
                                <p class="font-semibold">{{ nota.nomor_nota }} - {{ nota.perihal }}</p>
                                <p class="text-sm text-gray-500">Sisa: Rp. {{ nota.sisa_anggaran.toLocaleString('id-ID') }}</p>
                            </div>
                            <font-awesome-icon v-if="form.parent_ids.length > 0 && form.parent_ids[0] === nota.id" icon="check-circle" class="text-green-600 text-lg" />
                        </div>
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

const selectParentNota = (notaId) => {
    form.parent_ids = [notaId];
};

const handleSubmit = () => {
    const payload = { ...form };
    if (props.isEdit) {
        payload._method = 'PUT';
        form.post(route('nota-skpd.update', form.id), {
            onSuccess: () => {
                closeModal();
                emit('success');
            }
        });
    } else {
        form.post(route('nota-skpd.store'), {
            onSuccess: () => {
                closeModal();
                emit('success');
            }
        });
    }
};

const closeModal = () => {
    form.reset();
    emit('close');
};

watch(
    () => props.show,
    (show) => {
        if (show && props.isEdit && props.notaData) {
            Object.assign(form, props.notaData);
        }
    },
    { immediate: true }
);
</script>
