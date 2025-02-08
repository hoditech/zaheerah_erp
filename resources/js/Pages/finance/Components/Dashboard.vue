<template>
    <div>
        <h3 class="text-2xl font-semibold mb-4">Dashboard</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <SummaryCard title="Total Revenue" :value="totalRevenue" color="text-orange-500"/>
            <SummaryCard title="Total Cost" :value="totalCost" color="text-red-500"/>
            <SummaryCard title="Net Profit" :value="totalProfit" color="text-green-500"/>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Category Breakdown</h4>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 dark:bg-gray-700">
                        <th class="py-3 px-4 text-left">Category</th>
                        <th class="py-3 px-4 text-left">Revenue</th>
                        <th class="py-3 px-4 text-left">Cost</th>
                        <th class="py-3 px-4 text-left">Profit</th>
                        <th class="py-3 px-4 text-left">Inventory</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id"
                        class="border-t border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <td class="py-3 px-4">{{ category.name }}</td>
                        <td class="py-3 px-4">{{ category.revenue }}</td>
                        <td class="py-3 px-4">{{ category.cost }}</td>
                        <td class="py-3 px-4">{{ category.profit }}</td>
                        <td class="py-3 px-4">{{ category.inventory }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import SummaryCard from './SummaryCard.vue';

import { PropType } from 'vue';

defineProps({
    totalRevenue: String,
    totalCost: String,
    totalProfit: String,
    categories: Array as PropType<{ id: number; name: string; revenue: number; cost: number; profit: number; inventory: number; }[]>,
});

</script>