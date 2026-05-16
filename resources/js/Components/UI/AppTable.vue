<script setup>
defineProps({
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
    emptyMessage: { type: String, default: 'No data found.' },
});
</script>

<template>
    <div class="overflow-x-auto bg-[#161B26] rounded-xl border border-[#232936] shadow-sm">
        <table class="w-full text-sm text-slate-100">
            <thead>
                <tr class="border-b border-[#232936] bg-[#0F111A]/50">
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-3 text-left font-semibold text-slate-400 whitespace-nowrap', col.class || '']"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in rows"
                    :key="row.id || index"
                    class="border-b border-[#232936] hover:bg-[#232936]/50 transition-colors"
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
        <div v-if="$slots.pagination" class="px-4 py-3 border-t border-[#232936] bg-[#0F111A]/50">
            <slot name="pagination" />
        </div>
    </div>
</template>
