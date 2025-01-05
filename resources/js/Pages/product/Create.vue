<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from './Partials/ProductForm.vue';
import { Head } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
}

defineProps({
    categories: {
        type: Array as () => Category[],
        required: true,
    },
});



</script>

<template>

    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Add Poduct
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <div v-if="$page.props.flash.success" class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>
                    <ProductForm :categories="categories" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
