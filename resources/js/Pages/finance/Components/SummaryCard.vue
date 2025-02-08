<template>
  <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
    <h4 :class="['text-lg', 'font-semibold', 'text-gray-700', 'dark:text-gray-200']">{{ title }}</h4>
    <p :class="['text-2xl', 'font-bold', 'dark:text-gray-100', color]">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps({
  title: String,
  value: String,
  color: {
    type: String,
    default: 'text-gray-900', // default color
  },
});
</script>
