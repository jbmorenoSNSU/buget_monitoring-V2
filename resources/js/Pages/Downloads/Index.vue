<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import { usePageTitle } from '@/composables/usePageTitle';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';

// ── Types ────────────────────────────────────────────────────────────────────

interface ExportItem {
    id: number;
    type: string;
    format: 'pdf' | 'xlsx';
    file_name: string | null;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    error: string | null;
    created_at: string;
}

interface PaginatedExports {
    data: ExportItem[];
    links: { url: string | null; label: string; active: boolean }[];
    next_cursor: string | null;
    prev_cursor: string | null;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{ exports: PaginatedExports }>();

// ── Page title ────────────────────────────────────────────────────────────────

const { setPageTitle } = usePageTitle();
setPageTitle('Downloads');

// ── Delete ────────────────────────────────────────────────────────────────────

const deleteTarget = ref<number | null>(null);
const showDeleteModal = ref(false);

const promptDelete = (id: number) => {
    deleteTarget.value = id;
    showDeleteModal.value = true;
};

const doDelete = () => {
    if (deleteTarget.value !== null) {
        router.delete(`/downloads/${deleteTarget.value}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────────

const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    return new Intl.DateTimeFormat('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true,
    }).format(new Date(dateString));
};

// Replace ALL hyphens (e.g. "budget-goal" → "budget goal")
const formatType = (type: string): string => type.replace(/-/g, ' ');

// ponytail: map covers known formats; unknown falls back to a neutral style
const FORMAT_BADGE: Record<string, string> = {
    pdf:  'bg-rose-500/10 text-rose-400',
    xlsx: 'bg-emerald-500/10 text-emerald-400',
    csv:  'bg-blue-500/10 text-blue-400',
};

const formatBadgeClass = (format: string): string =>
    FORMAT_BADGE[format] ?? 'bg-slate-500/10 text-slate-400';

// ── Polling ───────────────────────────────────────────────────────────────────

const ACTIVE_STATUSES: ExportItem['status'][] = ['pending', 'processing'];

let pollInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        // Skip when the browser tab is hidden — no need to hammer the server
        if (document.hidden) return;

        const hasActive = props.exports.data.some(
            (e) => ACTIVE_STATUSES.includes(e.status)
        );

        if (hasActive) {
            router.reload({ only: ['exports'] });
        } else {
            // Nothing left to poll — stop the interval early
            clearInterval(pollInterval!);
            pollInterval = null;
        }
    }, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// ── Table columns ─────────────────────────────────────────────────────────────

const columns = [
    { key: 'type',       label: 'Report Type' },
    { key: 'format',     label: 'Format' },
    { key: 'status',     label: 'Status' },
    { key: 'created_at', label: 'Requested At' },
    { key: 'actions',    label: 'Actions', class: 'text-right', cellClass: 'text-right' },
];
</script>

<template>
    <div>
        <Head title="Downloads" />

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-100">Downloads</h2>
                <p class="text-sm text-slate-400">View and download your exported reports.</p>
            </div>
        </div>

        <AppTable :columns="columns" :rows="exports.data" emptyMessage="No exports requested yet.">

            <!-- Report Type -->
            <template #cell-type="{ row: item }">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded bg-slate-800 text-slate-300">
                        <AppIcon name="FileText" size="16" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-200 capitalize">
                            {{ formatType(item.type) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ item.file_name || 'Generating...' }}</p>
                    </div>
                </div>
            </template>

            <!-- Format badge -->
            <template #cell-format="{ row: item }">
                <span class="px-2 py-1 text-[10px] font-bold rounded uppercase" :class="formatBadgeClass(item.format)">
                    {{ item.format }}
                </span>
            </template>

            <!-- Status -->
            <template #cell-status="{ row: item }">
                <div class="flex items-center gap-2 text-sm" :class="{
                    'text-amber-400':  item.status === 'pending' || item.status === 'processing',
                    'text-emerald-400': item.status === 'completed',
                    'text-rose-400':   item.status === 'failed',
                }">
                    <AppIcon v-if="item.status === 'completed'"  name="CheckCircle" size="16" />
                    <AppIcon v-else-if="item.status === 'failed'" name="XCircle"    size="16" />
                    <AppIcon v-else name="Loader" size="16" class="animate-spin" />
                    <span class="capitalize font-medium">{{ item.status }}</span>
                </div>
                <p v-if="item.error" class="text-xs text-rose-500 mt-1 max-w-[200px] truncate" :title="item.error">
                    {{ item.error }}
                </p>
            </template>

            <!-- Requested At -->
            <template #cell-created_at="{ row: item }">
                <span class="text-sm text-slate-400">{{ formatDate(item.created_at) }}</span>
            </template>

            <!-- Actions -->
            <template #cell-actions="{ row: item }">
                <div class="flex justify-end gap-2">
                    <!-- No target="_blank" — the route returns Content-Disposition: attachment -->
                    <a v-if="item.status === 'completed'"
                       :href="`/downloads/${item.id}`"
                       class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-xs font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition">
                        <AppIcon name="Download" size="14" /> Download
                    </a>
                    <button v-else disabled
                            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-xs font-medium text-slate-500 bg-slate-800 rounded-lg cursor-not-allowed">
                        Processing
                    </button>
                    <button @click="promptDelete(item.id)"
                            class="inline-flex items-center justify-center p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition"
                            title="Delete">
                        <AppIcon name="Trash2" size="16" />
                    </button>
                </div>
            </template>

            <!-- Pagination -->
            <template #pagination v-if="exports.data.length > 0">
                <AppPagination :links="exports.links" :meta="exports" />
            </template>

        </AppTable>

        <!-- Delete confirmation modal -->
        <AppModal :show="showDeleteModal" title="Delete Export" @close="showDeleteModal = false">
            <p class="text-slate-400">
                Are you sure you want to delete this export? This action cannot be undone and will
                remove the file from your storage.
            </p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </div>
</template>
