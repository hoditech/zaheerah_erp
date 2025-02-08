<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    labels: Array,
    revenueData: Object,
    expensesData: Object,
});

const chartCanvas = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (chartCanvas.value) {
        new Chart(chartCanvas.value, {
            type: 'bar',
            data: {
                labels: props.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: props.revenueData,
                        backgroundColor: '#4CAF50',
                    },
                    {
                        label: 'Expenses',
                        data: props.expensesData,
                        backgroundColor: '#FF5252',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }
});

console.log(props);
</script>

<template>
    <canvas ref="chartCanvas"></canvas>
</template>