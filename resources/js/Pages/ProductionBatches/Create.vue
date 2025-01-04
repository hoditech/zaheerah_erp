<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface Category {
    id: number;
    name: string;
}

defineProps({
    categories: {
        type: Array as () => Category[],
        required: true,
    },
});

const form = reactive({
    product_id: '',
    quantity: '',
    material_cost: '',
    labor_cost: '',
    overhead_cost: '',
    batch_status: '',
    description: '',
});

const submitForm = () => {
    router.post(route('productions.store'), form);
};
</script>

<template>

    <Head title="Add Production Batch" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Add New Production Batch
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <form @submit.prevent="submitForm">
                        <!-- Batch Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-black dark:text-gray-200 py-4">Batch Details</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                  
                                <div>
                                    <label for="category"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                    <select v-model="form.product_id" id="category"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                        required>
                                        <option value="" disabled>Select a Product</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="quantity"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                    <input v-model="form.quantity" type="number" id="quantity"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                        required />
                                </div>
                            </div>
                        </div>

                        <!-- Costs -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">Costs</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <label for="material_cost"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Material
                                        Cost</label>
                                    <input v-model="form.material_cost" type="number" id="material_cost"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                                </div>
                                <div>
                                    <label for="labor_cost"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Labor
                                        Cost</label>
                                    <input v-model="form.labor_cost" type="number" id="labor_cost"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                                </div>
                                <div>
                                    <label for="overhead_cost"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Overhead
                                        Cost</label>
                                    <input v-model="form.overhead_cost" type="number" id="overhead_cost"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                                </div>
                            </div>

                        </div>

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-black dark:text-gray-200 py-4">Additional Information</h3>
                            
                            <div class="mb-6">
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="form.batch_status" id="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                                required>
                                <option value="" disabled>Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                            <div class="mb-8">
                                <label for="description"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea v-model="form.description" id="description"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                    rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-4">
                            <button type="submit"
                                class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-md">
                                Save
                            </button>
                            <button @click="$inertia.visit(route('productions.index'))" type="button"
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
