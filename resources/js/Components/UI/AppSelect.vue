<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    options: { type: Array, default: () => [] },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Select an option' },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-slate-300 mb-1">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            :value="modelValue"
            :required="required"
            :disabled="disabled"
            @change="emit('update:modelValue', $event.target.value)"
            :class="[
                'w-full px-3 py-2 border rounded-lg text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent appearance-none text-slate-100',
                error ? 'border-red-500 bg-red-900/20' : 'border-border bg-page-bg',
                disabled ? 'bg-card-bg text-slate-500 cursor-not-allowed' : '',
            ]"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option v-for="opt in options" :key="opt.value ?? opt.id" :value="opt.value ?? opt.id">
                {{ opt.label ?? opt.name }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
