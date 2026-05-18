import * as React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ChartAreaInteractive } from "@/components/chart-area-interactive";
import { SidebarInset } from "@/components/ui/sidebar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableCell,
  TableHead,
} from "@/components/ui/table";
import {
  IconUsers,
  IconBuildingStore,
  IconShoppingBag,
  IconWallet,
  IconArrowUpRight,
  IconFriends,
  IconMessage2,
  IconClock,
  IconChevronRight,
  IconAlertCircle,
  IconCircleCheck,
  IconTrendingUp,
} from '@tabler/icons-react';

interface DashboardProps {
  stats: {
    users: {
      total: number;
      active: number;
    };
    stores: {
      total: number;
      pending: number;
    };
    products: {
      total: number;
      pending: number;
    };
    drops: {
      total: number;
    };
    finances: {
      total_user_balance: number;
      total_store_balance: number;
      pending_user_withdrawals: number;
      pending_store_withdrawals: number;
    };
    social: {
      friendships: number;
      conversations: number;
    };
  };
  recentPendingStores: Array<{
    id: number;
    store_name: string;
    phone_number: string;
    created_at: string;
    owner: {
      id: number;
      full_name: string;
      email: string;
    } | null;
  }>;
  recentStoreWithdrawals: Array<{
    id: number;
    store_id: number;
    store_name: string;
    amount: number;
    method: number;
    status: number;
    created_at: string;
  }>;
}

