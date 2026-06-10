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
        <table class="w-full text-sm text-slate-100">
            <thead>
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
            <tbody>
                <tr
                    v-for="(row, index) in rows"
                    :key="row.id || index"
                    class="border-b border-border hover:bg-border/50 transition-colors"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-3', col.cellClass || '']"
                    >
                        <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                            {{ row[col.key] }}
                        </slot>
                    </td>
                </tr>
                <tr v-if="!rows.length">
                    <td :colspan="columns.length" class="px-4 py-8 text-center text-slate-400">
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

