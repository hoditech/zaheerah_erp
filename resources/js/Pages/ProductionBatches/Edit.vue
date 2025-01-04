<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

// Define the type for category
interface Category {
  id: number;
  name: string;
}

// Props
const props = defineProps<{
  production: {
    id: number;
    quantity: number;
    material_cost: number;
    labor_cost: number;
    overhead_cost: number;
    batch_status: number;
    description: string;
    product_id: number;
  };
  categories: Category[];
}>();



// Reactive form
const form = reactive({
  quantity: props.production.quantity,
  material_cost: props.production.material_cost,
  labor_cost: props.production.labor_cost,
  overhead_cost: props.production.labor_cost,
  batch_status: props.production.batch_status,
  description: props.production.description,
  product_id: props.production.product_id,
});

// Submit form method
const updateProductionBatch = () => {
  router.put(route('productions.update', props.production.id), form);
};
</script>

<template>

  <Head title="Edit Production Batch" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Edit Production Batch
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
          <form @submit.prevent="updateProductionBatch">


            <!-- Batch Details -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-black dark:text-gray-200 py-4">Batch Details</h3>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">


                <div>
                  <label for="product"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                  <select v-model="form.product_id" id="product"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                    required>
                    <option value="" disabled>Select a Product</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <label for="quantity"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                  <input v-model="form.quantity" type="number" id="quantity"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                    required />
                </div>
              </div>
            </div>

            <!-- Costs -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">Costs</h3>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                  <label for="material_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Material
                    Cost</label>
                  <input v-model="form.material_cost" type="number" id="material_cost"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                </div>
                <div>
                  <label for="labor_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Labor
                    Cost</label>
                  <input v-model="form.labor_cost" type="number" id="labor_cost"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                </div>
                <div>
                  <label for="overhead_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Overhead
                    Cost</label>
                  <input v-model="form.overhead_cost" type="number" id="overhead_cost"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500" />
                </div>
              </div>

            </div>

            <!-- Additional Information -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-black dark:text-gray-200 py-4">Additional Information</h3>

              <div class="mb-6">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select v-model="form.batch_status" id="status"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-500 dark:focus:border-indigo-500"
                  required>
                  <option value="" disabled>Select Status</option>
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </div>

              <div class="mb-8">
                <label for="description"
                  class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea v-model="form.description" id="description"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-green-500 dark:focus:border-green-500"
                  rows="3"></textarea>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
              <button type="button" @click="$inertia.visit(route('production-batches.index'))"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">
                Cancel
              </button>
              <button type="submit" @click="updateProductionBatch"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-600">
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
