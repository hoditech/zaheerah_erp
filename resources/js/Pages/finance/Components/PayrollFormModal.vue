<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-1/3 p-6">
            <h3 class="text-xl font-semibold mb-4">Add Payroll</h3>
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Employee Dropdown with Loading Indicator -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee</label>
                        <select v-model="form.user_id" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"
                            aria-label="Select an Employee" :disabled="isLoading">
                            <option value="" disabled>Select an Employee</option>
                            <option v-if="isLoading" disabled>Loading employees...</option>
                            <option v-else v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                        <span v-if="validationErrors.user_id" class="text-red-500 text-sm">
                            {{ validationErrors.user_id[0] }}
                        </span>
                    </div>

                    <!-- Dynamic Fields -->
                    <div v-for="(value, key) in salaryFields" :key="key">
                        <label class="block text-sm font-medium text-gray-700">{{ value.label }}</label>
                        <template v-if="key === 'payment_method' || key === 'payment_status'">
                            <select v-model="form[key]" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                                <option v-for="option in value.options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </template>
                        <template v-else>
                            <input v-model="form[key as keyof FormData]" :type="value.type" :required="value.required"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                        </template>
                        <span v-if="validationErrors[key]" class="text-red-500 text-sm">
                            {{ validationErrors[key][0] }}
                        </span>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-4 mt-6">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300"
                        :disabled="isLoading || isSubmitting">
                        {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                    </button>

                    <button type="button" @click="cancelForm"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-300"
                        :disabled="isSubmitting">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { fetchUsers, submitPayroll } from '@/Pages/Services/payrollService';

interface User {
    id: string;
    name: string;
}

interface SalaryField {
    label: string;
    type: string;
    required: boolean;
    options?: { label: string; value: string }[];
}

interface FormData {
    user_id: string;
    base_salary: string;
    overtime_pay: string;
    bonuses: string;
    deductions: string;
    tax: string;
    commission: string;
    payment_method: string;
    payment_status: string;
    payment_date: string;
}

const emit = defineEmits(['submit', 'close']);

const users = ref<User[]>([]);
const isLoading = ref<boolean>(true); // Loading state for fetching users
const isSubmitting = ref<boolean>(false); // Loading state for form submission
const validationErrors = ref<Record<string, string[]>>({});

const defaultForm: FormData = {
    user_id: '', base_salary: '', overtime_pay: '', bonuses: '', deductions: '', tax: '',
    commission: '', payment_method: 'Bank Transfer', payment_status: 'Pending', payment_date: '',
};

const form = ref<FormData>({ ...defaultForm });

const salaryFields: Record<string, SalaryField> = {
    base_salary: { label: 'Base Salary', type: 'number', required: true },
    overtime_pay: { label: 'Overtime Pay', type: 'number', required: false },
    bonuses: { label: 'Bonuses', type: 'number', required: false },
    deductions: { label: 'Deductions', type: 'number', required: false },
    tax: { label: 'Tax', type: 'number', required: false },
    commission: { label: 'Commission', type: 'number', required: false },
    payment_method: {
        label: 'Payment Method',
        type: 'select',
        required: true,
        options: [
            { label: 'Bank Transfer', value: 'bank_transfer' },
            { label: 'Cash', value: 'cash' },
            { label: 'Mobile Money', value: 'mobile_money' }
        ]
    },
    payment_status: {
        label: 'Payment Status',
        type: 'select',
        required: true,
        options: [
            { label: 'Paid', value: 'paid' },
            { label: 'Pending', value: 'pending' }
        ]
    },
    payment_date: { label: 'Payment Date', type: 'date', required: false },
};


const loadUsers = async () => {
    isLoading.value = true;
    try {
        users.value = await fetchUsers();
    } catch (error) {
        console.error('Failed to fetch users:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(loadUsers);

const submitForm = async () => {


    isSubmitting.value = true; // Start submission loading
    validationErrors.value = {}; // Reset validation errors
    try {
        await submitPayroll(form.value); // Simulating API call
        emit('submit');
        resetForm();
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            // Store validation errors
            validationErrors.value = error.response.data.errors;
        } else {
            console.error('Failed to submit payroll:', error);
        }
    } finally {
        isSubmitting.value = false; // Stop submission loading
    }
};

const cancelForm = () => {
    resetForm();
    emit('close');
};

const resetForm = () => {
    form.value = { ...defaultForm };
};
</script>
