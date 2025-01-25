<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
// @ts-ignore
import { can } from 'laravel-permission-to-vuejs'

defineProps({
    purchaseOrders: {
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
  { label: "Order Number", key: "order_number" },
  { label: "Category", key: "category.name" },
  { label: "Total Cost", key: "total_cost" },
  { label: "Supplie", key: "supplier.name" },
  { label: "Delivery Status", key: "delivery_status" },
];

const searchQuery = ref("");

const deleteOrder = (order: any) => {
  if (confirm(`Are you sure you want to delete the order: ${order.name}?`)) {
    router.delete(route('purchase-orders.destroy', order.id), {
      onSuccess: () => console.log(`${order.name} has been deleted successfully.`),
    });
  }
};

const editpurchaseOder = (order: any) => {
  router.visit(route('purchase-orders.edit', order.id));
};

let actions: Action[] = [];
if (can('manage_suppliers')) {
 actions = [
  {
    label: "Edit",
    onClick: editpurchaseOder,
    class: "text-blue-600 hover:text-blue-900",
  },
  {
    label: "Delete",
    onClick: deleteOrder,
    class: "text-red-600 hover:text-red-900",
  },
];}
</script>

<template>
  <Head title="Purchase Order" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">Purchase Orders</h2>
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
              placeholder="Search Order..."
              class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            />
          </div>
          <button 
            @click="$inertia.visit(route('purchase-orders.create'))"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
          >
            Add New
          </button>
        </div>

        <DataTable
          :columns="columns"
          :data="purchaseOrders"
          :searchQuery="searchQuery"
          :actions="actions"
          :pageSize="10"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
