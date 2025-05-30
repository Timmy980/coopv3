<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Get the full profile picture URL
const profilePictureUrl = computed(() => 
    props.user.profile_picture 
        ? `${usePage().props.appUrl}/storage/${props.user.profile_picture}`
        : ''
);

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.user.profile_picture && props.user.profile_picture !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="profilePictureUrl" :alt="user.first_name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(user.first_name + ' ' + user.last_name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.first_name + ' ' + user.last_name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
