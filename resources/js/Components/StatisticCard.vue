<script setup lang="ts">
import { defineProps } from 'vue';

// Define the props for the component
const props = defineProps({
    title: { type: String, required: true },
    value: { type: String, required: true },
    color: { type: String, default: 'indigo' }, // Color for value text, default to indigo
    subtitle: { type: String, default: '' },
    updated: { type: String, default: '' }
});
</script>

<template>
    <div class="p-5 bg-white dark:bg-gray-800 shadow-lg rounded-lg">
        <h3 class="text-xl font-medium text-gray-800 dark:text-gray-200">{{ title }}</h3>
        <p :class="`mt-2 text-3xl font-bold text-${color}-500`">{{ value }}</p>
        <p v-if="subtitle" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
        <p v-if="updated" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Updated {{ updated }}</p>
    </div>
</template>

<style scoped>
/* Ensures custom colors can be applied */
</style>
