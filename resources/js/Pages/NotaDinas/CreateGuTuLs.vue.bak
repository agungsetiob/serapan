<script setup>
import { computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    skpd: Object,
    parentNotes: Array,
    tahun: String,
});

const form = useForm({
    nomor_nota: '',
    perihal: '',
    anggaran: '',
    tanggal_pengajuan: '',
    jenis: '',
    parent_ids: [],
    lampirans: null, // FileList atau null
});

const submit = () => {
    form.post(route('store-gutuls'), {
        forceFormData: true, 
        onSuccess: () => form.reset(),
    });
};
const handleFileChange = (event) => {
    form.lampirans = event.target.files;
};
const lampiranItemErrors = computed(() => {
    if (!form.errors) return [];
    return Object.entries(form.errors)
        .filter(([key]) => key.startsWith('lampirans.'))
        .map(([_, message]) => message);
});

</script>

<template>
    <Head title="Tambah Nota GU/TU/LS" />

    <AuthenticatedLayout>
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        Buat GU/TU/LS - {{ skpd.nama_skpd }}
                    </h2>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block font-semibold">Nomor Nota</label>
                        <input v-model="form.nomor_nota" type="text" class="input w-full" />
                        <InputError :message="form.errors.nomor_nota" />
                    </div>

                    <div>
                        <label class="block font-semibold">Perihal</label>
                        <input v-model="form.perihal" type="text" class="input w-full" />
                        <InputError :message="form.errors.perihal" />
                    </div>

                    <div>
                        <label class="block font-semibold">Anggaran (Rp)</label>
                        <input v-model="form.anggaran" type="number" step="0.01" class="input w-full" />
                        <InputError :message="form.errors.anggaran" />
                    </div>

                    <div>
                        <label class="block font-semibold">Tanggal Pengajuan</label>
                        <input v-model="form.tanggal_pengajuan" type="date" class="input w-full" />
                        <InputError :message="form.errors.tanggal_pengajuan" />
                    </div>

                    <div>
                        <label class="block font-semibold">Jenis</label>
                        <select v-model="form.jenis" class="input w-full">
                            <option disabled value="">-- Pilih Jenis Nota --</option>
                            <option value="GU">GU</option>
                            <option value="TU">TU</option>
                            <option value="LS">LS</option>
                        </select>
                        <InputError :message="form.errors.jenis" />
                    </div>

                    <div>
                        <label class="block font-semibold">Pilih Nota Induk (Pelaksanaan/Perbup/Lain-lain)</label>
                        <select v-model="form.parent_ids" multiple class="input w-full h-40">
                            <option
                                v-for="nota in parentNotes"
                                :key="nota.id"
                                :value="nota.id"
                                :disabled="nota.sisa_anggaran <= 0"
                            >
                                {{ nota.nomor_nota }} - {{ nota.perihal }} - Sisa: Rp. {{ nota.sisa_anggaran.toLocaleString('id-ID') }}
                            </option>
                        </select>
                        <InputError :message="form.errors.parent_ids" />
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
                        <div v-if="form.errors.lampirans || lampiranItemErrors.length" class="text-sm text-red-600 mt-1">
                            <div v-if="form.errors.lampirans">{{ form.errors.lampirans }}</div>
                            <div v-for="(err, index) in lampiranItemErrors" :key="index">
                                {{ err }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing">Simpan Nota</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.input {
    @apply border border-gray-300 rounded-lg px-4 py-2 w-full;
}
</style>
