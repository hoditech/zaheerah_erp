<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import StatisticCard from '@/Components/StatisticCard.vue';
import ActivityPanel from '@/Components/ActivityPanel.vue';
// @ts-ignore
import { is, can } from 'laravel-permission-to-vuejs'


interface Category {
    id: number;
    name: string;
    revenue: string,
    cost: string,
    profit: string,
    inventory: string
}

interface User {
    id: number,
    name: string,
    email: string,
    phone: string
    role: string,
    status: string,
}

interface ActivityItem {
    description: string;
    time: string;
}

defineProps({
    totalRevenue: {
        type: String,
        default: '0'
        
    },
    totalProfit: {
        type: String,
        default: '0'
    },
    totalStock: {
        type: String,
        default: '0'
    },
    totalCost: {
        type: String,
        default: '0'
    },
    categories: Array as () => Category[],
    recentActivities: {
        type: Array as () => ActivityItem[],
        default: [],
    },
    users: Array as () => User[],
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


        <div class="p-6">
            <!-- Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Employees</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">256</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Active Employees</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">240</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">New Hires (This Month)</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">8</p>
                </div>
            </div>

            <!-- Notice Board Section -->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm mb-8">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Notice Board</h3>
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Company Holiday Announcement</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">The company will be closed on December 25th and 26th for the Christmas holiday. Wishing everyone a joyful celebration!</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Posted on: December 20, 2023</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">New Policy Update</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Starting January 1st, 2024, the company will implement a new remote work policy. Please review the updated guidelines in the employee handbook.</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Posted on: December 15, 2023</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Team Building Event</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Join us for a team-building event on December 22nd at 3 PM in the main conference room. Snacks and refreshments will be provided!</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Posted on: December 18, 2023</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events Section -->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm mb-8">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Upcoming Events</h3>
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Annual Company Dinner</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Join us for the annual company dinner on January 10th, 2024, at 7 PM. Venue: Grand Ballroom, City Hotel.</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Date: January 10, 2024</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Training Workshop</h4>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">A training workshop on "Effective Communication Skills" will be held on January 15th, 2024, from 10 AM to 2 PM.</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Date: January 15, 2024</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-sm mb-8">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Recent Employee Activity</h3>
                <ul class="space-y-4">
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">John Doe updated their profile</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">2 hours ago</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Jane Smith completed a training session</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">5 hours ago</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">New employee "Alice Johnson" onboarded</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">1 day ago</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- <div class="flex flex-col items-center justify-center h-[70vh] text-center">
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-gray-300 dark:border-gray-600 rounded-full"></div>
                <div class="absolute inset-0 border-t-4 border-blue-500 rounded-full animate-spin"></div>
            </div>
            <h3 class="mt-6 text-2xl font-semibold text-gray-800 dark:text-gray-200">
                Page Under Development
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-md">
                We are currently working on this section to improve your experience.  
                Please check back soon for updates.
            </p>
        </div> -->


        <!-- <main class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <section class="container mx-auto max-w-7xl space-y-12">
                <div v-if="is('Super Admin | Admin')" class="grid grid-cols-1 gap-6 md:grid-cols-4">
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
        </main> -->
    </AuthenticatedLayout>
</template>
