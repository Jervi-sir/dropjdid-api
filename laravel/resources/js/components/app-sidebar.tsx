import * as React from "react"
import {
  IconCamera,
  IconChartBar,
  IconDashboard,
  IconDatabase,
  IconFileAi,
  IconFileDescription,
  IconFileWord,
  IconFolder,
  IconHelp,
  IconInnerShadowTop,
  IconListDetails,
  IconReport,
  IconSearch,
  IconSettings,
  IconUsers,
  IconWallet,
  IconBuildingStore,
  IconFriends,
} from "@tabler/icons-react"

import { NavDocuments } from "@/components/nav-documents"
import { NavMain } from "@/components/nav-main"
import { NavSecondary } from "@/components/nav-secondary"
import { NavUser } from "@/components/nav-user"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"
import ListDropsController from "@/actions/App/Http/Controllers/Admin/Drops/ListDropsController"
import ListFriendshipsController from "@/actions/App/Http/Controllers/Admin/Friendships/ListFriendshipsController"
import ListProductsController from "@/actions/App/Http/Controllers/Admin/Products/ListProductsController"
import ListUsersController from "@/actions/App/Http/Controllers/Admin/Users/ListUsersController"
import ListStoresController from "@/actions/App/Http/Controllers/Admin/Stores/ListStoresController"
import ListStoreWalletsController from "@/actions/App/Http/Controllers/Admin/StoreWallets/ListStoreWalletsController"
import ListLabelsController from "@/actions/App/Http/Controllers/Admin/Labels/ListLabelsController"
import ListWalletsController from "@/actions/App/Http/Controllers/Admin/Wallets/ListWalletsController"
import AdminDashboardController from "@/actions/App/Http/Controllers/Admin/Dashboard/AdminDashboardController"
import { Link } from "@inertiajs/react"

const data = {
  user: {
    name: "Dropjdid",
    email: "m@example.com",
    avatar: "/avatars/shadcn.jpg",
  },
  navMain: [
    {
      title: "Drops",
      url: ListDropsController.url(),
      icon: IconDashboard,
    },
    {
      title: "Labels",
      url: ListLabelsController.url(),
      icon: IconListDetails,
    },
    {
      title: "Products",
      url: ListProductsController.url(),
      icon: IconChartBar,
    },
    {
      title: "Stores",
      url: ListStoresController.url(),
      icon: IconFolder,
    },
    {
      title: "Store Wallets",
      url: ListStoreWalletsController.url(),
      icon: IconBuildingStore,
    },
    {
      title: "Users",
      url: ListUsersController.url(),
      icon: IconUsers,
    },
    {
      title: "Wallets",
      url: ListWalletsController.url(),
      icon: IconWallet,
    },
    {
      title: "Friendships",
      url: ListFriendshipsController.url(),
      icon: IconFriends,
    },
  ],
  navClouds: [
    {
      title: "Capture",
      icon: IconCamera,
      isActive: true,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
    {
      title: "Proposal",
      icon: IconFileDescription,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
    {
      title: "Prompts",
      icon: IconFileAi,
      url: "#",
      items: [
        {
          title: "Active Proposals",
          url: "#",
        },
        {
          title: "Archived",
          url: "#",
        },
      ],
    },
  ],
  navSecondary: [
    {
      title: "Settings",
      url: "#",
      icon: IconSettings,
    },
    {
      title: "Get Help",
      url: "#",
      icon: IconHelp,
    },
    {
      title: "Search",
      url: "#",
      icon: IconSearch,
    },
  ],
  documents: [
    {
      name: "Data Library",
      url: "#",
      icon: IconDatabase,
    },
    {
      name: "Reports",
      url: "#",
      icon: IconReport,
    },
    {
      name: "Word Assistant",
      url: "#",
      icon: IconFileWord,
    },
  ],
}

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  return (
    <Sidebar collapsible="offcanvas" {...props}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              asChild
              className="data-[slot=sidebar-menu-button]:p-1.5!"
            >
              <Link href={AdminDashboardController.url()}>
                <IconInnerShadowTop className="size-5!" />
                <span className="text-base font-semibold">Acme Inc.</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={data.navMain} />
        <NavDocuments items={data.documents} />
        <NavSecondary items={data.navSecondary} className="mt-auto" />
      </SidebarContent>
      <SidebarFooter>
        <NavUser user={data.user} />
      </SidebarFooter>
    </Sidebar>
  )
}
