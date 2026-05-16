<script setup>
import { computed } from 'vue';
import * as LucideIcons from '@lucide/vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    strokeWidth: { type: [Number, String], default: 2 },
    class: { type: String, default: '' },
});

// Map legacy internal database string values (or semantic names) to their Lucide components
const lucideMapping = {
    'banknotes': 'Banknote',
    'credit-card': 'CreditCard',
    'building-library': 'Landmark',
    'currency-dollar': 'CircleDollarSign',
    'shopping-bag': 'ShoppingBag',
    'shopping-cart': 'ShoppingCart',
    'truck': 'Truck',
    'home': 'Home',
    'bolt': 'Zap',
    'heart': 'Heart',
    'academic-cap': 'GraduationCap',
    'briefcase': 'Briefcase',
    'chart-bar': 'BarChart',
    'computer-desktop': 'Monitor',
    'device-phone-mobile': 'Smartphone',
    'film': 'Film',
    'cake': 'Cake',
    'sparkles': 'Sparkles',
    'tag': 'Tag',
    'plus-circle': 'PlusCircle',
    'arrow-path': 'RefreshCw',
    'ellipsis-horizontal': 'MoreHorizontal',
    'wallet': 'Wallet',
    'gift': 'Gift',
    
    // Fallbacks and UI icons
    'Home': 'Home',
    'CreditCard': 'CreditCard',
    'ArrowUpDown': 'ArrowUpDown',
    'RefreshCw': 'RefreshCw',
    'Target': 'Target',
    'Folder': 'Folder',
    'BarChart': 'BarChart',
    'PieChart': 'PieChart',
    'ClipboardList': 'ClipboardList',
};

const iconComponent = computed(() => {
    const compName = lucideMapping[props.name] || props.name;
    return LucideIcons[compName] || LucideIcons.Package;
});
</script>

<template>
    <component :is="iconComponent" :size="size" :stroke-width="strokeWidth" :class="class" />
</template>
