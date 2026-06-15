<script setup lang="ts">
import AppIcon from '@/Components/UI/AppIcon.vue';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    cellClass?: string;
}

const props = withDefaults(defineProps<{
    columns?: Column[];
    rows?: any[];
    emptyMessage?: string;
    sortBy?: string;
    sortDirection?: string;
}>(), {
    columns: () => [],
    rows: () => [],
    emptyMessage: 'No data found.',
    sortBy: '',
    sortDirection: 'asc',
});

const emit = defineEmits<{
    (e: 'sort', key: string): void;
}>();

const handleHeaderClick = (col: Column) => {
    if (col.sortable) {
        emit('sort', col.key);
    }
};

defineSlots<
    {
        [key: `cell-${string}`]: (props: { row: any; value: any }) => any;
        pagination?: () => any;
    }
>();
</script>

<template>
    <div class="overflow-x-auto bg-card-bg rounded-xl border border-border shadow-sm">
        <table class="w-full text-sm text-slate-100 max-md:block max-md:w-full">
            <thead class="max-md:hidden">
                <tr class="border-b border-border bg-page-bg/50">
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        @click="handleHeaderClick(col)"
                        :class="[
                            'px-4 py-3 text-left font-semibold text-slate-400 whitespace-nowrap select-none',
                            col.sortable ? 'cursor-pointer hover:text-slate-200 transition-colors' : '',
                            col.class || ''
                        ]"
                    >
                        <div :class="['flex items-center gap-1.5', (col.class || '').includes('text-right') ? 'justify-end' : '']">
                            <span>{{ col.label }}</span>
                            <span v-if="col.sortable" class="text-slate-500">
                                <AppIcon
                                    :name="sortBy === col.key 
                                        ? (sortDirection === 'asc' ? 'ArrowUp' : 'ArrowDown') 
                                        : 'ArrowUpDown'"
                                    size="13"
                                    class="transition-transform duration-200"
                                />
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="max-md:block max-md:w-full">
                <tr
                    v-for="(row, index) in rows"
                    :key="row.id || index"
                    class="border-b border-border hover:bg-border/50 transition-colors max-md:block max-md:w-full max-md:mb-4 max-md:bg-page-bg/30 max-md:rounded-lg max-md:p-2"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-3 max-md:block max-md:w-full max-md:flex max-md:justify-between max-md:items-center max-md:border-b max-md:border-border/30 last:max-md:border-none', col.cellClass || '']"
                    >
                        <span class="md:hidden text-slate-400 font-medium text-xs">{{ col.label }}</span>
                        <div class="max-md:text-right">
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </div>
                    </td>
                </tr>
                <tr v-if="!rows.length" class="max-md:block">
                    <td :colspan="columns.length" class="px-4 py-8 text-center text-slate-400 max-md:block">
                        {{ emptyMessage }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-if="$slots.pagination" class="px-4 py-3 border-t border-border bg-page-bg/50">
            <slot name="pagination" />
        </div>
    </div>
</template>

