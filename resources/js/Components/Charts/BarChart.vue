<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, Title, Tooltip, Legend);

defineProps({
    chartData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 },
});
</script>

<template>
    <div :style="{ position: 'relative', height: height + 'px', width: '100%' }">
        <Bar :data="chartData" :options="{ responsive: true, maintainAspectRatio: false, ...options }" />
    </div>
</template>
