<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:green-indigo-600 dark:focus:ring-green-600"
        v-model="model"
        ref="input"
    />
</template>
