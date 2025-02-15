<template>
    <aside class="w-48 bg-white dark:bg-gray-800 border-r border-gray-300 dark:border-gray-700 p-4">
        <nav>
            <ul class="space-y-3">
                <li v-for="tab in tabs" :key="tab">
                    <a href="#" @click.prevent="switchTab(tab)" :class="{ 'bg-green-500 text-white': activeTab === tab }"
                        class="block p-3 rounded-lg hover:bg-green-500 hover:text-white transition">
                        {{ tab }}
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script setup lang="ts">
import { PropType } from 'vue';

defineProps({
    activeTab: String,
    tabs: Array as PropType<string[]>,
});


const emit = defineEmits(['switch-tab']);

const switchTab = (tab: string) => {
    emit('switch-tab', tab);
};
</script>