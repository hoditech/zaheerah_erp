<template>
    <div class=" fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-semibold mb-4 max-w-6xl">Edit Payroll</h3>
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Employee Dropdown -->
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Employee</label>
                        <select v-model="form.user_id" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" :disabled="isLoading || true">
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

                    <!-- Salary Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Base Salary</label>
                        <input v-model="form.base_salary" type="number" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                    </div>

                    <!-- Allowances -->
                    <div class="md:col-span-2 lg:col-span-3 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Allowances</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="field in allowanceFields" :key="field.key">
                                <label class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
                                <input v-model="form[field.key as keyof FormData]" :type="field.type"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="md:col-span-2 lg:col-span-3 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Deductions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="field in deductionFields" :key="field.key">
                                <label class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
                                <input v-model="form[field.key as keyof FormData]" :type="field.type"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="md:col-span-2 lg:col-span-3 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Payment Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select  v-model="form.payment_method" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                                    <option v-for="option in paymentMethodOptions" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                                <select v-model="form.payment_status" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                                    <option v-for="option in paymentStatusOptions" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                                <input v-model="form.payment_date" type="date"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="md:col-span-2 lg:col-span-3 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Bank Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                <input v-model="form.bank" type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Account Number</label>
                                <input v-model="form.account_number" type="number"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-4 mt-6">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300"
                        :disabled="isLoading || isSubmitting">
                        {{ isSubmitting ? 'Updating...' : 'Update' }}
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
import { onMounted, ref, watch } from 'vue';
import { fetchUsers, updatePayroll } from '@/Pages/Services/payrollService';

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

// In UpdateModal.vue (child)
interface FormData {
    id: number;
    user_id: number;
    base_salary: number;
    overtime_pay: number;
    bonuses: number;
    deductions: number;
    tax: number;
    commission: number;
    payment_method: string;
    payment_status: string;
    payment_date: string;
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

const emit = defineEmits(['update', 'close']);
const props = defineProps({
    payroll: {
        type: Object as () => FormData,
        required: true
    }
});

const users = ref<User[]>([]);
const isLoading = ref<boolean>(true);
const isSubmitting = ref<boolean>(false);
const validationErrors = ref<Record<string, string[]>>({});

const defaultForm: FormData = {
    id: props.payroll.id,
    user_id: props.payroll.user_id,
    base_salary: 0,
    overtime_pay: 0,
    bonuses: 0,
    deductions: 0,
    tax: 0,
    commission: 0,
    payment_method: 'bank_transfer',
    payment_status: 'pending',
    payment_date: '',
    location: '',
    designation: '',
    working_days: '',
    bank: '',
    account_number: '',
    transport: 0,
    house_rent: 0,
    medicals: 0,
    others: 0,
    income_tax: 0,
    cra: 0,
    pension: 0,
    nhf: 0,
    nhis: 0,
    salary_advance: 0,
};

const form = ref<FormData>({ ...defaultForm });
// Add this watch after initializing the form ref
watch(() => props.payroll, (newPayroll) => {
  if (newPayroll) {
    // Reset form with fresh data
    form.value = { ...defaultForm, ...newPayroll };
  }
}, { immediate: true, deep: true });

const allowanceFields = [
    { key: 'transport', label: 'Transport', type: 'number' },
    { key: 'house_rent', label: 'House Rent', type: 'number' },
    { key: 'medicals', label: 'Medicals', type: 'number' },
    { key: 'others', label: 'Others', type: 'number' },
];

const deductionFields = [
    { key: 'income_tax', label: 'Income Tax', type: 'number' },
    { key: 'cra', label: 'CRA', type: 'number' },
    { key: 'pension', label: 'Pension', type: 'number' },
    { key: 'nhf', label: 'NHF', type: 'number' },
    { key: 'nhis', label: 'NHIS', type: 'number' },
    { key: 'salary_advance', label: 'Salary Advance', type: 'number' },
];

const paymentMethodOptions = [
    { label: 'Bank Transfer', value: 'bank_transfer' },
    { label: 'Cash', value: 'cash' },
    { label: 'Mobile Money', value: 'mobile_money' }
];

const paymentStatusOptions = [
    { label: 'Paid', value: 'paid' },
    { label: 'Pending', value: 'pending' }
];

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
    isSubmitting.value = true;
    validationErrors.value = {};

    // Convert numeric fields to numbers
    const payload = {
        ...form.value,
        base_salary: Number(form.value.base_salary),
        transport: Number(form.value.transport),
        house_rent: Number(form.value.house_rent),
        medicals: Number(form.value.medicals),
        others: Number(form.value.others),
        income_tax: Number(form.value.income_tax),
        cra: Number(form.value.cra),
        pension: Number(form.value.pension),
        nhf: Number(form.value.nhf),
        nhis: Number(form.value.nhis),
        salary_advance: Number(form.value.salary_advance),
    };

    try {
        await updatePayroll(form.value.id, payload);
        emit('update'); // Only emit 'update'
    } catch (error: any) {
        // Handle error
    } finally {
        isSubmitting.value = false;
    }
};

const cancelForm = () => {
    emit('close');
};
</script>