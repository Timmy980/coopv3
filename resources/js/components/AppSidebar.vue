<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Shield, Landmark, Users, Download } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = page.props.auth.user;

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },


    ...(user?.roles.includes('admin') ? [{
        title: 'Roles',
        href: route('roles.index'),
        icon: Shield,
    },
    {
        title: 'Members Management',
        href: route('user-approvals.index'),
        icon: Users,
    },
    {
        title: 'Cooperative Accounts',
        href: route('cooperative_accounts.index'),
        icon: Landmark,
    },
    {
        title: 'Account Types',
        href: route('account-types.index'),
        icon: Folder,
    },
    {
        title: 'Member Accounts',
        href: route('member_accounts.index'),
        icon: Folder
    },

] : []),

{
    title: 'Savings Management',
    href: route('savings.index'),
    icon: Folder
},
];

const footerNavItems: NavItem[] = [
    
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
