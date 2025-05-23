<script setup>
import { ref, computed } from 'vue';

const isLoading = ref(false);
document.addEventListener('inertia:start', () => {
  isLoading.value = true;
});

document.addEventListener('inertia:finish', () => {
  isLoading.value = false;
});
const currentYear = computed(() => new Date().getFullYear());

const appName = import.meta.env.VITE_APP_NAME || 'Monitoring Anggaran';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-100 to-green-100 flex flex-col">
        <!-- Main Content -->
        <main class="flex-grow flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-green-600 p-6 text-center text-white">
                    <h1 class="text-2xl font-bold"><slot name="header" /></h1>
                    <p class="mt-1 opacity-90"><slot name="subheader" /></p>
                </div>
                
                <div class="p-8">
                    <slot />
                </div>
                <footer class="bg-white pb-4">
                    <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
                        <p>&copy; {{ currentYear }} {{ appName }} - Pemerintah Kabupaten Tanah Bumbu</p>
                        <p class="mt-1"></p>
                    </div>
                </footer>
            </div>
        </main>

        <!-- Loading Overlay -->
        <div v-if="isLoading" class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-70">
            <svg class="animate-spin h-20 w-20 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</template>