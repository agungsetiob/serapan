<template>
  <!-- Attachment List Modal -->
  <Modal :show="show" @close="closeModal">
    <div class="rounded-lg shadow-lg p-6 w-full">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Daftar Lampiran</h2>
        <button @click="closeModal">✖</button>
      </div>

      <ul class="list-disc pl-5 text-sm text-gray-700">
        <li v-if="loading" class="text-gray-500">Memuat lampiran...</li>
        <li v-else-if="error" class="text-red-500">{{ error }}</li>
        <li v-else-if="lampiranList.length === 0" class="text-gray-500">Tidak ada lampiran.</li>
        <li
          v-for="(file, index) in lampiranList"
          :key="index"
          class="mb-2 cursor-pointer text-blue-600 hover:underline"
          @click="openFilePreview(file)"
        >
          {{ file.name }}
          <span class="text-gray-500 text-xs"> ({{ formatDate(file.created_at) }})</span>
        </li>
      </ul>
    </div>
  </Modal>

  <!-- Attachment Preview Modal -->
  <Modal :show="!!selectedFile" @close="closeFilePreview" maxWidth="fullscreen">
    <div class="h-dvh p-6 flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">{{ selectedFile?.name }}</h3>
        <button @click="closeFilePreview">✖</button>
      </div>
      <div class="flex-1 overflow-hidden">
        <iframe 
          v-if="selectedFile"
          :src="selectedFile.url" 
          class="w-full h-full border-0 bg-white" 
          frameborder="0"
        />
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  notaId: {
    type: [Number, String],
    required: false
  }
})

const emit = defineEmits(['close'])

const lampiranList = ref([])
const loading = ref(false)
const error = ref(null)
const selectedFile = ref(null)
const lastFetchedNotaId = ref(null)

watch(() => props.show, (newVal) => {
  if (newVal) {
    checkAndFetchLampiran()
  }
})

onMounted(() => {
  if (props.show) {
    checkAndFetchLampiran()
  }
})

const closeModal = () => {
  emit('close')
  lastFetchedNotaId.value = null
}

const closeFilePreview = () => {
  selectedFile.value = null
}

const openFilePreview = (file) => {
  selectedFile.value = file
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleString('id-ID')
}

async function checkAndFetchLampiran() {
  if (!props.notaId) {
    console.warn('Nota ID tidak tersedia!')
    return
  }

  if (lastFetchedNotaId.value === props.notaId) {
    return
  }

  await fetchLampiran()
  lastFetchedNotaId.value = props.notaId
}

async function fetchLampiran() {
  loading.value = true
  error.value = null
  lampiranList.value = []

  try {
    const response = await fetch(`/nota/lampiran/${props.notaId}`)
    if (!response.ok) {
      throw new Error('Gagal memuat lampiran')
    }

    const result = await response.json()
    lampiranList.value = Array.isArray(result.data) ? result.data : []
  } catch (err) {
    error.value = err.message || 'Terjadi kesalahan'
  } finally {
    loading.value = false
  }
}
</script>