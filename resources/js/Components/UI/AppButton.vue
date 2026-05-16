<script setup>
defineProps({
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
});

const variantClasses = {
    primary: 'bg-[#6366F1] text-white hover:bg-[#4F46E5] focus:ring-[#6366F1]',
    secondary: 'bg-[#161B26] text-slate-200 border border-[#232936] hover:bg-[#232936] focus:ring-slate-500',
    danger: 'bg-[#F43F5E] text-white hover:bg-[#E11D48] focus:ring-red-500',
    success: 'bg-[#10B981] text-white hover:bg-[#059669] focus:ring-green-500',
    ghost: 'bg-transparent text-slate-400 hover:bg-[#161B26] hover:text-white focus:ring-slate-500',
};

const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer',
            variantClasses[variant],
            sizeClasses[size],
        ]"
    >
        <svg v-if="loading" class="animate-spin -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <slot />
    </button>
</template>
