<script setup>
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppDatePicker from '@/Components/UI/AppDatePicker.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    type: { type: String, default: 'text' },
    error: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    prefix: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);
</script>

<template>
    <!-- Date type: delegate to fully custom date picker -->
    <AppDatePicker
        v-if="type === 'date'"
        :modelValue="String(modelValue)"
        :label="label"
        :error="error"
        :required="required"
        :disabled="disabled"
        :placeholder="placeholder || 'Select date'"
        :hint="hint"
        @update:modelValue="emit('update:modelValue', $event)"
        @change="emit('change', $event)"
    />

    <!-- All other input types -->
    <div v-else>
        <label v-if="label" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            {{ label }}<span v-if="required" class="text-rose-400 ml-0.5">*</span>
        </label>

        <div class="relative flex items-center">
            <span
                v-if="prefix"
                class="absolute left-3 text-slate-500 text-sm font-medium select-none pointer-events-none z-10"
            >
                {{ prefix }}
            </span>

            <input
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                @input="emit('update:modelValue', $event.target.value)"
                @change="emit('change', $event.target.value)"
                :class="[
                    'app-input w-full rounded-lg border text-sm transition-all duration-200',
                    'focus:outline-none focus:ring-1',
                    'placeholder-slate-600 text-slate-100',
                    prefix ? 'pl-7 pr-3 py-2.5' : 'px-3 py-2.5',
                    error
                        ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-rose-400/30'
                        : 'border-border bg-card-bg hover:border-slate-600 focus:border-primary/60 focus:ring-primary/20',
                    disabled ? 'opacity-50 cursor-not-allowed text-slate-500' : '',
                ]"
            />
        </div>

        <p v-if="error" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
            <AppIcon name="AlertCircle" size="11" />{{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </div>
</template>
