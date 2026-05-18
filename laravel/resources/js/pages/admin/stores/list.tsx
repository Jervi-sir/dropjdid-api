import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowStoreController from '@/actions/App/Http/Controllers/Admin/Stores/ShowStoreController';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from '@/components/ui/sheet';
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
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select';
import {
  IconSearch,
  IconCalendar,
  IconRefresh,
  IconChevronLeft,
  IconChevronRight,
  IconCheck,
  IconLoader2,
  IconShoppingBag,
  IconWallet,
  IconPhone,
  IconMapPin,
  IconUser,
  IconCircleCheck,
  IconAlertTriangle,
  IconLock,
} from '@tabler/icons-react';

interface Store {
  id: number;
  store_name: string;
  phone_number: string;
  logo: string | null;
  description: string | null;
  balance: number;
  status: string;
  is_verified: boolean;
  user: {
    id: number;
    full_name: string;
    username: string;
  } | null;
  wilaya: {
    id: number;
    name: string;
  } | null;
  products_count: number;
  orders_count: number;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedStores {
  data: Store[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface FilterProps {
  search: string;
  status: string;
  per_page: number;
}

interface StoreListProps {
  stores: PaginatedStores;
  filters: FilterProps;
  statuses: Record<number, string>;
  stats: {
    total: number;
    pending: number;
    active: number;
    suspended: number;
    verified: number;
  };
}

export default function StoreList({ stores, filters, statuses, stats }: StoreListProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
  const [perPage, setPerPage] = React.useState(filters.per_page?.toString() || '10');

  // Sheet States
  const [selectedStoreId, setSelectedStoreId] = React.useState<number | null>(null);
  const [sheetOpen, setSheetOpen] = React.useState(false);
  const [loadingStore, setLoadingStore] = React.useState(false);
  const [previewStore, setPreviewStore] = React.useState<any>(null);

  // Sheet Management Form States
  const [formStatus, setFormStatus] = React.useState<string>('pending');
  const [formIsVerified, setFormIsVerified] = React.useState<boolean>(false);
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const [submitSuccess, setSubmitSuccess] = React.useState(false);

  React.useEffect(() => {
    if (selectedStoreId && sheetOpen) {
      setLoadingStore(true);
      setPreviewStore(null);
      setSubmitSuccess(false);

      fetch(ShowStoreController.show.url(selectedStoreId), {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then((res) => res.json())
        .then((data) => {
          setPreviewStore(data.store);
          setFormStatus(data.store.status);
          setFormIsVerified(data.store.is_verified);
        })
        .catch((err) => console.error(err))
        .finally(() => setLoadingStore(false));
    }
  }, [selectedStoreId, sheetOpen]);

  const handleSheetSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitSuccess(false);

    router.put(
      ShowStoreController.update.url(selectedStoreId!),
      {
        status: formStatus,
        is_verified: formIsVerified,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          setSubmitSuccess(true);
          // Refetch to reflect updates in preview
          fetch(ShowStoreController.show.url(selectedStoreId!), {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          })
            .then((res) => res.json())
            .then((data) => {
              setPreviewStore(data.store);
            });
        },
        onFinish: () => {
          setIsSubmitting(false);
        },
      }
    );
  };

  const applyFilters = (search = searchTerm, status = statusVal, limit = perPage) => {
    router.get(
      '/admin/stores',
      {
        search: search || undefined,
        status: status === 'all' ? undefined : status,
        per_page: limit === '10' ? undefined : limit,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      }
    );
  };

  React.useEffect(() => {
    const timer = setTimeout(() => {
      if (searchTerm !== (filters.search || '')) {
        applyFilters(searchTerm, statusVal, perPage);
      }
    }, 450);
    return () => clearTimeout(timer);
  }, [searchTerm]);

  const handleStatusFilterChange = (value: string) => {
    setStatusVal(value);
    applyFilters(searchTerm, value, perPage);
  };

  const handlePerPageChange = (value: string) => {
    setPerPage(value);
    applyFilters(searchTerm, statusVal, value);
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setStatusVal('all');
    setPerPage('10');
    router.get('/admin/stores', {}, { preserveState: true, replace: true });
  };

  const getStatusBadge = (status: string) => {
    switch (status.toLowerCase()) {
      case 'active':
        return (
          <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
            Active
          </Badge>
        );
      case 'suspended':
        return (
          <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
            Suspended
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100/80 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">
            Pending
          </Badge>
        );
      default:
        return <Badge variant="outline">{status}</Badge>;
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  return (
    <>
      <Head title="Stores Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Header Section */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Stores</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Verify credentials, track account balances, and audit store approvals.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" onClick={handleClearFilters}>
              <IconRefresh className="size-4" />
              <span>Refresh</span>
            </Button>
          </div>
        </div>

        {/* Stats Counters */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Stores</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.total}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Active</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.active}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-rose-500">Suspended</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.suspended}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-amber-500">Pending Review</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.pending}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-cyan-500">Verified Stores</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.verified}</span>
          </div>
        </div>

        {/* Filters and Table */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">

          {/* Filters Bar */}
          <div className="p-4 border-b flex flex-col sm:flex-row items-center gap-4 justify-between bg-muted/20">
            <div className="flex flex-1 flex-col sm:flex-row items-center gap-2 w-full">
              <div className="relative w-full sm:max-w-xs">
                <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search store name, phone or seller..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-9 h-10 w-full bg-background"
                />
              </div>

              <div className="w-full sm:w-44">
                <Select value={statusVal} onValueChange={handleStatusFilterChange}>
                  <SelectTrigger className="h-10 bg-background border-input">
                    <SelectValue placeholder="All Statuses" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    {Object.entries(statuses).map(([key, val]) => (
                      <SelectItem key={key} value={val}>
                        <span className="capitalize">{val}</span>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="flex items-center gap-2 shrink-0">
              <span className="text-xs text-muted-foreground font-semibold">Limit</span>
              <Select value={perPage} onValueChange={handlePerPageChange}>
                <SelectTrigger className="h-10 w-20 bg-background">
                  <SelectValue placeholder="10" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {/* Table Elements */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/15 border-b">
                <TableRow>
                  <TableHead className="w-[60px] pl-6 py-4">Logo</TableHead>
                  <TableHead className="min-w-[180px] py-4">Store Name</TableHead>
                  <TableHead className="py-4">Seller/Owner</TableHead>
                  <TableHead className="py-4">Phone Number</TableHead>
                  <TableHead className="py-4">Activity</TableHead>
                  <TableHead className="py-4">Balance</TableHead>
                  <TableHead className="py-4">Status</TableHead>
                  <TableHead className="py-4">Verified Badge</TableHead>
                  <TableHead className="py-4">Created</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[160px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {stores.data.length > 0 ? (
                  stores.data.map((store) => (
                    <TableRow key={store.id} className="hover:bg-muted/5 group/row transition-colors">
                      <TableCell className="pl-6 py-4">
                        <div className="size-10 rounded-xl overflow-hidden border shrink-0 bg-muted/10 flex items-center justify-center font-bold text-xs text-primary shadow-inner">
                          {store.logo ? (
                            <img src={store.logo} alt={store.store_name} className="w-full h-full object-cover" />
                          ) : (
                            store.store_name?.charAt(0).toUpperCase()
                          )}
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-col">
                          <span className="font-bold text-foreground text-sm">{store.store_name}</span>
                          <span className="text-xs text-muted-foreground truncate max-w-[220px] mt-0.5">
                            {store.description || 'No description provided'}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {store.user ? (
                          <div className="flex flex-col">
                            <span className="font-bold text-xs text-foreground">{store.user.full_name}</span>
                            <span className="text-[10px] text-muted-foreground">@{store.user.username}</span>
                          </div>
                        ) : (
                          <span className="text-xs text-muted-foreground">None</span>
                        )}
                      </TableCell>
                      <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconPhone className="size-3.5" />
                          <span>{store.phone_number || 'No phone number'}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex items-center gap-3.5 text-xs font-bold text-foreground">
                          <div className="flex flex-col">
                            <span className="text-[10px] text-muted-foreground font-semibold">Products</span>
                            <span>{store.products_count}</span>
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[10px] text-muted-foreground font-semibold">Orders</span>
                            <span>{store.orders_count}</span>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-xs font-bold text-foreground">
                        <div className="flex items-center gap-1">
                          <IconWallet className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                          <span>${store.balance.toFixed(2)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {getStatusBadge(store.status)}
                      </TableCell>
                      <TableCell className="py-4">
                        {store.is_verified ? (
                          <div className="flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 font-bold">
                            <IconCircleCheck className="size-4 shrink-0" />
                            <span>Verified</span>
                          </div>
                        ) : (
                          <span className="text-xs text-muted-foreground">Standard</span>
                        )}
                      </TableCell>
                      <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconCalendar className="size-3.5" />
                          <span>{formatDate(store.created_at)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-1.5">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5"
                            onClick={() => {
                              setSelectedStoreId(store.id);
                              setSheetOpen(true);
                            }}
                          >
                            Manage
                          </Button>
                          <Button variant="ghost" size="xs" className="h-7 px-2 text-muted-foreground hover:text-foreground" asChild>
                            <Link href={ShowStoreController.show.url(store.id)}>
                              Details
                            </Link>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={10} className="py-12 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconShoppingBag className="size-10 text-muted-foreground/55 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No stores found</p>
                          <p className="text-xs">Try adjusting your filters or search queries.</p>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination */}
          {stores.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {stores.from} to {stores.to} of {stores.total} stores
              </span>

              <div className="flex items-center gap-1.5">
                {stores.links.map((link, idx) => {
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

      {/* Dynamic Manage Store Sheet Preview */}
      <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
        <SheetContent side="right" className="w-full sm:max-w-xl overflow-y-auto bg-card border-l shadow-xl flex flex-col p-0">
          <SheetHeader className="p-6 border-b border-muted/50 bg-muted/10">
            <div className="flex items-center justify-between gap-3">
              <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                Manage Store #{selectedStoreId}
              </SheetTitle>
              {previewStore && getStatusBadge(previewStore.status)}
            </div>
            <SheetDescription className="text-xs text-muted-foreground">
              Review store details, verification credentials, and perform quick status updates.
            </SheetDescription>
          </SheetHeader>

          {loadingStore ? (
            <div className="flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Fetching store details...</span>
            </div>
          ) : previewStore ? (
            <div className="flex flex-col p-4 gap-5">

              {/* Store Overview */}
              <div className="flex flex-col gap-3.5 text-sm border bg-muted/10 p-4 rounded-xl">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  Store Profile
                </div>
                <div className="flex items-center gap-4">
                  <div className="size-16 rounded-xl overflow-hidden border shrink-0 bg-muted/15 flex items-center justify-center font-bold text-lg text-primary shadow-sm">
                    {previewStore.logo ? (
                      <img src={previewStore.logo} alt={previewStore.store_name} className="w-full h-full object-cover" />
                    ) : (
                      previewStore.store_name?.charAt(0).toUpperCase()
                    )}
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="font-bold text-foreground text-sm flex items-center gap-1.5">
                      {previewStore.store_name}
                      {previewStore.is_verified && (
                        <IconCircleCheck className="size-4 text-cyan-600 dark:text-cyan-400 shrink-0" />
                      )}
                    </span>
                    <span className="text-xs text-muted-foreground">Owner: {previewStore.user?.full_name} (@{previewStore.user?.username})</span>
                  </div>
                </div>

                <p className="text-xs text-muted-foreground italic leading-relaxed">
                  {previewStore.description || 'No description provided.'}
                </p>

                <hr className="border-muted/50 my-1" />

                <div className="grid grid-cols-2 gap-4 text-xs font-semibold">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Store Wallet Balance</span>
                    <span className="text-foreground font-extrabold text-emerald-600 dark:text-emerald-400">${previewStore.balance.toFixed(2)}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Phone Number</span>
                    <span className="text-foreground">{previewStore.phone_number || 'None'}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Location/Wilaya</span>
                    <span className="text-foreground">{previewStore.wilaya?.name || 'None'}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Registration Date</span>
                    <span className="text-foreground">{formatDate(previewStore.created_at)}</span>
                  </div>
                </div>
              </div>

              {/* Status Update Form */}
              <form onSubmit={handleSheetSubmit} className="flex flex-col gap-5 border p-4 rounded-xl bg-card shadow-xs">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                  <IconLock className="size-4 text-primary" />
                  <span>Moderation Control Panel</span>
                </div>

                {/* Status Dropdown */}
                <div className="flex items-center justify-between p-3 rounded-lg border bg-muted/5">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-xs font-bold text-foreground">Operational Status</span>
                    <span className="text-[10px] text-muted-foreground">Adjust display & permission status</span>
                  </div>
                  <Select value={formStatus} onValueChange={setFormStatus}>
                    <SelectTrigger className="w-32 h-9 bg-background">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="pending">Pending</SelectItem>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="suspended">Suspended</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Verification Checkbox */}
                <div
                  onClick={() => setFormIsVerified(!formIsVerified)}
                  className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all ${
                    formIsVerified
                      ? 'border-cyan-500/40 bg-cyan-500/5 dark:bg-cyan-500/10'
                      : 'border-muted hover:border-foreground/20 bg-muted/5'
                  }`}
                >
                  <div className="flex flex-col gap-0.5">
                    <span className="text-xs font-bold text-foreground flex items-center gap-1.5">
                      <IconCircleCheck className="size-4 text-cyan-600 dark:text-cyan-400" />
                      <span>Verified Merchant Badge</span>
                    </span>
                    <span className="text-[10px] text-muted-foreground">
                      Display verification badge in product listings
                    </span>
                  </div>
                  <div
                    className={`size-5 rounded-full border flex items-center justify-center transition-all ${
                      formIsVerified ? 'bg-cyan-600 border-cyan-600 text-white' : 'border-muted-foreground/35 bg-background'
                    }`}
                  >
                    {formIsVerified && <IconCheck className="size-3.5 stroke-[2.5]" />}
                  </div>
                </div>

                <Button type="submit" disabled={isSubmitting} className="w-full h-10 mt-1">
                  {isSubmitting ? 'Syncing Store Operational States...' : 'Commit Status Changes'}
                </Button>

                {submitSuccess && (
                  <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-1.5 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                    <IconCheck className="size-4" />
                    <span>Store status and verification synchronized successfully</span>
                  </div>
                )}
              </form>

              {/* Show Full Details */}
              <Link href={ShowStoreController.show.url(previewStore.id)}>
                <Button type="button" className="w-full h-10 mt-1" variant="outline">
                  Show full details & products list ({previewStore.products?.length || 0})
                </Button>
              </Link>

            </div>
          ) : null}
        </SheetContent>
      </Sheet>
    </>
  );
}
