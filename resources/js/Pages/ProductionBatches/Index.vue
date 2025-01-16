<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
// @ts-ignore
import { is } from 'laravel-permission-to-vuejs'

// Explicitly type the batches prop as an array
defineProps({
  batches: {
    type: Array as PropType<any[]>,
    required: false,
    default: () => [], 
  },
});


interface Action {
  label: string;
  onClick: (employee: any) => void;
  class: string;
}




const columns = [
  { label: "Batch Number", key: "batch_number" },
  { label: "Product", key: "product.name" },
  { label: "Quantity", key: "quantity" },
  { label: "Total Cost", key: "total_cost" },
];

const searchQuery = ref("");

const deleteBatch = (batch: any) => {
  if (confirm(`Are you sure you want to delete the batch: ${batch.name}?`)) {
    router.delete(route('productions.destroy', batch.id), {
      onSuccess: () => console.log(`${batch.name} has been deleted successfully.`),
    });
  }
};

const editBatch = (batch: any) => {
  router.visit(route('productions.edit', batch.id));
};
let actions: Action[] = [];
if (is('Technical Manager')) {
 actions = [
  {
    label: "Edit",
    onClick: editBatch,
    class: "text-blue-600 hover:text-blue-900",
  },
  {
    label: "Delete",
    onClick: deleteBatch,
    class: "text-red-600 hover:text-red-900",
  },
];}
</script>

<template>
  <Head title="Production Batches" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Production Batches</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ $page.props.flash.success }}
        </div>
        <div class="flex justify-between items-center mb-6">
          <div class="relative w-1/3">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search batches..."
              class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            />
          </div>
          <button
            @click="$inertia.visit(route('productions.create'))"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
          >
            Add New Batch
          </button>
        </div>

        <DataTable
          :columns="columns"
          :data="batches"
          :searchQuery="searchQuery"
          :actions="actions"
          :pageSize="10"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

