<script setup lang="ts">
// @ts-nocheck
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CartonForm from './Partials/CartonForm.vue';
</script>

<template>
    <Head title="Add Bloom Carton Card" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Bloom Carton Card</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <CartonForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
