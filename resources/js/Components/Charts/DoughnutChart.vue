<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    chartData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 },
    centerText: { type: String, default: '' },
    centerSubtext: { type: String, default: 'Total Expenses' },
});

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
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

const centerTextPlugin = {
    id: 'centerTextPlugin',
    beforeDraw(chart) {
        if (!props.centerText) return;
        
        const { ctx, chartArea: { top, left, width, height } } = chart;
        ctx.save();
        
        ctx.font = 'bolder 20px "Outfit", sans-serif';
        ctx.fillStyle = '#f1f5f9';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const centerX = left + width / 2;
        const centerY = top + height / 2;
        
        const yOffset = props.centerSubtext ? -8 : 0;
        ctx.fillText(props.centerText, centerX, centerY + yOffset);
        
        if (props.centerSubtext) {
            ctx.font = '500 11px "Outfit", sans-serif';
            ctx.fillStyle = '#94a3b8';
            ctx.fillText(props.centerSubtext, centerX, centerY + 14);
        }
        
        ctx.restore();
    }
};

const customPlugins = [centerTextPlugin];
</script>

<template>
    <div :style="{ position: 'relative', height: height + 'px', width: '100%' }">
        <Doughnut :data="chartData" :options="mergedOptions" :plugins="customPlugins" />
    </div>
</template>
