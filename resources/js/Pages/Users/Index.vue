<script setup>
import { computed, ref, watch } from 'vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/formatters'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';
import RegisterModal from './Partials/RegisterModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import ErrorFlash from '@/Components/ErrorFlash.vue';

const search = ref('');
watch(search, (val) => {
    router.get(route('users.index'), { search: val }, { preserveState: true, replace: true });
});
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
    flash.value.success = null;
    flash.value.error = null;
};
const props = defineProps({
    users: Object,
});

const page = usePage();

const showRegisterModal = ref(false);
const openRegisterModal = () => showRegisterModal.value = true;
const closeRegisterModal = () => showRegisterModal.value = false;
function toggleStatus(userId, currentStatus) {
    const form = useForm({
        status: currentStatus ? 0 : 1,
    });

    form.patch(route('users.toggle-status', userId), {
        preserveScroll: true,
    });
}
</script>

<template>

    <Head title="Users" />
    <AuthenticatedLayout>
        <SuccessFlash :flash="flash" @clearFlash="clearFlash" />
        <ErrorFlash :flash="flash" @clearFlash="clearFlash" />
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Pengguna</h2>
                        <button @click="openRegisterModal"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            + Tambah Pengguna
                        </button>
                    </div>
                    <SearchInput v-model:search="search" />
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr class="bg-gray-300 text-left">
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Role</th>
                                    <th class="px-4 py-2">Created at</th>
                                    <th class="px-4 py-2">Last Update</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id"
                                    class="hover:bg-red-50 transition even:bg-gray-100">
                                    <td class="px-4 py-2">{{ user.name }}</td>
                                    <td class="px-4 py-2 text-blue-600">{{ user.email }}</td>
                                    <td class="px-4 py-2">
                                        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                    </td>
                                    <td class="px-4 py-2">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-4 py-2">{{ formatDate(user.updated_at) }}</td>
                                    <td class="px-4 py-2">
                                        <button @click="toggleStatus(user.id, user.status)" type="button" :class="[
                                            'relative z-0 inline-flex items-center h-8 rounded-full focus:outline-none transition-colors duration-300',
                                            user.status ? 'bg-green-500 w-16' : 'bg-red-500 w-24'
                                        ]">
                                            <span
                                                class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                                                <span :class="user.status ? 'ml-5' : 'mr-4'">
                                                    {{ user.status ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </span>
                                            <span
                                                class="absolute left-1 top-1 h-6 w-6 bg-white rounded-full shadow transform transition duration-300"
                                                :class="user.status ? 'translate-x-15' : 'translate-x-16'"></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="px-4 py-2 text-center">Belum ada pengguna</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <RegisterModal :show="showRegisterModal" @close="closeRegisterModal" />
    </AuthenticatedLayout>
</template>
