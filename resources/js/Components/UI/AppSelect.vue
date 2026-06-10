<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    options: { type: Array, default: () => [] },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Select an option' },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const triggerRef = ref(null);
const panelRef = ref(null);

// Fixed panel coords — Teleport to body bypasses modal overflow:hidden
const panelStyle = ref({});

const selectedLabel = computed(() => {
    const opt = props.options.find(o => String(o.value ?? o.id) === String(props.modelValue));
    return opt ? (opt.label ?? opt.name) : null;
});

const computePosition = async () => {
    await nextTick();
    if (!triggerRef.value || !panelRef.value) return;

    const rect = triggerRef.value.getBoundingClientRect();
    const panelH = panelRef.value.scrollHeight || 220;
    const panelW = Math.max(rect.width, 200);
    const gap = 4;

    const spaceBelow = window.innerHeight - rect.bottom;
    const openUp = spaceBelow < panelH + gap && rect.top > panelH + gap;

    panelStyle.value = {
        position: 'fixed',
        width: panelW + 'px',
        left: Math.min(rect.left, window.innerWidth - panelW - 8) + 'px',
        top: openUp
            ? (rect.top - panelH - gap) + 'px'
            : (rect.bottom + gap) + 'px',
        zIndex: '9999',
    };
};

const select = (value) => {
    emit('update:modelValue', value);
    emit('change', value);
    isOpen.value = false;
};

const toggle = async () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) await computePosition();
};

const onOutside = (e) => {
    if (!triggerRef.value?.contains(e.target) && !panelRef.value?.contains(e.target)) {
        isOpen.value = false;
    }
};
const onScroll = () => { if (isOpen.value) computePosition(); };

onMounted(() => {
    document.addEventListener('mousedown', onOutside);
    window.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', onScroll);
});
onUnmounted(() => {
    document.removeEventListener('mousedown', onOutside);
    window.removeEventListener('scroll', onScroll, true);
    window.removeEventListener('resize', onScroll);
});
</script>

<template>
    <div>
        <!-- Label -->
        <label v-if="label" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            {{ label }}<span v-if="required" class="text-rose-400 ml-0.5">*</span>
        </label>

        <!-- Trigger Button -->
        <button
            ref="triggerRef"
            type="button"
            @click="toggle"
            :disabled="disabled"
            :class="[
                'w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg border text-sm transition-all duration-200 text-left focus:outline-none',
                error
                    ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-1 focus:ring-rose-400/30'
                    : isOpen
                        ? 'border-primary/70 bg-card-bg ring-1 ring-primary/30'
                        : 'border-border bg-card-bg hover:border-slate-600',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
            ]"
        >
            <span :class="selectedLabel ? 'text-slate-100' : 'text-slate-500'">
                {{ selectedLabel ?? placeholder }}
            </span>
            <AppIcon
                name="ChevronDown"
                size="14"
                :class="['text-slate-500 shrink-0 transition-transform duration-200', isOpen ? 'rotate-180' : '']"
            />
        </button>

        <!-- Error -->
        <p v-if="error" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
            <AppIcon name="AlertCircle" size="11" />{{ error }}
        </p>
    </div>

    <!-- ─── Dropdown Panel — Teleported to <body> to escape overflow:hidden ─── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-y-95 -translate-y-1"
            enter-to-class="opacity-100 scale-y-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-y-100 translate-y-0"
            leave-to-class="opacity-0 scale-y-95 -translate-y-1"
        >
            <div v-if="isOpen" ref="panelRef" :style="panelStyle">
                <ul class="bg-[#0D1017] border border-[#232936] rounded-xl shadow-2xl shadow-black/50 overflow-y-auto max-h-60 py-1">
                    <!-- Placeholder hint -->
                    <li class="px-3 py-2 text-xs text-slate-500 italic select-none border-b border-[#1a2030] mb-1">
                        {{ placeholder }}
                    </li>
                    <li
                        v-for="opt in options"
                        :key="opt.value ?? opt.id"
                        @click="select(String(opt.value ?? opt.id))"
                        :class="[
                            'flex items-center gap-2.5 px-3 py-2.5 mx-1 text-sm cursor-pointer transition-colors duration-100 rounded-lg',
                            String(opt.value ?? opt.id) === String(modelValue)
                                ? 'bg-primary/15 text-primary font-semibold'
                                : 'text-slate-300 hover:bg-[#1E2535] hover:text-slate-100',
                        ]"
                    >
                        <span class="w-3 shrink-0 flex items-center">
                            <AppIcon
                                v-if="String(opt.value ?? opt.id) === String(modelValue)"
                                name="Check"
                                size="12"
                                class="text-primary"
                            />
                        </span>
                        {{ opt.label ?? opt.name }}
                    </li>
                </ul>
            </div>
        </Transition>
    </Teleport>
</template>
