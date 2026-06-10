<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Select date' },
    hint: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);

// ─── State ─────────────────────────────────────────────────────────
const isOpen = ref(false);
const triggerRef = ref(null);
const panelRef = ref(null);

// Fixed-position panel coords (Teleport to body bypasses modal overflow:hidden)
const panelStyle = ref({ top: '0px', left: '0px', width: '288px' });

const today = new Date();
today.setHours(0, 0, 0, 0);

const cursorYear = ref(today.getFullYear());
const cursorMonth = ref(today.getMonth());

const selectedDate = computed(() => {
    if (!props.modelValue) return null;
    const [y, m, d] = props.modelValue.split('-').map(Number);
    return new Date(y, m - 1, d);
});

const displayValue = computed(() => {
    if (!selectedDate.value) return '';
    return selectedDate.value.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
});

// ─── Calendar grid ─────────────────────────────────────────────────
const MONTH_NAMES = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December',
];
const DAY_ABBREVS = ['Su','Mo','Tu','We','Th','Fr','Sa'];

const calendarTitle = computed(() => `${MONTH_NAMES[cursorMonth.value]} ${cursorYear.value}`);

const calendarDays = computed(() => {
    const firstDay = new Date(cursorYear.value, cursorMonth.value, 1).getDay();
    const daysInMonth = new Date(cursorYear.value, cursorMonth.value + 1, 0).getDate();
    const cells = [];
    for (let i = 0; i < firstDay; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(d);
    while (cells.length % 7 !== 0) cells.push(null);
    return cells;
});

const isToday = (day) => {
    if (!day) return false;
    const d = new Date(cursorYear.value, cursorMonth.value, day);
    return d.getTime() === today.getTime();
};
const isSelected = (day) => {
    if (!day || !selectedDate.value) return false;
    return (
        selectedDate.value.getFullYear() === cursorYear.value &&
        selectedDate.value.getMonth() === cursorMonth.value &&
        selectedDate.value.getDate() === day
    );
};

// ─── Navigation ────────────────────────────────────────────────────
const prevMonth = () => {
    if (cursorMonth.value === 0) { cursorMonth.value = 11; cursorYear.value--; }
    else cursorMonth.value--;
};
const nextMonth = () => {
    if (cursorMonth.value === 11) { cursorMonth.value = 0; cursorYear.value++; }
    else cursorMonth.value++;
};

const goToday = () => {
    cursorYear.value = today.getFullYear();
    cursorMonth.value = today.getMonth();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const value = `${today.getFullYear()}-${mm}-${dd}`;
    emit('update:modelValue', value);
    emit('change', value);
    isOpen.value = false;
};

// ─── Selection ─────────────────────────────────────────────────────
const selectDay = (day) => {
    if (!day) return;
    const mm = String(cursorMonth.value + 1).padStart(2, '0');
    const dd = String(day).padStart(2, '0');
    const value = `${cursorYear.value}-${mm}-${dd}`;
    emit('update:modelValue', value);
    emit('change', value);
    isOpen.value = false;
};

const clearDate = (e) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
};

// ─── Open / close with Teleport positioning ────────────────────────
const computePosition = async () => {
    await nextTick();
    if (!triggerRef.value || !panelRef.value) return;

    const rect = triggerRef.value.getBoundingClientRect();
    const panelH = panelRef.value.offsetHeight || 340;
    const panelW = Math.max(rect.width, 288);
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

const openCalendar = async () => {
    if (props.disabled) return;
    if (isOpen.value) { isOpen.value = false; return; }

    if (selectedDate.value) {
        cursorYear.value = selectedDate.value.getFullYear();
        cursorMonth.value = selectedDate.value.getMonth();
    } else {
        cursorYear.value = today.getFullYear();
        cursorMonth.value = today.getMonth();
    }

    isOpen.value = true;
    await computePosition();
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
            @click="openCalendar"
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
            <span :class="displayValue ? 'text-slate-100' : 'text-slate-500'">
                {{ displayValue || placeholder }}
            </span>
            <div class="flex items-center gap-1.5 shrink-0">
                <span
                    v-if="modelValue"
                    @click="clearDate"
                    class="text-slate-500 hover:text-slate-300 transition-colors cursor-pointer rounded p-0.5"
                >
                    <AppIcon name="X" size="11" />
                </span>
                <AppIcon name="Calendar" size="14" class="text-slate-500" />
            </div>
        </button>

        <!-- Error / Hint -->
        <p v-if="error" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
            <AppIcon name="AlertCircle" size="11" />{{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </div>

    <!-- ─── Calendar Panel — Teleported to <body> to escape overflow:hidden ─── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150 origin-top"
            enter-from-class="opacity-0 scale-y-95"
            enter-to-class="opacity-100 scale-y-100"
            leave-active-class="transition ease-in duration-100 origin-top"
            leave-from-class="opacity-100 scale-y-100"
            leave-to-class="opacity-0 scale-y-95"
        >
            <div v-if="isOpen" ref="panelRef" :style="panelStyle">
                <div class="bg-[#0D1017] border border-[#232936] rounded-xl shadow-2xl shadow-black/60 overflow-hidden">

                    <!-- Month / Year Navigation -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-[#232936]">
                        <button
                            type="button"
                            @click="prevMonth"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-[#1a2030] hover:text-slate-200 transition-colors cursor-pointer"
                        >
                            <AppIcon name="ChevronLeft" size="15" />
                        </button>

                        <span class="text-sm font-bold text-slate-100 tracking-wide select-none">
                            {{ calendarTitle }}
                        </span>

                        <button
                            type="button"
                            @click="nextMonth"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-[#1a2030] hover:text-slate-200 transition-colors cursor-pointer"
                        >
                            <AppIcon name="ChevronRight" size="15" />
                        </button>
                    </div>

                    <!-- Day Abbreviation Headers -->
                    <div class="grid grid-cols-7 px-3 pt-3 pb-1">
                        <div
                            v-for="abbr in DAY_ABBREVS"
                            :key="abbr"
                            class="text-center text-[10px] font-bold text-slate-600 uppercase tracking-wider py-1 select-none"
                        >
                            {{ abbr }}
                        </div>
                    </div>

                    <!-- Day Grid -->
                    <div class="grid grid-cols-7 px-3 pb-2 gap-y-0.5">
                        <button
                            v-for="(day, idx) in calendarDays"
                            :key="idx"
                            type="button"
                            :disabled="!day"
                            @click="selectDay(day)"
                            :class="[
                                'flex items-center justify-center h-8 w-full rounded-lg text-xs font-medium transition-all duration-100 select-none',
                                !day
                                    ? 'cursor-default pointer-events-none'
                                    : isSelected(day)
                                        ? 'bg-primary text-white font-bold cursor-pointer shadow-md shadow-primary/30 hover:bg-primary-hover'
                                        : isToday(day)
                                            ? 'text-primary border border-primary/50 cursor-pointer hover:bg-primary/20 font-bold'
                                            : 'text-slate-300 cursor-pointer hover:bg-[#1E2535] hover:text-slate-100',
                            ]"
                        >
                            {{ day ?? '' }}
                        </button>
                    </div>

                    <!-- Today Shortcut -->
                    <div class="px-3 py-2 border-t border-[#232936]">
                        <button
                            type="button"
                            @click="goToday"
                            class="w-full text-center text-xs text-primary hover:text-white font-semibold py-1.5 rounded-lg hover:bg-primary/90 transition-all duration-150 cursor-pointer"
                        >
                            Today
                        </button>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>
