<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { PropType } from 'vue';

interface Column {
  label: string;
  key: string;
}

interface Action {
  label: string;
  onClick: (row: any) => void;
  class?: string;
}

const props = defineProps<{
  columns: Column[];
  data: any[];
  searchQuery: string;
  actions?: Action[];
  pageSize?: number;
}>();

const currentPage = ref(1);
const pageSize = ref(props.pageSize || 10);
const totalItems = computed(() => filteredData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / pageSize.value));

const resolveNestedKey = (obj: any, key: string) => {
  return key.split('.').reduce((acc, part) => acc && acc[part], obj);
};

const filteredData = computed(() => {
  return props.data.filter((item) =>
    props.columns.some((column) =>
      resolveNestedKey(item, column.key)?.toString().toLowerCase().includes(props.searchQuery.toLowerCase())
    )
  );
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredData.value.slice(start, end);
});

const goToPage = (page: number) => {
  if (page > 0 && page <= totalPages.value) currentPage.value = page;
};

watch(
  () => props.searchQuery,
  () => (currentPage.value = 1)
);
</script>

<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
      <thead>
        <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold uppercase tracking-wide">
          <th v-for="column in columns" :key="column.key" class="px-6 py-4 border-b text-left">
            {{ column.label }}
          </th>
          <th v-if="actions" class="px-6 py-4 border-b text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in paginatedData" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-4 border-b text-gray-700 dark:text-gray-300"
          >
            {{ resolveNestedKey(row, column.key) }}
          </td>
          <td v-if="actions" class="px-6 py-4 border-b">
            <button
              v-for="action in actions"
              :key="action.label"
              @click="action.onClick(row)"
              :class="[action.class, 'text-xs font-medium px-3 py-1 rounded-md']"
            >
              {{ action.label }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4 flex items-center justify-between px-6 py-4 bg-gray-100 dark:bg-gray-700 border-t">
      <div class="text-xs">
        Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, totalItems) }} of {{ totalItems }} results
      </div>
      <div class="flex items-center space-x-1">
        <button @click="goToPage(1)" :disabled="currentPage === 1" class="btn">First</button>
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="btn">Prev</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="btn">Next</button>
        <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages" class="btn">Last</button>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Improving table aesthetics */
table {
    border-collapse: collapse; /* Ensuring borders collapse for a cleaner look */
}

th, td {
    padding: 12px; /* Adjusting padding for better spacing */
}

/* Header styles */
thead th {
    background-color: #2f855a; /* Darker green for table header */
    color: #ffffff; /* White text for contrast */
    font-weight: 600; /* Bold font for headers */
}

/* Alternate row coloring for better readability */
tbody tr:nth-child(even) {
    background-color: #f9fafb; /* Light gray for even rows */
}

tbody tr:hover {
    background-color: #edf2f7; /* Light hover effect for better UX */
}

.dark thead th {
    background-color: #4a5568; /* Dark mode header background */
}

.dark tbody tr:nth-child(even) {
    background-color: #2d3748; /* Darker gray for even rows in dark mode */
}

.dark tbody tr:hover {
    background-color: #3c4858; /* Darker hover effect for dark mode */
}

/* Enhancing the input field for a more modern look */
input[type="text"] {
    padding-left: 2rem; /* Add space for the search icon */
}

input::placeholder {
    color: #a0aec0; /* Placeholder color */
}
</style>
