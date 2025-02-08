<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { fetchUsers, submitPayroll } from '@/Pages/Services/payrollService';

const emit = defineEmits(['submit', 'close']);

const users = ref<{ id: string; name: string }[]>([]);


const form = ref({
    userId: '',
    baseSalary: '',
    overtimePay: '',
    bonuses: '',
    deductions: '',
    tax: '',
    commission: '',
    netSalary: '',
    paymentMethod: '',
    paymentStatus: 'Pending',
    paymentDate: '',
});

// Fetch users from payrolls.index route
const loadUsers = async () => {
    users.value = await fetchUsers();
};



// Fetch users when component mounts
onMounted(loadUsers);

const submitForm = async () => {
    emit('submit');
    resetForm();
};

const cancelForm = () => {
    resetForm();
    emit('close');
};

const resetForm = () => {
    form.value = {
        userId: '',
        baseSalary: '',
        overtimePay: '',
        bonuses: '',
        deductions: '',
        tax: '',
        commission: '',
        netSalary: '',
        paymentMethod: '',
        paymentStatus: 'Pending',
        paymentDate: '',
    };
};
</script>

<template>
    <div class="p-6">
        <h3 class="text-xl font-semibold mb-4">Add Payroll</h3>
        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Employee</label>
                    <select v-model="form.userId" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="" disabled>Select a Employee</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Base Salary</label>
                    <input v-model="form.baseSalary" type="number" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Overtime Pay</label>
                    <input v-model="form.overtimePay" type="number"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bonuses</label>
                    <input v-model="form.bonuses" type="number"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deductions</label>
                    <input v-model="form.deductions" type="number"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tax</label>
                    <input v-model="form.tax" type="number"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Commission</label>
                    <input v-model="form.commission" type="number"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Net Salary</label>
                    <input v-model="form.netSalary" type="number" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <input v-model="form.paymentMethod" type="text" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                    <select v-model="form.paymentStatus" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                    <input v-model="form.paymentDate" type="date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" />
                </div>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Submit
                </button>
                <button type="button" @click="cancelForm"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>
