<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
// @ts-ignore
import { can , is} from 'laravel-permission-to-vuejs'


const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

interface Action {
  label: string;
  onClick: (employee: any) => void;
  class: string;
}


const columns = [
    { label: "ID", key: "id" },
    { label: "Name", key: "name" },
    { label: "Category", key: "category.name" },
    { label: "Price", key: "price" },
    { label: "Stock", key: "stock" },
    { label: "Weight", key: "weight" },
    { label: "Weight per Unit", key: "weight_per_unit" },
    { label: "Total Cost", key: "total_cost" },
    { label: "Remaining units", key: "remaining_units" },
    { label: "Supplier", key: "supplier.name" },  // Assuming the supplier relationship is loaded
    { label: "Expiration Date", key: "exp_date" },
    { label: "Batch Number", key: "batch_number" },
];

const searchQuery = ref("");

const deleteProduct = (product: any) => {
    console.log('Delete Product:', product);
};



let actions: Action[] = [];
if (is('Super Admin')) {
 actions = [
    { label: "Delete", onClick: deleteProduct, class: "text-red-600 hover:text-red-900" },
    {
        label: "Edit",
        onClick: (product: any) => console.log("Edit", product),
        class: "text-blue-600 hover:text-blue-900",
    },
];}

// Function to export products as CSV
const exportToCSV = () => {
    const csvHeader = columns.map(col => col.label).join(",") + "\n";
    const csvRows = props.products.map(product =>
        columns.map(col => {
            const keys = col.key.split(".");
            let value = product;
            for (const key of keys) {
                value = [key];
                if (value === undefined) break;
            }
            return `"${value ?? ''}"`;
        }).join(",")
    ).join("\n");

    const csvContent = csvHeader + csvRows;
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "products.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head title="Product Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Product Management</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header with Search Bar, Export Button, and New Product Button -->
                <div class="flex justify-between items-center mb-6">
                    <div class="relative w-1/3">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search products..." 
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" 
                        />
                    </div>
                    <div class="flex space-x-4">
                        <button 
                            @click="exportToCSV" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
                        >
                            Export to CSV
                        </button>
                        <Link
                         :href="route('products.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
                        >
                            New Product
                    </Link>
                    </div>
                </div>

                <!-- DataTable Component -->
                <DataTable :columns="columns" :data="products" :searchQuery="searchQuery" :actions="actions" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
