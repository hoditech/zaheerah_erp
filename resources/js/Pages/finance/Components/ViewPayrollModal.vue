<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-4xl h-[80vh] overflow-y-auto">
            <div class="mt-6 flex justify-end">
                <button @click="printPayroll"
                    class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    Print
                </button>
                <button @click="close"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Close</button>

            </div>
            <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Payroll Details</h2>

            <div class="grid grid-cols-2 gap-6">
                <!-- Employee Information -->
                <div class="border-b pb-4">
                    <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Employee Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                        <div><strong>ID:</strong> {{ payroll.user.employee_id }}</div>
                        <div><strong>Name:</strong> {{ payroll.user.name }}</div>
                        <div><strong>Location:</strong> {{ payroll.location }}</div>
                        <div><strong>Designation:</strong> {{ payroll.designation }}</div>
                        <div><strong>Working Days:</strong> {{ payroll.working_days }}</div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="border-b pb-4">
                    <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Salary Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                        <div><strong>Base Salary:</strong> ₦{{ formatNumber(payroll.base_salary) }}</div>
                        <div><strong>Gross Salary:</strong> ₦{{ formatNumber(payroll.gross_salary) }}</div>
                        <div><strong>Net Salary:</strong> ₦{{ formatNumber(payroll.net_salary) }}</div>
                        <div><strong>Total Deduction:</strong> ₦{{ formatNumber(payroll.total_deduction) }}</div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="border-b pb-4">
                    <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Payment Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                        <div><strong>Method:</strong> {{ payroll.payment_method.replace('_', ' ') }}</div>
                        <div><strong>Date:</strong> {{ formatDate(payroll.payment_date) }}</div>
                        <div><strong>Status:</strong> <span :class="statusClasses(payroll.payment_status)">{{
                            payroll.payment_status }}</span></div>
                    </div>
                </div>

                <!-- Earnings & Deductions -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Earnings</h3>
                        <div class="text-gray-700 dark:text-gray-300"><strong>Overtime Pay:</strong> ₦{{
                            formatNumber(payroll.overtime_pay) }}</div>
                        <div><strong>Bonuses:</strong> ₦{{ formatNumber(payroll.bonuses) }}</div>
                        <div><strong>Commission:</strong> ₦{{ formatNumber(payroll.commission) }}</div>
                        <div><strong>Allowances:</strong></div>
                        <ul class="ml-4 list-disc text-gray-700 dark:text-gray-300">
                            <li>Transport: ₦{{ formatNumber(payroll.transport) }}</li>
                            <li>House Rent: ₦{{ formatNumber(payroll.house_rent) }}</li>
                            <li>Medical: ₦{{ formatNumber(payroll.medicals) }}</li>
                            <li>Others: ₦{{ formatNumber(payroll.others) }}</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Deductions</h3>
                        <div class="text-gray-700 dark:text-gray-300"><strong>Tax:</strong> ₦{{
                            formatNumber(payroll.tax) }}</div>
                        <div><strong>Income Tax:</strong> ₦{{ formatNumber(payroll.income_tax) }}</div>
                        <div><strong>CRA:</strong> ₦{{ formatNumber(payroll.cra) }}</div>
                        <div><strong>Pension:</strong> ₦{{ formatNumber(payroll.pension) }}</div>
                        <div><strong>NHF:</strong> ₦{{ formatNumber(payroll.nhf) }}</div>
                        <div><strong>NHIS:</strong> ₦{{ formatNumber(payroll.nhis) }}</div>
                        <div><strong>Salary Advance:</strong> ₦{{ formatNumber(payroll.salary_advance) }}</div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div>
                    <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Bank Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                        <div><strong>Bank:</strong> {{ payroll.bank }}</div>
                        <div><strong>Account Number:</strong> {{ payroll.account_number }}</div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

