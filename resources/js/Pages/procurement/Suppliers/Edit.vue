<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

interface Category {
    id: number;
    name: string;
}

interface Supplier {
    id: number;
    name: string;
}



const props = defineProps<{
    supplier: {
        id: number;
        name: string;
        contact_email: string;
        contact_phone: string;
        address: string;
        category_id: string;
        status: string;
    };
    categories: Category[];
}>();

console.log(props.supplier)

const form = reactive({
    name: props.supplier.name,
    contact_email: props.supplier.contact_email,
    contact_phone: props.supplier.contact_phone,
    address: props.supplier.address,
    category_id: props.supplier.category_id,
    status: props.supplier.status,
});

// Initialize form with existing data when component is mounted


const submitForm = () => {
    router.put(route('suppliers.update', props.supplier.id), form);
};
</script>

<template>

    <Head title="Edit Supply" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Supply
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <form @submit.prevent="submitForm">
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name
                                Name</label>
                            <input v-model="form.name" type="text" id="name"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required />
                        </div>

                        <div class="mb-6">
                            <label for="contact_email"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Email</label>
                            <input v-model="form.contact_email" type="email" id="contact_email"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required />
                        </div>

                        <div class="mb-6">
                            <label for="contact_phone"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Phone</label>
                            <input v-model="form.contact_phone" type="text" id="contact_phone"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required />
                        </div>

                        <div class="mb-6">
                            <label for="address"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <input v-model="form.address" id="address"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required>
                        </div>
                        <div class="mb-6">
                        <select v-model="form.category_id" id="category"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                            required>
                            <option value="" disabled>Select a Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        </div>

                        <div class="mb-6">
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="form.status" id="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required>
                                <option value="" disabled>Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                
                            </select>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit"
                                class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-md">
                                Update
                            </button>
                            <button @click="$inertia.visit(route('suppliers.index'))" type="button"
                                class="px-6 py-2 text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 rounded-md">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
