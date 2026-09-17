<template>
  <div>
    <h3 class="text-2xl font-semibold mb-4">Reports</h3>
    
    <!-- Report Selection -->
    <div class="mb-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Select Report Type</label>
          <select v-model="selectedReport" class="w-full px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700">
            <option v-for="report in reportTypes" :key="report.value" :value="report.value">
              {{ report.label }}
            </option>
          </select>
        </div>
        
        <!-- Dynamic Filters -->
        <div v-if="selectedReport !== 'inventory' && selectedReport !== 'expiry'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Date Range</label>
          <div class="flex space-x-4">
            <input v-model="dateRange.start" type="date"
              class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700" />
            <input v-model="dateRange.end" type="date"
              class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700" />
          </div>
        </div>
      </div>
    </div>

    <!-- Report Configuration -->
    <div class="mb-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Select Columns</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="column in availableColumns" :key="column.value" class="flex items-center space-x-2">
              <input type="checkbox" v-model="selectedColumns" :value="column.value"
                class="rounded border-gray-300 dark:border-gray-700" />
              <span class="text-gray-700 dark:text-gray-200">{{ column.label }}</span>
            </label>
          </div>
        </div>

        <div v-if="selectedReport === 'expiry'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Expiry Threshold (Days)</label>
          <input v-model.number="expiryThreshold" type="number" min="1" max="365"
            class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 w-32" />
        </div>
      </div>

      <button @click="fetchReportData" 
        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
        Generate Report
      </button>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex space-x-4">
      <button @click="exportReports('csv')"
        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
        Export as CSV
      </button>
      <!-- <button @click="exportReports('excel')"
        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
        Export as Excel
      </button> -->
    </div>

    <!-- Preview Table -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
      <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Preview</h4>
      <div v-if="isLoading" class="text-center py-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div>
      </div>
      <div v-else>
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200 dark:bg-gray-700">
              <th v-for="column in selectedColumns" :key="column" class="py-3 px-4 text-left">
                {{ columnLabels[column] }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(report, index) in reports" :key="index"
              class="border-t border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
              <td v-for="column in selectedColumns" :key="column" class="py-3 px-4">
                {{ formatColumn(report, column) }}
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="reports.length === 0" class="text-center py-4 text-gray-500">
          No data available for the selected report
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';

// Reactive state
const reportTypes = ref([
  { value: 'sales', label: 'Sales Report' },
  { value: 'products', label: 'Product Performance' },
  { value: 'categories', label: 'Category Sales' },
  { value: 'inventory', label: 'Inventory Status' },
  { value: 'expiry', label: 'Expiry Report' },
  { value: 'stock-movement', label: 'Stock Movement' }
]);

const selectedReport = ref('sales');
const dateRange = ref({ start: '', end: '' });
const expiryThreshold = ref(30);
const reports = ref<Array<any>>([]);
const selectedColumns = ref<string[]>([]);
const isLoading = ref(false);

// Column configurations
const reportColumns = {
  sales: [
    { value: 'transaction_id', label: 'Transaction ID' },
    { value: 'date', label: 'Date' },
    { value: 'total_amount', label: 'Total Amount' },
    { value: 'items_count', label: 'Items Sold' }
  ],
  products: [
    { value: 'product_name', label: 'Product Name' },
    { value: 'category', label: 'Category' },
    { value: 'total_sold', label: 'Units Sold' },
    { value: 'total_revenue', label: 'Revenue' }
  ],
  categories: [
    { value: 'category_name', label: 'Category' },
    { value: 'total_quantity', label: 'Units Sold' },
    { value: 'total_revenue', label: 'Revenue' }
  ],
  inventory: [
    { value: 'product_name', label: 'Product' },
    { value: 'category', label: 'Category' },
    { value: 'remaining_units', label: 'Stock' },
    { value: 'supplier', label: 'Supplier' }
  ],
  expiry: [
    { value: 'product_name', label: 'Product' },
    { value: 'exp_date', label: 'Expiry Date' },
    { value: 'remaining_units', label: 'Stock' },
    { value: 'days_until_expiry', label: 'Days Until Expiry' }
  ],
  'stock-movement': [
    { value: 'product_name', label: 'Product' },
    { value: 'total_produced', label: 'Total Produced' },
    { value: 'total_sold', label: 'Total Sold' },
    { value: 'net_movement', label: 'Net Movement' }
  ]
};

// Computed properties
const availableColumns = computed(() => 
  reportColumns[selectedReport.value as keyof typeof reportColumns] || []
);

const columnLabels = computed(() => {
  return Object.fromEntries(
    availableColumns.value.map(col => [col.value, col.label])
  );
});

// Watchers
watch(selectedReport, (newVal) => {
  selectedColumns.value = reportColumns[newVal as keyof typeof reportColumns]
    .slice(0, 4).map(c => c.value);
});

// Data fetching and transformation
const fetchReportData = async () => {
  try {

    isLoading.value = true;
    reports.value = [];
    
    const params: any = {};
    
    if (['sales', 'categories', 'stock-movement'].includes(selectedReport.value)) {
      params.start_date = dateRange.value.start;
      params.end_date = dateRange.value.end;
    }
    
    if (selectedReport.value === 'expiry') {
      params.threshold = expiryThreshold.value;
    }

    const response = await axios.get(`/reports/${selectedReport.value}`, { params });
    
    // Handle paginated and non-paginated responses
    const baseData = response.data.data?.data || response.data.data;
    console.log(baseData);
    
    switch(selectedReport.value) {
      case 'sales':
        reports.value = transformSalesData(baseData);
        break;
      case 'products':
        reports.value = transformProductData(baseData);
        break;
      case 'categories':
        reports.value = transformCategoryData(baseData);
        break;
      case 'inventory':
        reports.value = transformInventoryData(baseData);
        break;
      case 'expiry':
        reports.value = transformExpiryData(baseData);
        break;
      case 'stock-movement':
        reports.value = transformStockMovementData(baseData);
        break;
      default:
        reports.value = baseData;
    }
  } catch (error) {
    console.error('Error fetching report:', error);
    alert('Failed to fetch report data');
  } finally {
    isLoading.value = false;
  }
};

// Formatting and export
const formatColumn = (report: any, column: string) => {
  const value = report[column];
  if (column.includes('amount') || column.includes('revenue')) {
    return `$${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
  }
  if (typeof value === 'number') return value.toLocaleString();
  return value || 'N/A';
};

const exportReports = async (format: string) => {
  try {
    const params: any = { per_page: 'all' };
    
    if (['sales', 'categories', 'stock-movement'].includes(selectedReport.value)) {
      params.start_date = dateRange.value.start;
      params.end_date = dateRange.value.end;
    }
    
    if (selectedReport.value === 'expiry') {
      params.threshold = expiryThreshold.value;
    }

    const response = await axios.get(`/reports/${selectedReport.value}`, { params });
    const baseData = response.data.data?.data || response.data.data;
    
    let exportData = [];
    switch(selectedReport.value) {
      case 'sales':
        exportData = transformSalesData(baseData);
        break;
      case 'products':
        exportData = transformProductData(baseData);
        break;
      case 'categories':
        exportData = transformCategoryData(baseData);
        break;
      case 'inventory':
        exportData = transformInventoryData(baseData);
        break;
      case 'expiry':
        exportData = transformExpiryData(baseData);
        break;
      case 'stock-movement':
        exportData = transformStockMovementData(baseData);
        break;
      default:
        exportData = baseData;
    }

    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Report');

    const filename = `${selectedReport.value}_report.${format}`;
    XLSX.writeFile(workbook, filename, { bookType: format as XLSX.BookType });
  } catch (error) {
    console.error('Export failed:', error);
    alert('Failed to export report');
  }
};

// Transformation functions
const transformSalesData = (data: any) => {
  const transactions = data.transactions?.data || data;
  return transactions.map((sale: any) => ({
    transaction_id: sale.id,
    date: new Date(sale.created_at).toLocaleDateString(),
    total_amount: sale.total_amount || 0,
    items_count: sale.items?.reduce((sum: number, item: any) => sum + (item.quantity || 0), 0) || 0
  }));
};

const transformProductData = (data: any) => {
  return data.map((product: any) => ({
    product_name: product.name || 'N/A',
    category: product.category?.name || 'N/A',
    total_sold: product.total_sold || 0,
    total_revenue: product.total_revenue || 0
  }));
};

const transformCategoryData = (data: any) => {
  return data.map((category: any) => ({
    category_name: category.name || 'N/A',
    total_quantity: category.total_quantity || 0,
    total_revenue: category.total_revenue || 0
  }));
};

const transformInventoryData = (data: any) => {
  return data.map((product: any) => ({
    product_name: product.name || 'N/A',
    category: product.category?.name || 'N/A',
    remaining_units: product.stock || 0,
    supplier: product.supplier?.name || 'N/A'
  }));
};

const transformExpiryData = (data: any) => {
  const products = data.products?.data || data;
  return products.map((product: any) => ({
    product_name: product.name || 'N/A',
    exp_date: product.exp_date ? new Date(product.exp_date).toLocaleDateString() : 'N/A',
    remaining_units: product.remaining_units || 0,
    days_until_expiry: product.exp_date 
      ? Math.ceil((new Date(product.exp_date).getTime() - Date.now()) / (1000 * 3600 * 24)) 
      : 'N/A'  // Fixed missing parenthesis
  }));
};


const transformStockMovementData = (data: any) => {
  return data.map((product: any) => ({
    product_name: product.name || 'N/A',
    total_produced: product.total_produced || 0,
    total_sold: product.total_sold || 0,
    net_movement: (product.total_produced || 0) - (product.total_sold || 0)
  }));
};
</script>