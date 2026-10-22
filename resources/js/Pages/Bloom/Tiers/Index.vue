<script setup lang="ts">
// @ts-nocheck
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    tiers: {
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
    { label: 'Subtitle', key: 'subtitle' },
    { label: 'Volume', key: 'range_display' },
    { label: 'Price / carton', key: 'price_per_carton' },
    { label: 'Badge', key: 'badge' },
    { label: 'Theme', key: 'theme' },
    { label: 'Visible', key: 'is_active' },
    { label: 'Order', key: 'sort_order' },
];

const actions = [];

if (props.canManage) {
    actions.push({
        label: 'Edit',
        onClick: (tier: any) => router.visit(route('bloom-tiers.edit', tier.id)),
        class: 'text-blue-600 hover:text-blue-900',
    });
    actions.push({
        label: 'Delete',
        onClick: (tier: any) => {
            if (confirm(`Delete the ${tier.name} tier? This cannot be undone.`)) {
                router.delete(route('bloom-tiers.destroy', tier.id));
            }
        },
        class: 'text-red-600 hover:text-red-900',
    });
}
</script>

<template>
    <Head title="Bloom Tier Benefits" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">
                Bloom Tier Benefits &amp; Pricing
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
                    {{ $page.props.flash.success }}
                </div>

                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    These cards appear on the public site at
                    <span class="font-mono">/zaheerah-bloom</span>. The frontsite mirrors them from the ERP
                    on the next <span class="font-mono">erp:sync-bloom</span> run.
                </p>

                <div class="flex justify-between items-center mb-6">
                    <div class="relative w-1/3">
                        <input v-model="searchQuery" type="text" placeholder="Search tiers..."
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" />
                    </div>
                    <Link v-if="canManage" :href="route('bloom-tiers.create')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md">
                        New Bloom Tier
                    </Link>
                </div>

                <DataTable :columns="columns" :data="tiers" :searchQuery="searchQuery" :actions="actions" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
