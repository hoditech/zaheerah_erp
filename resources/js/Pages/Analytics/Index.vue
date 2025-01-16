<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

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
        
          </div>
          <button
            @click="$inertia.visit(route('suppliers.create'))"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-md transition duration-300 ease-in-out"
          >
            Add New Supply
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