export default function Page({ stats, recentPendingStores = [], recentStoreWithdrawals = [] }: DashboardProps) {
  const getMethodLabel = (method: number) => {
    switch (method) {
      case 0: return 'BaridiMob';
      case 1: return 'CCP';
      case 2: return 'Bank';
      case 3: return 'Cash';
      default: return 'Other';
    }
  };

  const getStatusBadge = (status: number) => {
    switch (status) {
      case 0:
        return (
          <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100/80 border border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30 text-[10px] py-0 px-2 font-medium">
            Identity Check
          </Badge>
        );
      case 1:
        return (
          <Badge className="bg-orange-50 text-orange-700 hover:bg-orange-100/80 border border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/30 text-[10px] py-0 px-2 font-medium">
            Pending
          </Badge>
        );
      default:
        return <Badge variant="outline" className="text-[10px] py-0 px-2 font-medium">Request</Badge>;
    }
  };

  // Format currency in DZD
  const formatDZD = (value: number) => {
    return new Intl.NumberFormat('en-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 2,
    }).format(value);
  };

  return (
    <SidebarInset>
      <Head title="Admin Dashboard" />

      <div className="flex flex-1 flex-col">
        <div className="@container/main flex flex-1 flex-col gap-6 py-6 px-4 lg:px-8">
          
          {/* Header Block */}
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-indigo-500/5 via-violet-500/5 to-purple-500/5 p-6 rounded-3xl border border-indigo-500/10 shadow-xs backdrop-blur-xs">
            <div className="flex flex-col gap-1">
              <h1 className="text-2xl lg:text-3xl font-extrabold tracking-tight bg-gradient-to-r from-foreground via-foreground/90 to-muted-foreground bg-clip-text text-transparent">
                Systems Command Center
              </h1>
              <p className="text-xs text-muted-foreground font-medium flex items-center gap-1.5 mt-1">
                <span className="size-2 rounded-full bg-emerald-500 animate-pulse shrink-0" />
                All services fully operational • Active real-time analytics
              </p>
            </div>
            <div className="flex items-center gap-2 text-xs font-bold text-muted-foreground bg-muted/65 px-4 py-2.5 rounded-xl border">
              <IconClock className="size-4 text-indigo-500" />
              <span>{new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</span>
            </div>
          </div>

          {/* Dynamic KPI Cards Row */}
          <div className="grid grid-cols-1 gap-4 @xl/main:grid-cols-2 @5xl/main:grid-cols-4">
            
            {/* Shoppers KPI */}
            <Card className="bg-gradient-to-t from-emerald-500/5 to-card border shadow-xs relative overflow-hidden group">
              <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <IconUsers className="size-24 text-emerald-500" />
              </div>
              <CardHeader className="pb-2">
                <CardDescription className="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">Shoppers Directory</CardDescription>
                <CardTitle className="text-3xl font-black tracking-tight mt-1 flex items-baseline gap-2">
                  {stats.users.total}
                  <span className="text-xs font-bold text-muted-foreground">total</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="pb-4">
                <div className="flex items-center gap-2 mt-2">
                  <span className="flex items-center gap-1 text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                    {stats.users.active} Active
                  </span>
                  <span className="text-[10px] text-muted-foreground font-medium">95.4% retention</span>
                </div>
              </CardContent>
            </Card>

            {/* Merchant Stores KPI */}
            <Card className="bg-gradient-to-t from-orange-500/5 to-card border shadow-xs relative overflow-hidden group">
              <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <IconBuildingStore className="size-24 text-orange-500" />
              </div>
              <CardHeader className="pb-2">
                <CardDescription className="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">Seller Profiles</CardDescription>
                <CardTitle className="text-3xl font-black tracking-tight mt-1 flex items-baseline gap-2">
                  {stats.stores.total}
                  <span className="text-xs font-bold text-muted-foreground">total</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="pb-4">
                <div className="flex items-center gap-2 mt-2">
                  {stats.stores.pending > 0 ? (
                    <span className="flex items-center gap-1 text-[11px] font-extrabold text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-500/20 animate-pulse">
                      {stats.stores.pending} Pending Audit
                    </span>
                  ) : (
                    <span className="flex items-center gap-1 text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                      All Approved
                    </span>
                  )}
                  <span className="text-[10px] text-muted-foreground font-medium">active stores</span>
                </div>
              </CardContent>
            </Card>

            {/* Catalog Products KPI */}
            <Card className="bg-gradient-to-t from-indigo-500/5 to-card border shadow-xs relative overflow-hidden group">
              <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <IconShoppingBag className="size-24 text-indigo-500" />
              </div>
              <CardHeader className="pb-2">
                <CardDescription className="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">Catalog Inventory</CardDescription>
                <CardTitle className="text-3xl font-black tracking-tight mt-1 flex items-baseline gap-2">
                  {stats.products.total}
                  <span className="text-xs font-bold text-muted-foreground">items</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="pb-4">
                <div className="flex items-center gap-2 mt-2">
                  {stats.products.pending > 0 ? (
                    <span className="flex items-center gap-1 text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-500/20">
                      {stats.products.pending} Awaiting Audit
                    </span>
                  ) : (
                    <span className="flex items-center gap-1 text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                      Fully Audited
                    </span>
                  )}
                  <span className="text-[10px] text-muted-foreground font-medium">listings verified</span>
                </div>
              </CardContent>
            </Card>

            {/* Financial Ledger KPI */}
            <Card className="bg-gradient-to-t from-violet-500/5 to-card border shadow-xs relative overflow-hidden group">
              <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <IconWallet className="size-24 text-violet-500" />
              </div>
              <CardHeader className="pb-2">
                <CardDescription className="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">Merchant Treasury</CardDescription>
                <CardTitle className="text-2xl font-black tracking-tight mt-1 truncate">
                  {formatDZD(stats.finances.total_store_balance)}
                </CardTitle>
              </CardHeader>
              <CardContent className="pb-4">
                <div className="flex items-center gap-2 mt-1">
                  <span className="text-[10px] font-bold text-muted-foreground/95 bg-muted px-2 py-0.5 rounded border">
                    Shoppers Wallet: {formatDZD(stats.finances.total_user_balance)}
                  </span>
                </div>
              </CardContent>
            </Card>

          </div>

          {/* Interactive Charts & Financial Health split grid */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {/* Interactive Growth chart (2 columns) */}
            <div className="lg:col-span-2">
              <ChartAreaInteractive />
            </div>

            {/* Financial Status & Payout Health (1 column) */}
            <div className="bg-card border rounded-3xl p-6 shadow-xs flex flex-col justify-between gap-6">
              <div className="flex flex-col gap-4">
                <div className="flex items-center justify-between border-b pb-3">
                  <div className="flex items-center gap-2">
                    <IconWallet className="size-5 text-indigo-500" />
                    <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Treasury & Payouts</h3>
                  </div>
                  <Badge variant="outline" className="text-[10px] font-extrabold uppercase">DZD System Ledger</Badge>
                </div>

                <div className="flex flex-col gap-4 mt-2">
                  {/* Store Wallet Progress bar */}
                  <div className="flex flex-col gap-1.5">
                    <div className="flex justify-between text-xs font-bold">
                      <span className="text-muted-foreground">Merchant Balance</span>
                      <span className="text-foreground">{formatDZD(stats.finances.total_store_balance)}</span>
                    </div>
                    <div className="h-2 w-full bg-muted rounded-full overflow-hidden">
                      <div className="h-full bg-gradient-to-r from-violet-500 to-indigo-600 rounded-full" style={{ width: '75%' }} />
                    </div>
                  </div>

                  {/* User Wallet Progress bar */}
                  <div className="flex flex-col gap-1.5">
                    <div className="flex justify-between text-xs font-bold">
                      <span className="text-muted-foreground">Shopper Deposits</span>
                      <span className="text-foreground">{formatDZD(stats.finances.total_user_balance)}</span>
                    </div>
                    <div className="h-2 w-full bg-muted rounded-full overflow-hidden">
                      <div className="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style={{ width: '40%' }} />
                    </div>
                  </div>
                </div>

                {/* Queue Indicators */}
                <div className="grid grid-cols-2 gap-3 mt-4">
                  <div className="bg-muted/50 border p-3 rounded-2xl flex flex-col gap-1">
                    <span className="text-[10px] font-bold text-muted-foreground/80 uppercase">Store Payouts</span>
                    <span className="text-lg font-black text-foreground flex items-center gap-1.5">
                      {stats.finances.pending_store_withdrawals}
                      {stats.finances.pending_store_withdrawals > 0 && (
                        <span className="size-2 rounded-full bg-orange-500 animate-pulse" />
                      )}
                    </span>
                    <span className="text-[9px] text-muted-foreground">requests queued</span>
                  </div>

                  <div className="bg-muted/50 border p-3 rounded-2xl flex flex-col gap-1">
                    <span className="text-[10px] font-bold text-muted-foreground/80 uppercase">Shopper Payouts</span>
                    <span className="text-lg font-black text-foreground flex items-center gap-1.5">
                      {stats.finances.pending_user_withdrawals}
                      {stats.finances.pending_user_withdrawals > 0 && (
                        <span className="size-2 rounded-full bg-orange-500 animate-pulse" />
                      )}
                    </span>
                    <span className="text-[9px] text-muted-foreground">requests queued</span>
                  </div>
                </div>
              </div>

              {/* Social Interactivity Summary Footer */}
              <div className="flex items-center gap-3 border-t pt-4 bg-muted/30 -mx-6 -mb-6 p-6 rounded-b-3xl">
                <div className="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                  <IconFriends className="size-4 text-indigo-500" />
                  <span>{stats.social.friendships} Friendships</span>
                </div>
                <div className="size-1 rounded-full bg-muted-foreground/30" />
                <div className="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                  <IconMessage2 className="size-4 text-indigo-500" />
                  <span>{stats.social.conversations} DMs</span>
                </div>
              </div>

            </div>

          </div>

          {/* Actionable Pending Operations tables grid */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {/* Table 1: Pending Merchant Registrations */}
            <div className="bg-card border rounded-3xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center justify-between border-b pb-3">
                <div className="flex items-center gap-2">
                  <IconBuildingStore className="size-5 text-orange-500" />
                  <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Stores Awaiting Audit</h3>
                </div>
                <Badge variant="outline" className="text-[10px] font-extrabold bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20">{recentPendingStores.length} queued</Badge>
              </div>

              {recentPendingStores.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-muted/30">
                        <TableHead className="text-[10px] font-extrabold uppercase py-2">Store Info</TableHead>
                        <TableHead className="text-[10px] font-extrabold uppercase py-2">Owner Profile</TableHead>
                        <TableHead className="text-[10px] font-extrabold uppercase py-2 text-right">Review</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {recentPendingStores.map((store) => (
                        <TableRow key={store.id} className="hover:bg-muted/30">
                          <TableCell className="py-2.5">
                            <div className="flex flex-col">
                              <span className="font-bold text-xs text-foreground">{store.store_name}</span>
                              <span className="text-[10px] text-muted-foreground mt-0.5">{store.phone_number || 'No contact'}</span>
                            </div>
                          </TableCell>
                          <TableCell className="py-2.5">
                            <div className="flex flex-col">
                              <span className="font-semibold text-xs text-foreground/80">{store.owner?.full_name || 'N/A'}</span>
                              <span className="text-[10px] text-muted-foreground mt-0.5">{store.owner?.email || ''}</span>
                            </div>
                          </TableCell>
                          <TableCell className="py-2.5 text-right">
                            <Button size="xs" variant="outline" className="h-7 rounded-lg group" asChild>
                              <Link href={`/admin/stores/${store.id}`}>
                                Audit <IconChevronRight className="size-3 ml-1 group-hover:translate-x-0.5 transition-transform" />
                              </Link>
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              ) : (
                <div className="flex flex-col items-center justify-center py-8 text-center gap-2">
                  <IconCircleCheck className="size-8 text-emerald-500" />
                  <span className="text-xs font-bold text-foreground mt-1">Pending Queue Clear!</span>
                  <span className="text-[10px] text-muted-foreground">All merchant stores are audited and live.</span>
                </div>
              )}
            </div>

            {/* Table 2: Pending Merchant Withdrawal Requests */}
            <div className="bg-card border rounded-3xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center justify-between border-b pb-3">
                <div className="flex items-center gap-2">
                  <IconWallet className="size-5 text-indigo-500" />
                  <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Merchant Withdrawal Requests</h3>
                </div>
                <Badge variant="outline" className="text-[10px] font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20">{recentStoreWithdrawals.length} queued</Badge>
              </div>

              {recentStoreWithdrawals.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-muted/30">
                        <TableHead className="text-[10px] font-extrabold uppercase py-2">Merchant</TableHead>
                        <TableHead className="text-[10px] font-extrabold uppercase py-2">Details</TableHead>
                        <TableHead className="text-[10px] font-extrabold uppercase py-2 text-right">Payout</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {recentStoreWithdrawals.map((req) => (
                        <TableRow key={req.id} className="hover:bg-muted/30">
                          <TableCell className="py-2.5">
                            <div className="flex flex-col">
                              <span className="font-bold text-xs text-foreground">{req.store_name || 'Merchant'}</span>
                              <span className="text-[10px] text-muted-foreground mt-0.5 flex items-center gap-1">
                                <IconClock className="size-3 text-indigo-500" />
                                {new Date(req.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="py-2.5">
                            <div className="flex flex-col gap-0.5">
                              <span className="font-extrabold text-xs text-indigo-600 dark:text-indigo-400">{formatDZD(req.amount)}</span>
                              <div className="flex items-center gap-1.5 mt-0.5">
                                <span className="text-[10px] text-muted-foreground/80 font-bold uppercase">{getMethodLabel(req.method)}</span>
                                {getStatusBadge(req.status)}
                              </div>
                            </div>
                          </TableCell>
                          <TableCell className="py-2.5 text-right">
                            <Button size="xs" variant="outline" className="h-7 rounded-lg group" asChild>
                              <Link href={`/admin/store-wallets/${req.store_id}`}>
                                Process <IconChevronRight className="size-3 ml-1 group-hover:translate-x-0.5 transition-transform" />
                              </Link>
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              ) : (
                <div className="flex flex-col items-center justify-center py-8 text-center gap-2">
                  <IconCircleCheck className="size-8 text-emerald-500" />
                  <span className="text-xs font-bold text-foreground mt-1">Payout Queue Clear!</span>
                  <span className="text-[10px] text-muted-foreground">All merchant payouts have been processed.</span>
                </div>
              )}
            </div>

          </div>

        </div>
      </div>
    </SidebarInset>
  );
}
