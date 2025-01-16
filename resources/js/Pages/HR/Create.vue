<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface Category {
  id: number;
  name: string;
}

interface Role {
  id: number;
  name: string;
}

defineProps({
    categories: {
        type: Array as () => Category[],
        required: true,
    },
    roles: {
        type: Array as () => Role[],
        required: true,
    },
});

const form = reactive({
    name: '',
    email: '',
    phone: '',
    can_login: false,
    role_name: '',
    category_id: '',
    password: '',
    password_confirmation: '',
});

const submitForm = () => {
    router.post(route('employees.store'), form);
};
</script>

<template>
  <Head title="Add Employee" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Add New Employee
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

            <!-- Category -->
            <div class="mb-6">
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
              <select
                v-model="form.category_id"
                id="category"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              >
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Role -->
            <div class="mb-6">
              <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Role</label>
              <select
                v-model="form.role_name"
                id="role"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              >
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
            </div>

            <!-- Password -->
            <div class="mb-6">
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
              <input
                v-model="form.password"
                type="password"
                id="password"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              />
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                id="password_confirmation"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                required
              />
            </div>

            <!-- Buttons -->
            <div class="flex space-x-4">
              <button
                type="submit"
                class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-md"
              >
                Save
              </button>
              <button
                @click="$inertia.visit(route('employees.index'))"
                type="button"
                class="px-6 py-2 text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 rounded-md"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
