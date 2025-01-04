<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

// Define the type for category and employee
interface Category {
  id: number;
  name: string;
}

interface Employee {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: string;
  category_id: number | null;
}

// Props
const props = defineProps<{
  employee: Employee;
  categories: Category[];
}>();

// Reactive form
const form = reactive({
  name: props.employee.name,
  email: props.employee.email,
  phone: props.employee.phone ?? '',
  role: props.employee.role,
  category_id: props.employee.category_id ?? '',
});

// Submit form method
const submitForm = () => {
  router.put(route('employees.update', props.employee.id), form);
};
</script>

<template>
  <Head title="Edit Employee" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Edit Employee
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
          <form @submit.prevent="submitForm">
            <!-- Name -->
            <div class="mb-6">
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              />
            </div>

            <!-- Email -->
            <div class="mb-6">
              <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <input
                v-model="form.email"
                type="email"
                id="email"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              />
            </div>

            <!-- Phone -->
            <div class="mb-6">
              <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                id="phone"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
              />
            </div>

            <!-- Role -->
            <div class="mb-6">
              <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
              <input
                v-model="form.role"
                type="text"
                id="role"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              />
            </div>

            <!-- Category -->
            <div class="mb-6">
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
              <select
                v-model="form.category_id"
                id="category"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              >
                <option v-for="category in props.categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
              <button
                type="button"
                @click="$inertia.visit(route('employees.index'))"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
              <button
                type="submit"
                @click="submitForm"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600"
              >
                Update
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
