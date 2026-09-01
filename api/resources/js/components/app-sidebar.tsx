import { Link } from '@inertiajs/react';
import { BookOpen, Calendar, FolderGit2, LayoutGrid, Package, Store, Truck, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Events',
        href: '/admin/events',
        icon: Calendar,
    },
    {
        title: 'Orders',
        href: admin.orders.index(),
        icon: Package,
    },
    {
        title: 'Supply Requests',
        href: '/admin/supply-requests',
        icon: Truck,
    },
    {
        title: 'Creator Requests',
        href: admin.creators.requests.index(),
        icon: Users,
    },
    {
        title: 'Store Requests',
        href: admin.sgm.requests.index(),
        icon: Store,
    },
    {
        title: 'Stores Directory',
        href: admin.sgm.stores.index(),
        icon: Store,
    },
    {
        title: 'Products Review',
        href: '/admin/sgm/products',
        icon: Package,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
