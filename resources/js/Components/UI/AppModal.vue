<script setup>
defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'md' },
});

const emit = defineEmits(['close']);

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="emit('close')" />
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="show"
                        :class="['relative bg-card-bg rounded-xl shadow-2xl w-full overflow-hidden text-slate-200 border border-border', maxWidthClass[maxWidth]]"
                    >
                        <div v-if="title" class="px-6 py-4 border-b border-border">
                            <h3 class="text-lg font-semibold text-slate-50">{{ title }}</h3>
                            <button @click="emit('close')" class="absolute top-4 right-4 text-slate-500 hover:text-slate-300 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-6">
                            <slot />
                        </div>
                        <div v-if="$slots.footer" class="px-6 py-4 border-t border-border bg-page-bg/50 flex justify-end gap-3">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
