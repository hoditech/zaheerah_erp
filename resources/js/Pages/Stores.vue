<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';

const stores = ref([
    { id: 1, name: "Main Store", location: "123 Main St", manager: "Alice Johnson", status: "Open" },
    { id: 2, name: "Downtown Outlet", location: "45 Downtown Ave", manager: "Bob Smith", status: "Closed" },
    { id: 3, name: "Westside Branch", location: "78 West St", manager: "Carol White", status: "Open" },
]);

const columns = [
    { label: "ID", key: "id" },
    { label: "Name", key: "name" },
    { label: "Location", key: "location" },
    { label: "Manager", key: "manager" },
    { label: "Status", key: "status" },
];

const searchQuery = ref("");

const deleteStore = (store: any) => {
    stores.value = stores.value.filter(s => s.id !== store.id);
};

const actions = [
    { label: "Delete", onClick: deleteStore, class: "text-red-600 hover:text-red-900" },
    {
        label: "Edit",
        onClick: (store: any) => console.log("Edit", store),
        class: "text-blue-600 hover:text-blue-900",
    },
];
</script>

<template>
    <Head title="Stores Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Stores Management</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header with Search Bar and New Store Button -->
                <div class="flex justify-between items-center mb-6">
                    <div class="relative w-1/3">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search stores..." 
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" 
                        />
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.742 10.988a4.5 4.5 0 1 1 .707.707l4.47 4.47a1 1 0 0 1-1.414 1.414l-4.47-4.47a4.5 4.5 0 0 1-.707-.707zM12 9.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <router-link 
                        to="/stores/new" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
                    >
                        New Store
                    </router-link>
                </div>

                <!-- DataTable Component -->
                <DataTable :columns="columns" :data="stores" :searchQuery="searchQuery" :actions="actions" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
