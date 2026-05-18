import { IconLayoutDashboard, IconMail, type Icon } from "@tabler/icons-react"

import { Button } from "@/components/ui/button"
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"
import { ChartBarIcon } from "lucide-react"
import { Link } from "@inertiajs/react"
import { useCurrentUrl } from "@/hooks/use-current-url"
import AdminDashboardController from "@/actions/App/Http/Controllers/Admin/Dashboard/AdminDashboardController"

export function NavMain({
  items,
}: {
  items: {
    title: string
    url: string
    icon?: Icon
  }[]
}) {
  const { isCurrentOrParentUrl } = useCurrentUrl()

  return (
    <SidebarGroup>
      <SidebarGroupContent className="flex flex-col gap-2">
        <SidebarMenu>
          <SidebarMenuItem className="flex items-center gap-2">
            <Link href={AdminDashboardController.url()}
              className="flex-1"
            >
              <SidebarMenuButton
                tooltip="Dashboard"
                isActive={isCurrentOrParentUrl(AdminDashboardController.url())}
              >
                <IconLayoutDashboard />
                <span>Dashboard</span>
              </SidebarMenuButton>
            </Link>
            <Button
              size="icon"
              className="size-8 group-data-[collapsible=icon]:opacity-0"
              variant="outline"
            >
              <ChartBarIcon />
              <span className="sr-only">Quick Stats</span>
            </Button>
          </SidebarMenuItem>
        </SidebarMenu>
        <SidebarMenu>
          {items.map((item) => (
            <Link key={item.title} href={item.url}>
              <SidebarMenuItem>
                <SidebarMenuButton tooltip={item.title} isActive={isCurrentOrParentUrl(item.url)}>
                  {item.icon && <item.icon />}
                  <span>{item.title}</span>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </Link>
          ))}
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  )
}

