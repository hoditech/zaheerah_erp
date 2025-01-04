<script setup lang="ts">
import { defineProps } from 'vue';

interface ActivityItem {
    description: string;
    time: string;
}

// Define the props for the component
const props = defineProps<{
    title: string;
    items: ActivityItem[]; // Specify items as an array of ActivityItem objects
}>();
</script>

<template>
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ title }}</h3>
        <ul class="mt-4 space-y-4">
            <li 
                v-for="(item, index) in items" 
                :key="index" 
                class="p-4 bg-gray-100 dark:bg-gray-700 rounded-md flex justify-between"
            >
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ item.description }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.time }}</span>
            </li>
        </ul>
    </div>
</template>
