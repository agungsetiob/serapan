<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    tahunSumber: Number,
    tahunTarget: Number,
    isCopying: Boolean
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Modal :show="show" @close="emit('cancel')" max-width="xl">
        <div class="p-4">
            <p class="text-md mb-6 text-gray-700 text-center">
                Salin semua program, kegiatan, dan sub kegiatan dari tahun <strong class="text-red-500">{{ tahunSumber }}</strong> ke
                <strong class="text-green-500">{{ tahunTarget }}</strong>?
                <br>Proses ini tidak akan menghapus data yang sudah ada.
            </p>
            <div class="flex justify-end gap-2">
                <SecondaryButton @click="emit('cancel')" :disabled="isCopying" class="disabled:cursor-not-allowed">
                    Batal
                </SecondaryButton>
                <PrimaryButton :disabled="isCopying" @click="$emit('confirm')" class="disabled:cursor-not-allowed">
                    <font-awesome-icon v-if="isCopying" icon="spinner" spin class="mr-2" />
                    {{ isCopying ? 'Sedang menyalin...' : 'Ya, Copy' }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
