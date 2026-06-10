<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';

import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';

const props = defineProps<{
    exports: any;
}>();

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
            }
        });
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true
    }).format(date);
};

let pollInterval: any = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        const hasActiveExports = props.exports.data.some((e: any) => e.status === 'pending' || e.status === 'processing');
        if (hasActiveExports) {
            router.reload({ only: ['exports'], preserveScroll: true, preserveState: true });
        }
    }, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <AppLayout title="Downloads">
        <Head title="Downloads" />

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white mb-1">Downloads</h1>
                <p class="text-sm text-slate-400">View and download your exported reports.</p>
            </div>
        </div>

        <AppCard class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800/50 border-b border-border">
                            <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Report Type</th>
                            <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Format</th>
                            <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Requested At</th>
                            <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in exports.data" :key="item.id" class="hover:bg-slate-800/30 transition">
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded bg-slate-800 text-slate-300">
                                        <AppIcon name="FileText" size="16" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-200 capitalize">
                                            {{ item.type.replace('-', ' ') }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ item.file_name || 'Generating...' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <span class="px-2 py-1 text-xs font-medium rounded uppercase"
                                      :class="item.format === 'pdf' ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400'">
                                    {{ item.format }}
                                </span>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2 text-sm" :class="{
                                    'text-amber-400': item.status === 'pending' || item.status === 'processing',
                                    'text-emerald-400': item.status === 'completed',
                                    'text-rose-400': item.status === 'failed'
                                }">
                                    <AppIcon v-if="item.status === 'completed'" name="CheckCircle" size="16" />
                                    <AppIcon v-else-if="item.status === 'failed'" name="XCircle" size="16" />
                                    <AppIcon v-else name="Loader" size="16" class="animate-spin" />
                                    <span class="capitalize">{{ item.status }}</span>
                                </div>
                                <p v-if="item.error" class="text-xs text-rose-500 mt-1 max-w-[200px] truncate" :title="item.error">
                                    {{ item.error }}
                                </p>
                            </td>
                            <td class="p-4 align-middle text-sm text-slate-400">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="p-4 align-middle text-right flex justify-end gap-2">
                                <a v-if="item.status === 'completed'" :href="`/downloads/${item.id}`" target="_blank"
                                   class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition">
                                    <AppIcon name="Download" size="14" /> Download
                                </a>
                                <button v-else disabled class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-500 bg-slate-800 rounded-lg cursor-not-allowed">
                                    Processing
                                </button>
                                <button @click="promptDelete(item.id)" class="inline-flex items-center justify-center p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" title="Delete">
                                    <AppIcon name="Trash2" size="18" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="exports.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-500">
                                No exports requested yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-border bg-slate-800/30" v-if="exports.data.length > 0">
                <AppPagination :links="exports.links" :meta="exports" />
            </div>
        </AppCard>

        <AppModal :show="showDeleteModal" title="Delete Export" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete this export? This action cannot be undone and will remove the file from your storage.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
