<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    chartData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 },
});

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 12,
                boxHeight: 12,
                padding: 16,
                color: '#94A3B8',
                font: {
                    family: 'Outfit, ui-sans-serif, system-ui, sans-serif',
                    size: 11,
                    weight: '500'
                }
            }
        }
    }
};

const mergedOptions = computed(() => {
    return {
        ...defaultOptions,
        ...props.options,
        plugins: {
            ...defaultOptions.plugins,
            ...props.options.plugins,
            legend: {
                ...defaultOptions.plugins.legend,
                ...props.options.plugins?.legend,
                labels: {
                    ...defaultOptions.plugins.legend.labels,
                    ...props.options.plugins?.legend?.labels
                }
            }
        }
    };
});
</script>

<template>
    <div :style="{ position: 'relative', height: height + 'px', width: '100%' }">
        <Doughnut :data="chartData" :options="mergedOptions" />
    </div>
</template>
