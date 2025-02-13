<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from './Partials/ProductForm.vue';
import { Head } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
}

interface Supplier {
    id: number;
    name: string;
}

defineProps({
    categories: {
        type: Array as () => Category[],
        required: true,
    },
    suppliers: {
        type: Array as () => Supplier[],
        required: true,
    },
});



</script>

<template>

    <Head title="Add Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Add Poduct
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                  
                    <ProductForm :categories="categories" :suppliers="suppliers"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
