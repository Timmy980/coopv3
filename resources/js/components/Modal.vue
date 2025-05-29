<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

const close = () => {
    emit('close');
};
</script>

<template>
    <div v-show="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <div class="fixed inset-0 transform transition-all" @click="close">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="transform transition-all sm:w-full sm:max-w-md sm:mx-auto">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div @click.stop>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
