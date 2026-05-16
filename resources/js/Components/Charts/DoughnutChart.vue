<script setup>
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

defineProps({
    chartData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 },
});
</script>

<template>
    <div :style="{ position: 'relative', height: height + 'px', width: '100%' }">
        <Doughnut :data="chartData" :options="{ responsive: true, maintainAspectRatio: false, ...options }" />
    </div>
</template>
