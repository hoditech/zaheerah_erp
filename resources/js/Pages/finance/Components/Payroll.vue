<template>
    <div>
        <PayrollFormModal v-if="isCreateModalOpen" @submit="handlePayrollSubmit" @close="closePayrollForm" />
        <UpdateModal 
            v-if="isEditModalOpen" 
            @update="handlePayrollUpdate" 
            @close="closeModals" 
            :payroll="editedPayroll!"
            :key="editedPayroll?.id" 
        />
        <ViewPayrollModal 
            v-if="isViewModalOpen" 
            @close="closeModals" 
            :payroll="viewedPayroll!"
        />

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
            <button @click="isCreateModalOpen = true"
                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                Add Payroll
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
            <div class="overflow-y-auto max-h-[400px] overflow-x-auto">
                <table class="w-full border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Employee ID</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Employee</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Base Salary</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Gross Salary</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Total Deduction</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Net Salary</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Payment Method</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Payment Date</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Status</th>
                            <th class="py-3 px-4 text-left sticky top-0 bg-gray-200 dark:bg-gray-700 z-10">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payroll in filteredPayrolls" :key="payroll.id"
                            class="border-t border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="py-3 px-4">{{ payroll.user.employee_id }}</td>
                            <td class="py-3 px-4">{{ payroll.user.name }}</td>
                            <td class="py-3 px-4">₦{{ formatNumber(payroll.base_salary) }}</td>
                            <td class="py-3 px-4">₦{{ formatNumber(payroll.gross_salary) }}</td>
                            <td class="py-3 px-4">₦{{ formatNumber(payroll.total_deduction) }}</td>
                            <td class="py-3 px-4">₦{{ formatNumber(payroll.net_salary) }}</td>
                            <td class="py-3 px-4 capitalize">{{ payroll.payment_method.replace('_', ' ') }}</td>
                            <td class="py-3 px-4">{{ formatDate(payroll.payment_date) }}</td>
                            <td class="py-3 px-4">
                                <span :class="statusClasses(payroll.payment_status)">
                                    {{ payroll.payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 space-x-2">
                                <button @click="openEditModal(payroll)"
                                    class="px-2 py-1 bg-yellow-500 text-sm text-white rounded-md hover:bg-yellow-600 transition">
                                    Edit
                                </button>
                                <button @click="confirmDelete(payroll)"
                                    class="px-2 py-1 bg-red-500 text-sm text-white rounded-md hover:bg-red-600 transition">
                                    Delete
                                </button>
                                <button @click="openViewModal(payroll)"
                                    class="px-2 py-1 bg-blue-500 text-sm z-50 text-white rounded-md hover:bg-blue-600 transition">
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center p-4">
            <p class="text-green-500">Loading...</p>
        </div>

        <!-- Empty state -->
        <div v-if="!loading && payrolls.length === 0" class="text-center p-8 text-gray-500">
            No payroll records found
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import PayrollFormModal from './PayrollFormModal.vue';
import UpdateModal from './UpdateModal.vue';
import ViewPayrollModal from './ViewPayrollModal.vue';
import { fetchPayrolls, deletePayroll } from '@/Pages/Services/payrollService';

// In index.vue (parent)
interface Payroll {
    id: number;
    user: { name: string,employee_id: string };
    base_salary: number;
    gross_salary: number;
    total_deduction: number;
    net_salary: number;
    payment_method: string;
    payment_status: 'paid' | 'pending';
    payment_date: string;
    user_id: number; // Changed from string to number
    overtime_pay: number;
    bonuses: number;
    deductions: number;
    tax: number;
    commission: number;
    location: string;
    designation: string;
    working_days: string;
    bank: string;
    account_number: string;
    transport: number;
    house_rent: number;
    medicals: number;
    others: number;
    income_tax: number;
    cra: number;
    pension: number;
    nhf: number;
    nhis: number;
    salary_advance: number;
}

const payrolls = ref<Payroll[]>([]);
const loading = ref(false);
const payrollFilter = ref('All');
const payrollFilters = ['All', 'Paid', 'Pending'];
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const editedPayroll = ref<Payroll | null>(null);
const viewedPayroll = ref<Payroll | null>(null);

const successMessage = ref('');
const editPayroll = ref<Payroll | null>(null);

const formatNumber = (num: number) => num?.toLocaleString() || '0';
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString();

const statusClasses = (status: string) => ({
    'text-green-500': status === 'paid',
    'text-yellow-500': status === 'pending',
    'text-red-500': status === 'failed'
});

const handlePayrollUpdate = () => {
    isEditModalOpen.value = false;
    successMessage.value = 'Payroll successfully updated!';
    setTimeout(() => successMessage.value = '', 3000);
    loadPayrolls();
};

const closeModals = () => {
  isCreateModalOpen.value = false;
  isEditModalOpen.value = false;
  isViewModalOpen.value = false;
  editedPayroll.value = null;
  viewedPayroll.value = null;
};

const filteredPayrolls = computed(() => {
    if (payrollFilter.value === 'All') return payrolls.value;
    return payrolls.value.filter(payroll => 
        payroll.payment_status === payrollFilter.value.toLowerCase()
    );
});

const handlePayrollSubmit = () => {
    isCreateModalOpen.value = false;
    successMessage.value = 'Payroll successfully submitted!';
    setTimeout(() => successMessage.value = '', 3000);
    loadPayrolls();
};

const loadPayrolls = async () => {
    loading.value = true;
    try {
        payrolls.value = await fetchPayrolls();
    } catch (error) {
        console.error('Error fetching payrolls:', error);
    } finally {
        loading.value = false;
    }
};

const openEditModal = (payroll: Payroll) => {
  editedPayroll.value = { ...payroll }; // Clone the object
  isEditModalOpen.value = true;
};

const openViewModal = (payroll: Payroll) => {
  viewedPayroll.value = { ...payroll }; // Clone the object
  isViewModalOpen.value = true;
};

const confirmDelete = async (payroll: Payroll) => {
    if (confirm('Are you sure you want to delete this payroll record?')) {
        try {
            await deletePayroll(payroll.id);
            payrolls.value = payrolls.value.filter(p => p.id !== payroll.id);
            successMessage.value = 'Payroll deleted successfully!';
            setTimeout(() => successMessage.value = '', 3000);
        } catch (error) {
            console.error('Error deleting payroll:', error);
        }
    }
};

onMounted(loadPayrolls);

const closePayrollForm = () => {
    isCreateModalOpen.value = false;
};
</script>