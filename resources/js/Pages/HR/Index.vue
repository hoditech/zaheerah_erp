<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, PropType, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
// @ts-ignore
import { is, can } from 'laravel-permission-to-vuejs';
import * as XLSX from 'xlsx';

const props = defineProps({
  employees: {
    type: Array as PropType<any[]>,
    required: false,
    default: () => [],
  }
});

interface Action {
  label: string;
  onClick: (employee: any) => void;
  class: string;
}

const formattedEmployees = computed(() => 
  props.employees.map((employee) => ({
    ...employee,
    roles: employee.roles.map((role: { name: string }) => role.name).join(', '),
  }))
);

const columns = [
  { label: "ID", key: "employee_id" },
  { label: "Name", key: "name" },
  { label: "Email", key: "email" },
  { label: "Phone", key: "phone" },
  { label: "Roles", key: "roles" },
  { label: "Category", key: "category.name" },
];

const searchQuery = ref("");

const deleteEmployee = (employee: any) => {
  if (confirm(`Are you sure you want to delete ${employee.name}?`)) {
    router.delete(route('employees.destroy', employee.id), {
      onSuccess: () => {
        console.log(`${employee.name} has been deleted successfully.`);
      },
    });
    employee.value = employee.value.filter((e: { id: any; }) => e.id !== employee.id);
  }
};

const editEmployee = (employee: any) => {
  router.visit(route('employees.edit', employee.id));
};

// Excel Export Functionality
const exportToExcel = () => {
  // Create worksheet data with proper headers
  const worksheetData = formattedEmployees.value.map(employee => 
    columns.reduce((acc: any, col) => {
      acc[col.label] = getNestedValue(employee, col.key);
      return acc;
    }, {})
  );

  // Create worksheet and workbook
  const worksheet = XLSX.utils.json_to_sheet(worksheetData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Employees");

  // Export the file
  XLSX.writeFile(workbook, 'employees.xlsx', { compression: true });
};

const getNestedValue = (obj: any, path: string) => {
  return path.split('.').reduce((acc, part) => (acc && acc[part] != null ? acc[part] : ''), obj);
};

let actions: Action[] = [];
if (is('Super Admin')) {
  actions = [
    {
      label: "Edit",
      onClick: editEmployee,
      class: "text-blue-600 hover:text-blue-900",
    },
    {
      label: "Delete",
      onClick: deleteEmployee,
      class: "text-red-600 hover:text-red-900",
    },
  ];
}
</script>

<template>
  <Head title="Employee Management" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Employee Management</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ $page.props.flash.success }}
        </div>
        
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
          <div class="relative w-1/3">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search employees..."
              class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            />
          </div>
          <div class="flex gap-2">
            <button
              v-if="is('Super Admin | HR')"
              @click="exportToExcel"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
            >
              Export to Excel
            </button>
            <button
              v-if="is('Super Admin | HR')"
              @click="$inertia.visit(route('employees.create'))"
              class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
            >
              Add Employee
            </button>
          </div>
        </div>

        <!-- DataTable Component -->
        <DataTable
          :columns="columns"
          :data="formattedEmployees"
          :searchQuery="searchQuery"
          :actions="actions"
          :pageSize="5"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>