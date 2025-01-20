<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';


// Define the type for category and employee
interface Category {
  id: number;
  name: string;
}

interface Role {
  id: number;
  name: string;
}
interface Permission {
  id: number;
  name: string;
}

interface Employee {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role_name: string;
  can_login: boolean | null;
  category_id: number | null;
}

const props = defineProps<{
  employee: Employee;
  categories: Category[];
  roles: Role[];
  permissions: Permission[];
  employeeRole: number | null;
  employeePermissions: number[];
}>();


// Reactive form
const form = reactive({
  name: props.employee.name,
  email: props.employee.email,
  phone: props.employee.phone ?? '',
  role_id: props.employeeRole ?? '',
  category_id: props.employee.category_id ?? '',
  password: '',
  can_login: props.employee.can_login ?? false,
  password_confirmation: '',
  selected_permissions: props.employeePermissions ?? [],
});

const { errors } = usePage().props;


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

            <!-- User Information Section -->
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">User Information</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                  <input v-model="form.name" type="text" id="name"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                    required />
                  <span v-if="errors.name" class="text-sm text-red-600">
                    {{ errors.name }}
                  </span>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                  <input v-model="form.email" type="email" id="email"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                    required readonly="true" />
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                  <input v-model="form.phone" type="text" id="phone"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500" />
                </div>
              </div>
            </div>


            <!-- Password Section -->
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Password</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                  <label for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                  <input v-model="form.password" type="password" id="password"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500" />
                  <span v-if="errors.password" class="text-sm text-red-600">
                    {{ errors.password }}
                  </span>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label for="password_confirmation"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                  <input v-model="form.password_confirmation" type="password" id="password_confirmation"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500" />
                </div>
              </div>
            </div>


            <!-- Role and Category Section -->
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Role and Category</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category -->
                <div>
                  <label for="category"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                  <select v-model="form.category_id" id="category"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                    required>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>

                <!-- Role -->
                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Role</label>
                  <select v-model="form.role_id" id="role"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                    required>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                      {{ role.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Permissions Section -->
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Assign Permissions</h2>
              <div
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 border border-gray-300 rounded-md p-4 dark:border-gray-600 dark:bg-gray-700">
                <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                  <input type="checkbox" :value="permission.id" v-model="form.selected_permissions"
                    class="mr-2 text-green-600 focus:ring-green-500 dark:focus:ring-green-400" />
                  <label class="text-sm text-gray-700 dark:text-gray-300">{{ permission.name }}</label>
                </div>
              </div>
            </div>

            <!-- Can Login Toggle Section -->
            <!-- Toggle Can Login Section -->
            <!-- Can Login Toggle -->
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Additional Settings</h2>
              <div class="flex items-center">
                <label for="can_login" class="mr-4 text-sm font-medium text-gray-700 dark:text-gray-300">Can
                  Login</label>
                <button type="button" :class="{ 'bg-green-500': form.can_login, 'bg-gray-300': !form.can_login }"
                  @click="form.can_login = !form.can_login"
                  class="relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none">
                  <span :class="{ 'translate-x-6': form.can_login, 'translate-x-1': !form.can_login }"
                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform">
                  </span>
                </button>
              </div>
            </div>



            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
              <button type="button" @click="$inertia.visit(route('employees.index'))"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">
                Cancel
              </button>
              <button type="submit" @click="submitForm"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-600">
                Update
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
