<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import LoginButton from '@/Components/LoginButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ErrorFlash from '@/Components/ErrorFlash.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const passwordVisible = ref(false);
const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const page = usePage();
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
    flash.value.error = null;
};
</script>

<template>
    <GuestLayout>
        <template #subheader>Monitoring dan Analisis Serapan Anggaran</template>

        <Head title="Log in" />
        
        <div class="flex flex-col items-center mb-2">
            <img 
                src="/img/beraksi.png" 
                alt="Logo BerAKSI"
                class="h-24 mb-2"
            />
        </div>

        <ErrorFlash :flash="flash" @clearFlash="clearFlash" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Email" class="block text-sm font-medium text-gray-700 mb-1" />
                
                <div class="relative">
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        :class="{ 'border-red-500': form.errors.email }"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="your@mail.com"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <font-awesome-icon :icon="['far', 'envelope']" />
                    </div>
                </div>
                
                <InputError class="mt-1 text-sm text-red-600" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <InputLabel for="password" value="Kata Sandi" class="block text-sm font-medium text-gray-700" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-blue-600 hover:text-blue-500"
                    >
                        Lupa kata sandi?
                    </Link>
                </div>
                
                <div class="relative">
                    <TextInput
                        id="password"
                        :type="passwordVisible ? 'text' : 'password'"
                        class="block w-full px-4 py-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        :class="{ 'border-red-500': form.errors.password }"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        @click="togglePasswordVisibility"
                        :aria-label="passwordVisible ? 'Sembunyikan password' : 'Tampilkan password'"
                    >
                        <font-awesome-icon :icon="passwordVisible ? 'fas fa-eye-slash' : 'fas fa-eye'" />
                    </button>
                </div>
                
                <InputError class="mt-1 text-sm text-red-600" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                <InputLabel for="remember" value="Ingat saya" class="ml-2 block text-sm text-gray-700" />
            </div>

            <div>
                <LoginButton
                    class="w-full"
                    :disabled="form.processing"
                >
                    Masuk
                </LoginButton>
            </div>
        </form>
    </GuestLayout>
</template>