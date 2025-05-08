<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const isLoading = ref(false);
document.addEventListener('inertia:start', () => {
  isLoading.value = true;
});

document.addEventListener('inertia:finish', () => {
  isLoading.value = false;
});
const currentYear = computed(() => new Date().getFullYear());

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-pattern pt-6 sm:justify-center sm:pt-0"
    >
        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <div class="text-center">
                <Link href="/" class="inline-block">
                    <ApplicationLogo class="h-20 w-20 fill-current text-red-500" />
                </Link>
                <p class="font-bold">Monitoring dan Analisa Serapan Anggaran</p>
            </div>
            <!-- <div v-if="isLoading" class="fixed inset-0 bg-white bg-opacity-70 z-50 flex items-center justify-center">
                <font-awesome-icon icon="spinner" spin class="text-6xl text-green-400" />
            </div> -->
            <div v-if="isLoading" class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-70">
                <svg class="animate-spin h-20 w-20 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <slot />
            <footer class="text-gray-800 py-4 text-center w-full">
                <div class="max-w-7xl mx-auto px-4">
                    <p class="text-sm">&copy; {{ currentYear }} {{ appName }} Kabupaten Tanah Bumbu</p>
                </div>
            </footer>
        </div>
    </div>
</template>
