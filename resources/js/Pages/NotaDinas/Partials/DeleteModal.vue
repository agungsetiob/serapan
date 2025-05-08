<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  nota: Object,
  show: Boolean
});


const emit = defineEmits(['close']);

const form = useForm({});

function deleteNota() {
    form.delete(route('nota-dinas.destroy', props.nota.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onError: () => {
            // Handle error
        }
    });
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="xl">
        <div class="bg-white rounded-lg p-6 w-full">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                <button @click="$emit('close')">✖</button>
            </div>
            
            <p v-if="nota" class="mb-6">Anda yakin ingin menghapus nota dinas no <span class="font-semibold text-red-400">{{ nota?.nomor_nota }}</span>
                <br>Data yang sudah dihapus tidak dapat dikembalikan.</p>
            
            <div class="flex justify-end space-x-3">
                <button @click="$emit('close')"
                        class="px-3 py-2 bg-gray-300 text-gray-700 text-sm sm:text-base rounded hover:bg-gray-400">
                    Batal
                </button>
                <button @click="deleteNota"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
                    <span v-if="form.processing">
                        <font-awesome-icon icon="spinner" spin class="mr-2" />
                        Menghapus...
                    </span>
                    <span v-else>
                        Ya, Hapus
                    </span>
                </button>
            </div>
        </div>
    </Modal>
</template>