<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error'].includes(value),
    },
    message: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div
        v-show="show"
        :class="{
            'fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50': true,
        }"
    >
        <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
            <div
                :class="{
                    'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden': true,
                    'border-l-4': true,
                    'border-green-400': type === 'success',
                    'border-red-400': type === 'error',
                }"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg
                                v-if="type === 'success'"
                                class="h-6 w-6 text-green-400"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <svg
                                v-if="type === 'error'"
                                class="h-6 w-6 text-red-400"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">
                                {{ message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
