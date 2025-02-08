<template>
    <div>
        <PayrollFormModal v-if="isPayrollFormOpen" @submit="handlePayrollSubmit" @close="closePayrollForm" />

        <!-- Success message -->
        <div v-if="successMessage" class="bg-green-500 text-white p-4 rounded-lg mb-4">
            {{ successMessage }}
        </div>

        <h3 class="text-2xl font-semibold mb-4">Payroll Tracking</h3>
        <div class="mb-6 flex space-x-4">
            <button v-for="filter in payrollFilters" :key="filter" @click="payrollFilter = filter"
                :class="{ 'bg-green-500 text-white': payrollFilter === filter }"
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-green-500 hover:text-white transition">
                {{ filter }}
            </button>
            <button @click="isPayrollFormOpen = true"
                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                Add Payroll
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
            <div class="overflow-y-auto max-h-[400px]"> <!-- Container with fixed height and overflow -->
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Employee</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Base Salary</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Overtime Pay</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Bonuses</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Net Salary</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Status</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payroll in filteredPayrolls" :key="payroll.id"
                            class="border-t border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="py-3 px-4">{{ payroll.user.name }}</td>
                            <td class="py-3 px-4">₦{{ payroll.base_salary.toLocaleString() }}</td>
                            <td class="py-3 px-4">₦{{ payroll.overtime_pay.toLocaleString() }}</td>
                            <td class="py-3 px-4">₦{{ payroll.bonuses.toLocaleString() }}</td>
                            <td class="py-3 px-4">₦{{ payroll.net_salary.toLocaleString() }}</td>
                            <td class="py-3 px-4">
                                <span
                                    :class="{ 'text-green-500': payroll.payment_status === 'paid', 'text-yellow-500': payroll.payment_status === 'pending' }">
                                    {{ payroll.payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <!-- Add an "Update" button for each payroll -->
                                <button @click=""
                                    class="px-2 py-1 bg-yellow-500 text-sm text-white rounded-md hover:bg-yellow-600 transition">
                                    Update
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Loading Spinner -->
        <!-- Loading Text -->
        <div v-if="loading" class="flex justify-center items-center p-4">
            <p class="text-green-500">Loading...</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import PayrollFormModal from './PayrollFormModal.vue';
import { fetchPayrols, submitPayroll } from '@/Pages/Services/payrollService';

const payrolls = ref([] as Array<{
    id: number;
    user: { name: string };
    base_salary: number;
    overtime_pay: number;
    bonuses: number;
    net_salary: number;
    payment_status: 'paid' | 'pending';
}>);

const loading = ref(false); // Loading state to control the spinner
const payrollFilter = ref('All');
const payrollFilters = ['All', 'Paid', 'Pending'];
const isPayrollFormOpen = ref(false);
const successMessage = ref(''); // Variable to store success message

const filteredPayrolls = computed(() => {
    if (payrollFilter.value === 'All') return payrolls.value;
    return payrolls.value.filter(payroll => payroll.payment_status === payrollFilter.value.toLowerCase());
});

const handlePayrollSubmit = () => {
    isPayrollFormOpen.value = false;

    successMessage.value = 'Payroll successfully submitted!'; // Show success message
    setTimeout(() => {
        successMessage.value = ''; // Hide success message after 3 seconds
    }, 3000);

    loadPayrolls();
};

const loadPayrolls = async () => {
    loading.value = true; // Start loading spinner

    try {
        const payrollsData = await fetchPayrols();
        payrolls.value = payrollsData;
        console.log(payrollsData);
    } catch (error) {
        console.error('Error fetching payrolls:', error);
    } finally {
        loading.value = false; // Stop loading spinner once data is fetched
    }
};

onMounted(loadPayrolls);

const closePayrollForm = () => {
    isPayrollFormOpen.value = false;
};
</script>
