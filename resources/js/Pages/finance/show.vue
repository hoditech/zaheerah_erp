<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from './Components/Sidebar.vue';
import Dashboard from './Components/Dashboard.vue';
import Payroll from './Components/Payroll.vue';
import Reports from './Components/Reports.vue';

const props = defineProps<{
    totalRevenue: string;
    totalCost: string;
    totalPayrollExpense: string;
    totalProfit: string;
    totalStock: string;
    categories?: {
        id: number; 
        name: string;
        revenue: number;
        cost: number;
        profit: number;
        inventory: number;
        totalStock: number;
        stockValue: number; 
    }[];
    payrolls?: {
        id: number;
        user: { name: string };
        net_salary: number;
        status: "Paid" | "Pending";
    }[];
}>();


const activeTab = ref('Dashboard');
const tabs = ['Dashboard', 'Payroll', 'Reports'];

const switchTab = (tab: string) => {
    activeTab.value = tab;
};
</script>

<template>

    <Head title="Financial Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Financial Management</h2>
        </template>

        <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
            <Sidebar :activeTab="activeTab" :tabs="tabs" @switch-tab="switchTab" />
            <main class="flex-1 p-4">
                <Dashboard v-if="activeTab === 'Dashboard'" :totalRevenue="totalRevenue" :totalCost="totalCost"
                    :totalProfit="totalProfit" :categories="categories" />
                <Payroll v-if="activeTab === 'Payroll'" :payrolls="payrolls" />
                <Reports v-if="activeTab === 'Reports'" :categories="categories" />
            </main>
        </div>
    </AuthenticatedLayout>
</template>