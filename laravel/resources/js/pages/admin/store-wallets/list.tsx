import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ListStoreWalletsController from '@/actions/App/Http/Controllers/Admin/StoreWallets/ListStoreWalletsController';
import ShowStoreWalletController from '@/actions/App/Http/Controllers/Admin/StoreWallets/ShowStoreWalletController';
import {
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetFooter,
} from '@/components/ui/sheet';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import {
  IconSearch,
  IconWallet,
  IconCircleCheck,
  IconAlertTriangle,
  IconShieldCheck,
  IconArrowRight,
  IconLoader2,
  IconRefresh,
  IconScale,
  IconChevronLeft,
  IconChevronRight,
  IconBuildingStore,
} from '@tabler/icons-react';

interface Store {
  id: number;
  store_name: string;
  phone_number: string;
  logo: string | null;
}

interface StoreWallet {
  id: number;
  store: Store | null;
  type: string;
  type_raw: number;
  balance: string;
  pending_balance: string;
  is_identity_verified: boolean;
  status: string;
  status_raw: number;
  currency: string;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedStoreWallets {
  data: StoreWallet[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface ListStoreWalletsProps {
  wallets: PaginatedStoreWallets;
  kpis: {
    total_balance: string;
    total_pending_balance: string;
    verified_identity_count: number;
    blocked_count: number;
    total_count: number;
  };
  filters: {
    search: string;
    status: string;
    type: string;
    is_identity_verified: string;
    per_page: number;
  };
}

export default function ListStoreWallets({ wallets, kpis, filters }: ListStoreWalletsProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [statusFilter, setStatusFilter] = React.useState(filters.status || 'all');
  const [typeFilter, setTypeFilter] = React.useState(filters.type || 'all');
  const [verifiedFilter, setVerifiedFilter] = React.useState(filters.is_identity_verified || 'all');

  // Sheet Preview States
  const [previewOpen, setPreviewOpen] = React.useState(false);
  const [previewWallet, setPreviewWallet] = React.useState<StoreWallet | null>(null);
  const [loadingWallet, setLoadingWallet] = React.useState(false);

  // Form hook for saving wallet status and identity check
  const { data, setData, put, processing, errors, clearErrors } = useForm({
    status: 0,
    is_identity_verified: false,
  });

  // Fetch individual store wallet details for preview
  const handleOpenPreview = async (wallet: StoreWallet) => {
    setPreviewOpen(true);
    setLoadingWallet(true);
    setPreviewWallet(wallet);
    clearErrors();

    setData({
      status: wallet.status_raw,
      is_identity_verified: wallet.is_identity_verified,
    });

    try {
      const response = await fetch(ShowStoreWalletController.show.url(wallet.id), {
        headers: { Accept: 'application/json' },
      });
      if (response.ok) {
        const result = await response.json();
        setPreviewWallet(result.store_wallet);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoadingWallet(false);
    }
  };

  const handleUpdateWallet = (e: React.FormEvent) => {
    e.preventDefault();
    if (!previewWallet) return;

    put(ShowStoreWalletController.update.url(previewWallet.id), {
      preserveScroll: true,
      onSuccess: () => {
        setPreviewOpen(false);
      },
    });
  };

  const applyFilters = () => {
    router.get(
      '/admin/store-wallets',
      {
        search: searchTerm || undefined,
        status: statusFilter !== 'all' ? statusFilter : undefined,
        type: typeFilter !== 'all' ? typeFilter : undefined,
        is_identity_verified: verifiedFilter !== 'all' ? verifiedFilter : undefined,
      },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  };

  React.useEffect(() => {
    const timer = setTimeout(() => {
      applyFilters();
    }, 450);
    return () => clearTimeout(timer);
  }, [searchTerm, statusFilter, typeFilter, verifiedFilter]);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'verified':
        return <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">Verified</Badge>;
      case 'pending':
        return <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">Pending</Badge>;
      case 'blocked':
        return <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">Blocked</Badge>;
      case 'rejected':
        return <Badge className="bg-red-50 text-red-700 hover:bg-red-100 border-red-500/20 border dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30">Rejected</Badge>;
      default:
        return <Badge variant="outline" className="bg-slate-50 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200">New</Badge>;
    }
  };

  const getTypeBadge = (type: string) => {
    if (type === 'refund') {
      return <Badge className="bg-sky-50 text-sky-700 border-sky-300 dark:bg-sky-500/10 dark:text-sky-400">Refund</Badge>;
    }
    return <Badge className="bg-indigo-50 text-indigo-700 border-indigo-300 dark:bg-indigo-500/10 dark:text-indigo-400">Balance</Badge>;
  };

  return (
    <>
      <Head title="Store Wallets Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Page Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Store Wallets</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Audit vendor store ledger accounts, freeze balances, check identity documents, and approve payouts.
            </p>
          </div>
          <Button variant="outline" size="sm" onClick={applyFilters} className="self-start md:self-auto gap-1">
            <IconRefresh className="size-4" />
            <span>Sync Ledger</span>
          </Button>
        </div>

        {/* KPI Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          
          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
              <IconWallet className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Total Balance</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.total_balance} DZD</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
              <IconScale className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Pending Payouts</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.total_pending_balance} DZD</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
              <IconShieldCheck className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Verified Identity</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.verified_identity_count} Stores</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
              <IconAlertTriangle className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Frozen Wallets</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.blocked_count} Frozen</span>
            </div>
          </div>
        </div>

        {/* Filter bar and Table */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">
          
          <div className="p-4 border-b flex flex-col lg:flex-row items-center gap-4 bg-muted/15">
            <div className="relative w-full lg:max-w-xs shrink-0">
              <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search store name or phone..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9 h-10 w-full bg-background"
              />
            </div>

            <div className="flex flex-wrap items-center gap-3 w-full lg:justify-end">
              <div className="flex items-center gap-1.5 shrink-0">
                <span className="text-xs font-semibold text-muted-foreground">Type:</span>
                <Select value={typeFilter} onValueChange={setTypeFilter}>
                  <SelectTrigger className="w-[120px] h-9 bg-background">
                    <SelectValue placeholder="Select type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Types</SelectItem>
                    <SelectItem value="0">Balance</SelectItem>
                    <SelectItem value="1">Refund</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="flex items-center gap-1.5 shrink-0">
                <span className="text-xs font-semibold text-muted-foreground">Status:</span>
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-[120px] h-9 bg-background">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="0">New</SelectItem>
                    <SelectItem value="1">Pending</SelectItem>
                    <SelectItem value="2">Verified</SelectItem>
                    <SelectItem value="3">Blocked</SelectItem>
                    <SelectItem value="4">Rejected</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="flex items-center gap-1.5 shrink-0">
                <span className="text-xs font-semibold text-muted-foreground">Identity Check:</span>
                <Select value={verifiedFilter} onValueChange={setVerifiedFilter}>
                  <SelectTrigger className="w-[140px] h-9 bg-background">
                    <SelectValue placeholder="Identity Check" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Checks</SelectItem>
                    <SelectItem value="1">Verified Only</SelectItem>
                    <SelectItem value="0">Unverified Only</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>

          {/* Table Container */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/5 border-b">
                <TableRow>
                  <TableHead className="pl-6 py-4">Store Identity</TableHead>
                  <TableHead className="py-4">Type</TableHead>
                  <TableHead className="py-4 text-right">Available Balance</TableHead>
                  <TableHead className="py-4 text-right">Pending Balance</TableHead>
                  <TableHead className="py-4 text-center">Verification Status</TableHead>
                  <TableHead className="py-4 text-center">Wallet Status</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[200px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {wallets.data.length > 0 ? (
                  wallets.data.map((wallet) => (
                    <TableRow key={wallet.id} className="hover:bg-muted/5 group/row transition-colors">
                      
                      <TableCell className="pl-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="size-9 rounded-xl bg-primary/10 border text-primary flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                            {wallet.store?.logo ? (
                              <img src={wallet.store.logo} alt={wallet.store.store_name} className="w-full h-full object-cover" />
                            ) : (
                              <IconBuildingStore className="size-5 stroke-[1.8]" />
                            )}
                          </div>
                          <div className="flex flex-col gap-0.5">
                            <span className="font-extrabold text-foreground text-xs">{wallet.store?.store_name || 'N/A'}</span>
                            <span className="text-[10px] text-muted-foreground font-semibold">{wallet.store?.phone_number || 'N/A'}</span>
                          </div>
                        </div>
                      </TableCell>

                      <TableCell className="py-4">
                        {getTypeBadge(wallet.type)}
                      </TableCell>

                      <TableCell className="py-4 text-right font-extrabold text-foreground text-xs">
                        {wallet.balance} {wallet.currency}
                      </TableCell>

                      <TableCell className="py-4 text-right text-xs text-amber-600 dark:text-amber-400 font-bold">
                        {wallet.pending_balance} {wallet.currency}
                      </TableCell>

                      <TableCell className="py-4 text-center">
                        {wallet.is_identity_verified ? (
                          <div className="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-xs font-bold bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                            <IconCircleCheck className="size-3.5 stroke-[2.5]" />
                            <span>Verified</span>
                          </div>
                        ) : (
                          <div className="inline-flex items-center gap-1 text-muted-foreground text-xs font-semibold bg-muted/10 px-2 py-0.5 rounded-full">
                            <span>Unverified</span>
                          </div>
                        )}
                      </TableCell>

                      <TableCell className="py-4 text-center">
                        {getStatusBadge(wallet.status)}
                      </TableCell>

                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-2">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5 font-bold"
                            onClick={() => handleOpenPreview(wallet)}
                          >
                            Manage
                          </Button>
                          <Button variant="ghost" size="xs" className="h-7 px-2" asChild>
                            <Link href={ShowStoreWalletController.show.url(wallet.id)}>
                              Details
                            </Link>
                          </Button>
                        </div>
                      </TableCell>

                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={7} className="py-16 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconWallet className="size-12 text-muted-foreground/50 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No store wallets found</p>
                          <p className="text-xs">Adjust filter settings to query store wallets.</p>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination */}
          {wallets.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {wallets.from} to {wallets.to} of {wallets.total} store wallets
              </span>

              <div className="flex items-center gap-1.5">
                {wallets.links.map((link, idx) => {
                  const isPrev = link.label.includes('Previous');
                  const isNext = link.label.includes('Next');

                  let label = link.label;
                  if (isPrev) label = 'Previous';
                  if (isNext) label = 'Next';

                  const isDisabled = !link.url;

                  return (
                    <Link
                      key={idx}
                      href={link.url || '#'}
                      disabled={isDisabled}
                      preserveScroll
                      preserveState
                      className={`
                        inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none 
                        ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'}
                        ${link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm' : 'border border-border bg-card text-foreground'}
                      `}
                    >
                      {isPrev && <IconChevronLeft className="size-3.5 -ml-0.5" />}
                      <span>{label}</span>
                      {isNext && <IconChevronRight className="size-3.5 -mr-0.5" />}
                    </Link>
                  );
                })}
              </div>
            </div>
          )}

        </div>
      </div>

      {/* Slide-over Manager Sheet */}
      <Sheet open={previewOpen} onOpenChange={setPreviewOpen}>
        <SheetContent className="sm:max-w-md flex flex-col h-full bg-card">
          <SheetHeader className="border-b pb-4">
            <SheetTitle className="text-lg font-black text-foreground">Store Wallet Auditor</SheetTitle>
            <SheetDescription className="text-xs text-muted-foreground">
              Review store verification status, update account status, and freeze funds.
            </SheetDescription>
          </SheetHeader>

          {loadingWallet ? (
            <div className="flex-grow flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Querying store wallets...</span>
            </div>
          ) : previewWallet ? (
            <form onSubmit={handleUpdateWallet} className="flex-grow flex flex-col justify-between">
              <div className="flex flex-col p-4 gap-6 overflow-y-auto">

                <div className="flex items-center gap-4 bg-muted/20 border p-4 rounded-2xl">
                  <div className="size-12 rounded-xl bg-primary/10 border text-primary flex items-center justify-center font-extrabold text-base uppercase overflow-hidden shrink-0">
                    {previewWallet.store?.logo ? (
                      <img src={previewWallet.store.logo} alt={previewWallet.store.store_name} className="w-full h-full object-cover" />
                    ) : (
                      <IconBuildingStore className="size-6 stroke-[1.8]" />
                    )}
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="font-extrabold text-foreground text-sm">{previewWallet.store?.store_name || 'N/A'}</span>
                    <span className="text-xs text-muted-foreground font-semibold">{previewWallet.store?.phone_number || 'N/A'}</span>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="border rounded-xl p-3 flex flex-col bg-background/50">
                    <span className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Ledger Balance</span>
                    <span className="text-sm font-black text-foreground mt-0.5">{previewWallet.balance} {previewWallet.currency}</span>
                  </div>
                  <div className="border rounded-xl p-3 flex flex-col bg-background/50">
                    <span className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Pending Balance</span>
                    <span className="text-sm font-black text-amber-600 dark:text-amber-400 mt-0.5">{previewWallet.pending_balance} {previewWallet.currency}</span>
                  </div>
                </div>

                <div className="flex flex-col gap-2">
                  <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Account Status</label>
                  <Select value={String(data.status)} onValueChange={(val) => setData('status', Number(val))}>
                    <SelectTrigger className="w-full h-10 bg-background">
                      <SelectValue placeholder="Update status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="0">New (Unverified)</SelectItem>
                      <SelectItem value="1">Pending Approval</SelectItem>
                      <SelectItem value="2">Verified (Active)</SelectItem>
                      <SelectItem value="3">Blocked (Frozen Funds)</SelectItem>
                      <SelectItem value="4">Rejected</SelectItem>
                    </SelectContent>
                  </Select>
                  {errors.status && <span className="text-xs text-rose-500 font-semibold">{errors.status}</span>}
                </div>

                <div className="flex items-center space-x-3 bg-muted/10 border p-4 rounded-xl">
                  <Checkbox
                    id="is_identity_verified"
                    checked={data.is_identity_verified}
                    onCheckedChange={(checked) => setData('is_identity_verified', Boolean(checked))}
                  />
                  <div className="grid gap-1.5 leading-none">
                    <label
                      htmlFor="is_identity_verified"
                      className="text-xs font-bold text-foreground leading-none cursor-pointer"
                    >
                      Verify Legal Documents
                    </label>
                    <p className="text-[10px] text-muted-foreground">
                      Confirm store has supplied correct business permits or legal identification.
                    </p>
                  </div>
                </div>

              </div>

              <SheetFooter className="border-t p-4 flex flex-col gap-2 bg-muted/10">
                <Button type="submit" disabled={processing} className="w-full font-bold shadow-xs">
                  {processing ? 'Saving...' : 'Sync Wallet Settings'}
                </Button>
                
                <Button variant="outline" className="w-full gap-1.5 font-bold" asChild>
                  <Link href={ShowStoreWalletController.show.url(previewWallet.id)} onClick={() => setPreviewOpen(false)}>
                    <span>View Financial Details Page</span>
                    <IconArrowRight className="size-4" />
                  </Link>
                </Button>
              </SheetFooter>
            </form>
          ) : null}
        </SheetContent>
      </Sheet>
    </>
  );
}
