<script setup>
import AppIcon from '@/Components/UI/AppIcon.vue';
defineProps({
    modelValue: { type: String, default: 'banknotes' },
    label: { type: String, default: 'Icon' },
});

const emit = defineEmits(['update:modelValue']);

const icons = [
    'banknotes', 'credit-card', 'building-library', 'currency-dollar',
    'shopping-bag', 'shopping-cart', 'truck', 'home',
    'bolt', 'heart', 'academic-cap', 'briefcase',
    'chart-bar', 'computer-desktop', 'device-phone-mobile', 'film',
    'cake', 'sparkles', 'tag', 'plus-circle',
    'arrow-path', 'ellipsis-horizontal', 'wallet', 'gift',
    'shirt', 'hammer', 'utensils', 'church', 'piggy-bank',
];
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">{{ label }}</label>
        <div class="grid grid-cols-8 gap-2">
            <button
                v-for="icon in icons"
                :key="icon"
                type="button"
                @click="emit('update:modelValue', icon)"
                :class="[
                    'w-10 h-10 flex items-center justify-center rounded-lg border-2 text-lg transition-all duration-200 cursor-pointer hover:bg-border',
                    modelValue === icon ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'border-border',
                ]"
                :title="icon"
            >
                <AppIcon :name="icon" size="20" :class="modelValue === icon ? 'text-primary' : 'text-slate-400'" />
            </button>
        </div>
    </div>
</template>
