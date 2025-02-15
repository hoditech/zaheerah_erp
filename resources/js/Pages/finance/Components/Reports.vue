<template>
  <div>
    <h3 class="text-2xl font-semibold mb-4">Reports</h3>
    <div class="mb-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
      <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Customize Report</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Date Range</label>
          <div class="flex space-x-4">
            <input v-model="dateRange.start" type="date"
              class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <input v-model="dateRange.end" type="date"
              class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Select Columns</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="column in ['name', 'revenue', 'cost', 'profit', 'inventory']" :key="column"
              class="flex items-center space-x-2">
              <input type="checkbox" v-model="selectedColumns" :value="column"
                class="rounded border-gray-300 dark:border-gray-700" />
              <span class="text-gray-700 dark:text-gray-200">{{ column.replace(/([A-Z])/g, '$1').toUpperCase() }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-6 flex space-x-4">
      <button @click="exportReports('csv')"
        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
        Export as CSV
      </button>

      <button @click="exportReports('excel')"
        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
        Export as Excel
      </button>
    </div>
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
      <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Preview</h4>
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-200 dark:bg-gray-700">
            <th v-for="column in selectedColumns" :key="column" class="py-3 px-4 text-left">
              {{ column.replace(/([A-Z])/g, ' $1').toUpperCase() }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="report in filteredReports" :key="report.id"
            class="border-t border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
            <td v-for="column in selectedColumns" :key="column" class="py-3 px-4">
              {{ report[column] ?? 'N/A' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import * as XLSX from 'xlsx'; 

const props = withDefaults(defineProps<{
  categories?: {
    id: number;
    name: string;
    revenue: number;
    cost: number;
    profit: number;
    inventory: number;
  }[];
}>(), {
  categories: () => [],
});

const selectedColumns = ref(['name', 'revenue', 'cost', 'profit', 'inventory']);
const dateRange = ref({ start: '2023-10-01', end: '2023-10-31' });

const filteredReports = computed(() => {
  return (props.categories ?? []).filter(category => {
    // Add filtering logic based on date range if needed
    return true;
  }) as { [key: string]: any }[];
});


const exportReports = (format: string) => {
  const headers = selectedColumns.value.map(col => 
    col.replace(/([A-Z])/g, ' $1').toUpperCase()
  );
  
  // Prepare data for Excel
  const excelData = [
    headers,
    ...filteredReports.value.map(report =>
      selectedColumns.value.map(col => report[col] ?? 'N/A')
    )
  ];

  const rows = filteredReports.value.map(report =>
    selectedColumns.value.map(col => {
      const value = report[col] ?? 'N/A';
      const escaped = String(value).replace(/"/g, '""');
      return `"${escaped}"`;
    }).join(',')
  ).join('\r\n');

  if (format === 'excel') {
    // Create workbook & worksheet
    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(excelData);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Report');
    
    // Generate XLSX file
    XLSX.writeFile(workbook, 'custom_report.xlsx');
  }
  else if (format === 'csv') {
    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(excelData);

    // Add column styling
    worksheet['!cols'] = headers.map(() => ({
      wch: 20 // Column width
    }));

    // Freeze header row
    worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };
    
    // Add filters
    worksheet['!autofilter'] = { ref: XLSX.utils.encode_range({ 
      s: { r: 0, c: 0 }, 
      e: { r: excelData.length, c: headers.length } 
    }) };

    XLSX.utils.book_append_sheet(workbook, worksheet, 'Report');
    XLSX.writeFile(workbook, 'custom_report.xlsx');

  }
};
</script>