<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>

    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8 sm:pt-12">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                        <div class="hidden md:flex justify-center md:justify-center">
                            <img src="/img/logo.png" alt="Logo" class="max-w-full h-auto md:max-w-xs" />
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <UpdatePasswordForm class="max-w-xl" />
                        <div class="hidden md:flex justify-center md:justify-center">
                            <img src="/img/beraksi.png" alt="Logo" class="max-w-full h-auto md:max-w-xs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
