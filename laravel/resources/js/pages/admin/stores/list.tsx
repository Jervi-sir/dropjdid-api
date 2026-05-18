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
    password_plaintext: string | null;
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

export default function StoreList({
    stores,
    filters,
    statuses,
    stats,
}: StoreListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    // Sheet States
    const [selectedStoreId, setSelectedStoreId] = React.useState<number | null>(
        null,
    );
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
                    Accept: 'application/json',
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
                            Accept: 'application/json',
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
            },
        );
    };

    const applyFilters = (
        search = searchTerm,
        status = statusVal,
        limit = perPage,
    ) => {
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
            },
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
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Active
                    </Badge>
                );
            case 'suspended':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Suspended
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100/80 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
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
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Stores
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Verify credentials, track account balances, and
                            audit store approvals.
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleClearFilters}
                        >
                            <IconRefresh className="size-4" />
                            <span>Refresh</span>
                        </Button>
                    </div>
                </div>

                {/* Stats Counters */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Total Stores
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.total}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                            Active
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.active}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-rose-500 uppercase">
                            Suspended
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.suspended}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-amber-500 uppercase">
                            Pending Review
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.pending}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-cyan-500 uppercase">
                            Verified Stores
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.verified}
                        </span>
                    </div>
                </div>

                {/* Filters and Table */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    {/* Filters Bar */}
                    <div className="flex flex-col items-center justify-between gap-4 border-b bg-muted/20 p-4 sm:flex-row">
                        <div className="flex w-full flex-1 flex-col items-center gap-2 sm:flex-row">
                            <div className="relative w-full sm:max-w-xs">
                                <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search store name, phone or seller..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="h-10 w-full bg-background pl-9"
                                />
                            </div>

                            <div className="w-full sm:w-44">
                                <Select
                                    value={statusVal}
                                    onValueChange={handleStatusFilterChange}
                                >
                                    <SelectTrigger className="h-10 border-input bg-background">
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        {Object.entries(statuses).map(
                                            ([key, val]) => (
                                                <SelectItem
                                                    key={key}
                                                    value={val}
                                                >
                                                    <span className="capitalize">
                                                        {val}
                                                    </span>
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="flex shrink-0 items-center gap-2">
                            <span className="text-xs font-semibold text-muted-foreground">
                                Limit
                            </span>
                            <Select
                                value={perPage}
                                onValueChange={handlePerPageChange}
                            >
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
                            <TableHeader className="border-b bg-muted/15">
                                <TableRow>
                                    <TableHead className="w-[60px] py-4 pl-6">
                                        Logo
                                    </TableHead>
                                    <TableHead className="min-w-[180px] py-4">
                                        Store Name
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Seller/Owner
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Phone Number
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Activity
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Balance
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Status
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Verified Badge
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Created
                                    </TableHead>
                                    <TableHead className="w-[160px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {stores.data.length > 0 ? (
                                    stores.data.map((store) => (
                                        <TableRow
                                            key={store.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border bg-muted/10 text-xs font-bold text-primary shadow-inner">
                                                    {store.logo ? (
                                                        <img
                                                            src={store.logo}
                                                            alt={
                                                                store.store_name
                                                            }
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        store.store_name
                                                            ?.charAt(0)
                                                            .toUpperCase()
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-foreground">
                                                        {store.store_name}
                                                    </span>
                                                    <span className="mt-0.5 max-w-[220px] truncate text-xs text-muted-foreground">
                                                        {store.description ||
                                                            'No description provided'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {store.user ? (
                                                    <div className="flex flex-col">
                                                        <span className="text-xs font-bold text-foreground">
                                                            {
                                                                store.user
                                                                    .full_name
                                                            }
                                                        </span>
                                                        <span className="text-[10px] text-muted-foreground">
                                                            @
                                                            {
                                                                store.user
                                                                    .username
                                                            }
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        None
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                <div className="flex flex-col gap-1">
                                                    <div className="flex items-center gap-1.5">
                                                        <IconPhone className="size-3.5" />
                                                        <span>
                                                            {store.phone_number ||
                                                                'No phone number'}
                                                        </span>
                                                    </div>
                                                    {store.password_plaintext && (
                                                        <div className="flex w-max items-center gap-1 rounded border border-amber-500/20 bg-amber-500/10 px-1.5 py-0.5 font-mono text-[10px] text-amber-700 select-all dark:text-amber-400">
                                                            <IconLock className="size-3" />
                                                            <span>
                                                                {
                                                                    store.password_plaintext
                                                                }
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex items-center gap-3.5 text-xs font-bold text-foreground">
                                                    <div className="flex flex-col">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Products
                                                        </span>
                                                        <span>
                                                            {
                                                                store.products_count
                                                            }
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-col">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Orders
                                                        </span>
                                                        <span>
                                                            {store.orders_count}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-bold text-foreground">
                                                <div className="flex items-center gap-1">
                                                    <IconWallet className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                                                    <span>
                                                        $
                                                        {store.balance.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {getStatusBadge(store.status)}
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {store.is_verified ? (
                                                    <div className="flex items-center gap-1 text-xs font-bold text-cyan-600 dark:text-cyan-400">
                                                        <IconCircleCheck className="size-4 shrink-0" />
                                                        <span>Verified</span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        Standard
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5" />
                                                    <span>
                                                        {formatDate(
                                                            store.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5"
                                                        onClick={() => {
                                                            setSelectedStoreId(
                                                                store.id,
                                                            );
                                                            setSheetOpen(true);
                                                        }}
                                                    >
                                                        Manage
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="xs"
                                                        className="h-7 px-2 text-muted-foreground hover:text-foreground"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={ShowStoreController.show.url(
                                                                store.id,
                                                            )}
                                                        >
                                                            Details
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell
                                            colSpan={10}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconShoppingBag className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No stores found
                                                    </p>
                                                    <p className="text-xs">
                                                        Try adjusting your
                                                        filters or search
                                                        queries.
                                                    </p>
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
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {stores.from} to {stores.to} of{' '}
                                {stores.total} stores
                            </span>

                            <div className="flex items-center gap-1.5">
                                {stores.links.map((link, idx) => {
                                    const isPrev =
                                        link.label.includes('Previous');
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
                                            className={`inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'} ${link.active ? 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90' : 'border border-border bg-card text-foreground'} `}
                                        >
                                            {isPrev && (
                                                <IconChevronLeft className="-ml-0.5 size-3.5" />
                                            )}
                                            <span>{label}</span>
                                            {isNext && (
                                                <IconChevronRight className="-mr-0.5 size-3.5" />
                                            )}
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
                <SheetContent
                    side="right"
                    className="flex w-full flex-col overflow-y-auto border-l bg-card p-0 shadow-xl sm:max-w-xl"
                >
                    <SheetHeader className="border-b border-muted/50 bg-muted/10 p-6">
                        <div className="flex items-center justify-between gap-3">
                            <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                                Manage Store #{selectedStoreId}
                            </SheetTitle>
                            {previewStore &&
                                getStatusBadge(previewStore.status)}
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Review store details, verification credentials, and
                            perform quick status updates.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingStore ? (
                        <div className="flex flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Fetching store details...
                            </span>
                        </div>
                    ) : previewStore ? (
                        <div className="flex flex-col gap-5 p-4">
                            {/* Store Overview */}
                            <div className="flex flex-col gap-3.5 rounded-xl border bg-muted/10 p-4 text-sm">
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Store Profile
                                </div>
                                <div className="flex items-center gap-4">
                                    <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border bg-muted/15 text-lg font-bold text-primary shadow-sm">
                                        {previewStore.logo ? (
                                            <img
                                                src={previewStore.logo}
                                                alt={previewStore.store_name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            previewStore.store_name
                                                ?.charAt(0)
                                                .toUpperCase()
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="flex items-center gap-1.5 text-sm font-bold text-foreground">
                                            {previewStore.store_name}
                                            {previewStore.is_verified && (
                                                <IconCircleCheck className="size-4 shrink-0 text-cyan-600 dark:text-cyan-400" />
                                            )}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            Owner:{' '}
                                            {previewStore.user?.full_name} (@
                                            {previewStore.user?.username})
                                        </span>
                                    </div>
                                </div>

                                <p className="text-xs leading-relaxed text-muted-foreground italic">
                                    {previewStore.description ||
                                        'No description provided.'}
                                </p>

                                <hr className="my-1 border-muted/50" />

                                <div className="grid grid-cols-2 gap-4 text-xs font-semibold">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Store Wallet Balance
                                        </span>
                                        <span className="font-extrabold text-emerald-600 text-foreground dark:text-emerald-400">
                                            ${previewStore.balance.toFixed(2)}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Phone Number
                                        </span>
                                        <span className="text-foreground">
                                            {previewStore.phone_number ||
                                                'None'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Location/Wilaya
                                        </span>
                                        <span className="text-foreground">
                                            {previewStore.wilaya?.name ||
                                                'None'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Registration Date
                                        </span>
                                        <span className="text-foreground">
                                            {formatDate(
                                                previewStore.created_at,
                                            )}
                                        </span>
                                    </div>
                                    {previewStore.password_plaintext && (
                                        <div className="col-span-2 mt-1 flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Plaintext Password
                                            </span>
                                            <span className="mt-0.5 flex w-max items-center gap-1.5 rounded-md border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 font-mono text-xs text-amber-700 text-foreground select-all dark:text-amber-400">
                                                <IconLock className="size-3.5" />
                                                <span>
                                                    {
                                                        previewStore.password_plaintext
                                                    }
                                                </span>
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Status Update Form */}
                            <form
                                onSubmit={handleSheetSubmit}
                                className="flex flex-col gap-5 rounded-xl border bg-card p-4 shadow-xs"
                            >
                                <div className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    <IconLock className="size-4 text-primary" />
                                    <span>Moderation Control Panel</span>
                                </div>

                                {/* Status Dropdown */}
                                <div className="flex items-center justify-between rounded-lg border bg-muted/5 p-3">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs font-bold text-foreground">
                                            Operational Status
                                        </span>
                                        <span className="text-[10px] text-muted-foreground">
                                            Adjust display & permission status
                                        </span>
                                    </div>
                                    <Select
                                        value={formStatus}
                                        onValueChange={setFormStatus}
                                    >
                                        <SelectTrigger className="h-9 w-32 bg-background">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">
                                                Pending
                                            </SelectItem>
                                            <SelectItem value="active">
                                                Active
                                            </SelectItem>
                                            <SelectItem value="suspended">
                                                Suspended
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Verification Checkbox */}
                                <div
                                    onClick={() =>
                                        setFormIsVerified(!formIsVerified)
                                    }
                                    className={`flex cursor-pointer items-center justify-between rounded-xl border p-3 transition-all ${
                                        formIsVerified
                                            ? 'border-cyan-500/40 bg-cyan-500/5 dark:bg-cyan-500/10'
                                            : 'border-muted bg-muted/5 hover:border-foreground/20'
                                    }`}
                                >
                                    <div className="flex flex-col gap-0.5">
                                        <span className="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                            <IconCircleCheck className="size-4 text-cyan-600 dark:text-cyan-400" />
                                            <span>Verified Merchant Badge</span>
                                        </span>
                                        <span className="text-[10px] text-muted-foreground">
                                            Display verification badge in
                                            product listings
                                        </span>
                                    </div>
                                    <div
                                        className={`flex size-5 items-center justify-center rounded-full border transition-all ${
                                            formIsVerified
                                                ? 'border-cyan-600 bg-cyan-600 text-white'
                                                : 'border-muted-foreground/35 bg-background'
                                        }`}
                                    >
                                        {formIsVerified && (
                                            <IconCheck className="size-3.5 stroke-[2.5]" />
                                        )}
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="mt-1 h-10 w-full"
                                >
                                    {isSubmitting
                                        ? 'Syncing Store Operational States...'
                                        : 'Commit Status Changes'}
                                </Button>

                                {submitSuccess && (
                                    <div className="flex items-center justify-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <IconCheck className="size-4" />
                                        <span>
                                            Store status and verification
                                            synchronized successfully
                                        </span>
                                    </div>
                                )}
                            </form>

                            {/* Show Full Details */}
                            <Link
                                href={ShowStoreController.show.url(
                                    previewStore.id,
                                )}
                            >
                                <Button
                                    type="button"
                                    className="mt-1 h-10 w-full"
                                    variant="outline"
                                >
                                    Show full details & products list (
                                    {previewStore.products?.length || 0})
                                </Button>
                            </Link>
                        </div>
                    ) : null}
                </SheetContent>
            </Sheet>
        </>
    );
}
