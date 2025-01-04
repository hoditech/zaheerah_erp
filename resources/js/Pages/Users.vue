<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';

const users = ref([
    { id: 1, name: "Yahaya Hudu", email: "huduyahaya@gmail.com", role: "Admin", status: "Active" },

]);

const columns = [
    { label: "ID", key: "id" },
    { label: "Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Role", key: "role" },
    { label: "Status", key: "status" },
];

const searchQuery = ref("");

const deleteUser = (user: any) => {
    users.value = users.value.filter(u => u.id !== user.id);
};

const actions = [
    { label: "Delete", onClick: deleteUser, class: "text-red-600 hover:text-red-900" },
    {
        label: "Edit",
        onClick: (user: any) => console.log("Edit", user),
        class: "text-blue-600 hover:text-blue-900",
    },
];
</script>

<template>

    <Head title="User Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">User Management</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header with Search Bar and New User Button -->
                <div class="flex justify-between items-center mb-6">
                    <div class="relative w-1/3">
                        <input v-model="searchQuery" type="text" placeholder="Search users..."
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" />
                    </div>
                    <router-link to="/users/new"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out">
                        New User
                    </router-link>
                </div>

                <!-- DataTable Component -->
                <DataTable :columns="columns" :data="users" :searchQuery="searchQuery" :actions="actions"
                    :pageSize="2" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
