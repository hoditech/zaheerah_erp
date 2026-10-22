<script setup lang="ts">
// @ts-nocheck
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref('');

const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Name', key: 'name' },
    { label: 'Pack', key: 'pack_description' },
    { label: 'Price / carton', key: 'price_per_carton' },
    { label: 'Retail margin', key: 'retail_margin' },
    { label: 'Badge', key: 'badge' },
    { label: 'Highlighted', key: 'is_highlighted' },
    { label: 'Visible', key: 'is_active' },
    { label: 'Order', key: 'sort_order' },
];

const actions = [];

if (props.canManage) {
    actions.push({
        label: 'Edit',
        onClick: (product: any) => router.visit(route('bloom-products.edit', product.id)),
        class: 'text-blue-600 hover:text-blue-900',
    });
    actions.push({
        label: 'Delete',
        onClick: (product: any) => {
            if (confirm(`Delete ${product.name}? This cannot be undone.`)) {
                router.delete(route('bloom-products.destroy', product.id));
            }
        },
        class: 'text-red-600 hover:text-red-900',
    });
}
</script>

<template>
    <Head title="Bloom Carton Cards" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">
                Bloom Carton Cards
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
                    {{ $page.props.flash.success }}
                </div>

                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    These are the “Best-Selling Cartons for Retailers” cards on the public site at
                    <span class="font-mono">/zaheerah-bloom</span>. The frontsite mirrors them from the ERP
                    on the next <span class="font-mono">erp:sync-bloom</span> run.
                </p>

                <div class="flex justify-between items-center mb-6">
                    <div class="relative w-1/3">
                        <input v-model="searchQuery" type="text" placeholder="Search carton cards..."
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" />
                    </div>
                    <Link v-if="canManage" :href="route('bloom-products.create')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md">
                        New Carton Card
                    </Link>
                </div>

                <DataTable :columns="columns" :data="products" :searchQuery="searchQuery" :actions="actions" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
