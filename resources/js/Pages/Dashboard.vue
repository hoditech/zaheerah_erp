<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import StatisticCard from '@/Components/StatisticCard.vue';
import ActivityPanel from '@/Components/ActivityPanel.vue';

defineProps({
    totalRevenue: String,
    totalProfit: String,
    totalStock: Number,
    totalCost: Number,
    categories: Array,
    recentActivities: Array,
    users: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <header class="flex items-center justify-between px-4 py-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Admin Dashboard</h2>
            </header>
        </template>

        <main class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <section class="container mx-auto max-w-7xl space-y-12">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <StatisticCard title="Total Revenue" :value="totalRevenue" color="yellow" subtitle="Monthly Revenue" />
                    <StatisticCard title="Total Cost" :value="totalProfit" color="red" subtitle="Monthly Costs" />
                    <StatisticCard title="Profit" :value="totalCost" color="green" subtitle="Monthly Profit" />
                    <StatisticCard title="Inventory Level" :value="totalStock" color="green" subtitle="Items" />
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                    <h3 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Financial Indicators by Category</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="category in categories"
                            :key="category.id"
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow p-6 hover:shadow-xl transition-all duration-300"
                        >
                            <h4 class="text-xl font-medium text-gray-800 dark:text-gray-200">{{ category.name }}</h4>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Revenue:</span>
                                    <span class="font-semibold text-grey-900 dark:text-green-400">{{ category.revenue }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Cost:</span>
                                    <span class="font-semibold text-red-600 dark:text-red-400">{{ category.cost }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Profit:</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">{{ category.profit }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Inventory level:</span>
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">{{ category.inventory }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                    <h3 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Employee Records</h3>
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium text-sm">
                                <th class="p-3 border-b dark:border-gray-600">Name</th>
                                <th class="p-3 border-b dark:border-gray-600">Email</th>
                                <th class="p-3 border-b dark:border-gray-600">Phone Number</th>
                                <th class="p-3 border-b dark:border-gray-600">Role</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700 dark:text-gray-200">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="p-3 border-b dark:border-gray-600">{{ user.name }}</td>
                                <td class="p-3 border-b dark:border-gray-600">{{ user.email }}</td>
                                <td class="p-3 border-b dark:border-gray-600">{{ user.phone }}</td>
                                <td class="p-3 border-b dark:border-gray-600">
                                    <span :class="user.status === 'Active' ? 'text-green-500' : 'text-red-500'">{{ user.role }}</span>
                                </td>
                                
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-1">
                    <ActivityPanel title="Recent Activity" :items="recentActivities" />
                </div>

                
            </section>
        </main>
    </AuthenticatedLayout>
</template>
