<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
// @ts-ignore
import { is } from 'laravel-permission-to-vuejs'

defineProps({
  supplies: {
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
  { label: "Name", key: "name" },
  { label: "Contact Email", key: "contact_email" },
  { label: "Contact Phone", key: "contact_phone" },
  { label: "Address", key: "address" },
  { label: "Category", key: "category.name" },
  { label: "Status", key: "status" },
];

const searchQuery = ref("");

const deleteSupply = (supply: any) => {
  if (confirm(`Are you sure you want to delete the supply: ${supply.name}?`)) {
    router.delete(route('suppliers.destroy', supply.id), {
      onSuccess: () => console.log(`${supply.name} has been deleted successfully.`),
    });
  }
};

const editSupply = (supply: any) => {
  router.visit(route('suppliers.edit', supply.id));
};

let actions: Action[] = [];
if (is('Technical Manager')) {
 actions = [
  {
    label: "Edit",
    onClick: editSupply,
    class: "text-blue-600 hover:text-blue-900",
  },
  {
    label: "Delete",
    onClick: deleteSupply,
    class: "text-red-600 hover:text-red-900",
  },
];
}
</script>

<template>
  <Head title="Supplies" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Suppliers</h2>
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
              placeholder="Search supplies..."
              class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            />
          </div>
          <button v-if="is('Technical Manager | HR')"
            @click="$inertia.visit(route('suppliers.create'))"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
          >
            Add New Supply
          </button>
        </div>

        <DataTable
          :columns="columns"
          :data="supplies"
          :searchQuery="searchQuery"
          :actions="actions"
          :pageSize="5"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
