<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';


const props = defineProps({
  employees: {
    type: Array  as PropType<any[]>,
      required: false,
      default: () => [],
  }
});





const columns = [
  { label: "Name", key: "name" },
  { label: "Email", key: "email" },
  { label: "Phone", key: "phone" },
  { label: "Role", key: "role" },
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
  // Redirect to edit page using Inertia
  router.visit(route('employees.edit', employee.id));
};

const actions = [
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
        <!-- Header with Search Bar and Add Employee Button -->
        <div class="flex justify-between items-center mb-6">
          <div class="relative w-1/3">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search employees..."
              class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            />
          </div>
          <button
            @click="$inertia.visit(route('employees.create'))"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
          >
            Add Employee
          </button>
        </div>

        <!-- DataTable Component -->
        <DataTable
          :columns="columns"
          :data="employees"
          :searchQuery="searchQuery"
          :actions="actions"
          :pageSize="5"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
