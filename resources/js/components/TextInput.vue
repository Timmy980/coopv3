<script setup lang="ts">
import { ref } from 'vue';

const input = ref<HTMLInputElement | null>(null);

defineProps<{
    modelValue: string;
    type?: string;
    required?: boolean;
    disabled?: boolean;
}>();

defineEmits(['update:modelValue']);

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>

<template>
    <input
        ref="input"
        :type="type"
        class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        :required="required"
        :disabled="disabled"
    >
</template>
