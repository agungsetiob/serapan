<script setup>
import { computed, ref, watch } from 'vue';
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessFlash from '@/Components/SuccessFlash.vue';

const search = ref('');
watch(search, (val) => {
  router.get(route('users.index'), { search: val }, { preserveState: true, replace: true });
});
const flash = computed(() => page.props.flash || {});
const clearFlash = () => {
  flash.value.success = null;
};
const props = defineProps({
    users: Object,
});

const page = usePage();

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
        <div class="pt-6 sm:pt-24 mx-2 sm:px-2">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Pengguna</h2>
                        <Link
                           :href="route('register')"
                           preserve-scroll
                           class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-500 text-white text-sm sm:text-base font-medium rounded hover:bg-indigo-700">
                            + Tambah Pengguna
                        </Link>
                    </div>

                    <div v-if="flash.error" class="mb-4">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error! </strong>
                            <span class="block sm:inline">{{ flash.error }}</span>
                            <button @click="flash.success = null" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-green-700" role="button" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <title>Close</title>
                                    <path d="M14.348 5.652a1 1 0 00-1.414-1.414L10 7.172 7.066 4.238a1 1 0 10-1.414 1.414L8.586 8.586 5.652 11.52a1 1 0 101.414 1.414L10 10.828l2.934 2.934a1 1 0 001.414-1.414L11.414 8.586l2.934-2.934z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr class="bg-gray-300 text-left">
                                    <th class="px-4 py-2">Nama</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Role</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-red-50 transition even:bg-gray-100">
                                    <td class="px-4 py-2">{{ user.name }}</td>
                                    <td class="px-4 py-2 text-blue-600">{{ user.email }}</td>
                                    <td class="px-4 py-2">
                                        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <button
                                            @click="toggleStatus(user.id, user.status)"
                                            type="button"
                                            :class="[
                                              'relative z-0 inline-flex items-center h-8 rounded-full focus:outline-none transition-colors duration-300',
                                              user.status ? 'bg-green-500 w-16' : 'bg-red-500 w-24'
                                            ]"
                                        >
                                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                                                <span :class="user.status ? 'ml-5' : 'mr-4'">
                                                    {{ user.status ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </span>
                                            <span
                                              class="absolute left-1 top-1 h-6 w-6 bg-white rounded-full shadow transform transition duration-300"
                                              :class="user.status ? 'translate-x-15' : 'translate-x-16'"
                                            ></span>
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
    </AuthenticatedLayout>
</template>
