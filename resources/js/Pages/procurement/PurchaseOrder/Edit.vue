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
    purchaseOrder: {
        id: number;
        order_number: string;
        supplier_id: number;
        category_id: number;
        total_cost: number;
        order_date: number;
        delivery_status: string;
    };
    categories: Category[];
    suppliers: Category[];
}>();

console.log(props.purchaseOrder)

const form = reactive({
    order_number: props.purchaseOrder.order_number,
    supplier_id: props.purchaseOrder.supplier_id,
    category_id: props.purchaseOrder.category_id,
    total_cost: props.purchaseOrder.total_cost,
    order_date: props.purchaseOrder.order_date,
    delivery_status: props.purchaseOrder.delivery_status,
});




const submitForm = () => {
    router.put(route('purchase-orders.update', props.purchaseOrder.id), form);
};
</script>

<template>

    <Head title="Edit Supply" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Order
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ $page.props.flash.success }}
        </div>
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <form @submit.prevent="submitForm">

                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order
                                Number</label>
                            <input v-model="form.order_number" type="text" id="name"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required />
                        </div>

                        <div class="mb-6">
                            <select v-model="form.supplier_id" id="category"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required>
                                <option value="" disabled>Select a Supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <select v-model="form.category_id" id="category"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required>
                                <option value="" disabled>Select a Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>


                        <div class="mb-6">
                            <label for="total_cost"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                Cost</label>
                            <input v-model="form.total_cost" id="total_cost" type="number"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required>
                        </div>

                        <div class="mb-6">
                            <label for="order_date"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order
                                Date</label>
                            <input v-model="form.order_date" id="order_date" type="date"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required>
                        </div>

                        <div class="mb-6">
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="form.delivery_status" id="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                                required>
                                <option value="" disabled>Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit"
                                class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-md">
                                Save
                            </button>
                            <button @click="$inertia.visit(route('purchase-orders.index'))" type="button"
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