interface Payroll {
    id: number;
    user: { name: string, employee_id: string };
    base_salary: number;
    gross_salary: number;
    total_deduction: number;
    net_salary: number;
    payment_method: string;
    payment_status: 'paid' | 'pending' | 'failed';
    payment_date: string;
    overtime_pay: number;
    bonuses: number;
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
const props = defineProps<{ payroll: Payroll }>();
const emit = defineEmits(['close']);

const formatNumber = (num: number) => num?.toLocaleString() || '0';
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString();
const statusClasses = (status: string) => ({
    'text-green-500': status === 'paid',
    'text-yellow-500': status === 'pending',
    'text-red-500': status === 'failed'
});
const close = () => emit('close');

const printPayroll = () => {
    const content = `
    <html>
        <head>
            <title>Payroll Slip - ${props.payroll.user.name}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .payroll-slip { max-width: 800px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 25px; }
                .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .border-bottom { border-bottom: 1px solid #ddd; padding-bottom: 15px; }
                .text-bold { font-weight: 600; }
                .status-paid { color: #22c55e; }
                .status-pending { color: #eab308; }
                .status-failed { color: #ef4444; }
                .logo { max-width: 150px; margin: 0 auto 15px; }

            </style>
        </head>
        <body>
            <div class="payroll-slip">
                <div class="header">
                <img src="/images/logo2.png" alt="Company Logo" class="logo">
                    <h1>${props.payroll.user.name}'s Payroll Slip</h1>
                    <p>Generated: ${new Date().toLocaleDateString()}</p>
                </div>

                <div class="grid-2">
                    <!-- Employee Info -->
                    <div class="border-bottom">
                        <h3>Employee Information</h3>
                        <p>ID: ${props.payroll.user.employee_id}</p>
                        <p>Name: ${props.payroll.user.name}</p>
                        <p>Location: ${props.payroll.location}</p>
                        <p>Designation: ${props.payroll.designation}</p>
                    </div>

                    <!-- Payment Info -->
                    <div class="border-bottom">
                        <h3>Payment Details</h3>
                        <p>Status: <span class="status-${props.payroll.payment_status}">
                            ${props.payroll.payment_status}
                        </span></p>
                        <p>Method: ${props.payroll.payment_method.replace('_', ' ')}</p>
                        <p>Date: ${formatDate(props.payroll.payment_date)}</p>
                    </div>
                </div>

                <!-- Full Salary Breakdown -->
                <div class="grid-2" style="margin-top: 20px;">
                    <div>
                        <h3>Earnings</h3>
                        <p>Base Salary: ₦${formatNumber(props.payroll.base_salary)}</p>
                        <p>Gross Salary: ₦${formatNumber(props.payroll.gross_salary)}</p>
                        <p>Overtime: ₦${formatNumber(props.payroll.overtime_pay)}</p>
                        <p>Bonuses: ₦${formatNumber(props.payroll.bonuses)}</p>
                        <p>Commission: ₦${formatNumber(props.payroll.commission)}</p>
                    </div>

                    <div>
                        <h3>Deductions</h3>
                        <p>Total Deductions: ₦${formatNumber(props.payroll.total_deduction)}</p>
                        <p>Tax: ₦${formatNumber(props.payroll.tax)}</p>
                        <p>Pension: ₦${formatNumber(props.payroll.pension)}</p>
                        <p>NHIS: ₦${formatNumber(props.payroll.nhis)}</p>
                    </div>
                </div>

                <div class="text-bold" style="margin-top: 25px; font-size: 1.2em;">
                    Net Salary: ₦${formatNumber(props.payroll.net_salary)}
                </div>

                <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
                    <p>Bank: ${props.payroll.bank}</p>
                    <p>Account: ${props.payroll.account_number}</p>
                </div>
            </div>
        </body>
    </html>
    `;

    const printWindow = window.open('', '_blank');
    printWindow?.document.write(content);
    printWindow?.document.close();
    printWindow?.print();
};
</script>
