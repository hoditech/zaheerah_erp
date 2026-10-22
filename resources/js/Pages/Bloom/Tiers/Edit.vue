<script setup lang="ts">
// @ts-nocheck
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TierForm from './Partials/TierForm.vue';

defineProps({
    tier: {
        type: Object,
        required: true,
    },
    themes: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Edit ${tier.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit {{ tier.name }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <TierForm :tier="tier" :themes="themes" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
