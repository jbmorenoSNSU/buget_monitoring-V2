<script setup>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

defineProps({
    chartData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 },
});
</script>

<template>
    <div :style="{ position: 'relative', height: height + 'px', width: '100%' }">
        <Line :data="chartData" :options="{ responsive: true, maintainAspectRatio: false, ...options }" />
    </div>
</template>
