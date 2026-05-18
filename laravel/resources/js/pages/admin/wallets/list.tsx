import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ListWalletsController from '@/actions/App/Http/Controllers/Admin/Wallets/ListWalletsController';
import ShowWalletController from '@/actions/App/Http/Controllers/Admin/Wallets/ShowWalletController';
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
    IconDotsVertical,
    IconChevronLeft,
    IconChevronRight,
} from '@tabler/icons-react';

interface User {
    id: number;
    full_name: string;
    username: string;
    email: string;
    image: string | null;
}

interface Wallet {
    id: number;
    user: User | null;
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

interface PaginatedWallets {
    data: Wallet[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface ListWalletsProps {
    wallets: PaginatedWallets;
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

export default function ListWallets({
    wallets,
    kpis,
    filters,
}: ListWalletsProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusFilter, setStatusFilter] = React.useState(
        filters.status || 'all',
    );
    const [typeFilter, setTypeFilter] = React.useState(filters.type || 'all');
    const [verifiedFilter, setVerifiedFilter] = React.useState(
        filters.is_identity_verified || 'all',
    );

    // Sheet Preview States
    const [previewOpen, setPreviewOpen] = React.useState(false);
    const [previewWallet, setPreviewWallet] = React.useState<Wallet | null>(
        null,
    );
    const [loadingWallet, setLoadingWallet] = React.useState(false);

    // Form hook for saving wallet status and identity check
    const { data, setData, put, processing, errors, reset, clearErrors } =
        useForm({
            status: 0,
            is_identity_verified: false,
        });

    // Fetch individual wallet details for preview
    const handleOpenPreview = async (wallet: Wallet) => {
        setPreviewOpen(true);
        setLoadingWallet(true);
        setPreviewWallet(wallet);
        clearErrors();

        setData({
            status: wallet.status_raw,
            is_identity_verified: wallet.is_identity_verified,
        });

        try {
            const response = await fetch(
                ShowWalletController.show.url(wallet.id),
                {
                    headers: { Accept: 'application/json' },
                },
            );
            if (response.ok) {
                const result = await response.json();
                setPreviewWallet(result.wallet);
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

        put(ShowWalletController.update.url(previewWallet.id), {
            preserveScroll: true,
            onSuccess: () => {
                setPreviewOpen(false);
            },
        });
    };

    const applyFilters = () => {
        router.get(
            '/admin/wallets',
            {
                search: searchTerm || undefined,
                status: statusFilter !== 'all' ? statusFilter : undefined,
                type: typeFilter !== 'all' ? typeFilter : undefined,
                is_identity_verified:
                    verifiedFilter !== 'all' ? verifiedFilter : undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
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
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Verified
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            case 'blocked':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Blocked
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="border border-red-500/20 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                        Rejected
                    </Badge>
                );
            default:
                return (
                    <Badge
                        variant="outline"
                        className="border-slate-300 bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        New
                    </Badge>
                );
        }
    };

    const getTypeBadge = (type: string) => {
        if (type === 'refund') {
            return (
                <Badge className="border-sky-300 bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                    Refund
                </Badge>
            );
        }
        return (
            <Badge className="border-indigo-300 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                Balance
            </Badge>
        );
    };

    return (
        <>
            <Head title="Wallets Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Page Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Merchant Wallets
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Audit merchant balances, verify identities, freeze
                            funds, and review withdrawal requests.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={applyFilters}
                        className="gap-1 self-start md:self-auto"
                    >
                        <IconRefresh className="size-4" />
                        <span>Sync Balances</span>
                    </Button>
                </div>

                {/* KPI Grid */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* KPI 1 */}
                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <IconWallet className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Total Balance
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.total_balance} DZD
                            </span>
                        </div>
                    </div>

                    {/* KPI 2 */}
                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <IconScale className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Pending Payouts
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.total_pending_balance} DZD
                            </span>
                        </div>
                    </div>

                    {/* KPI 3 */}
                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <IconShieldCheck className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Verified Identity
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.verified_identity_count} Merchants
                            </span>
                        </div>
                    </div>

                    {/* KPI 4 */}
                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <IconAlertTriangle className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Frozen Accounts
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.blocked_count} Blocked
                            </span>
                        </div>
                    </div>
                </div>

                {/* Filter bar and Table */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    {/* Filters Area */}
                    <div className="flex flex-col items-center gap-4 border-b bg-muted/15 p-4 lg:flex-row">
                        <div className="relative w-full shrink-0 lg:max-w-xs">
                            <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search merchant name or email..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-10 w-full bg-background pl-9"
                            />
                        </div>

                        <div className="flex w-full flex-wrap items-center gap-3 lg:justify-end">
                            {/* Type Select */}
                            <div className="flex shrink-0 items-center gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">
                                    Type:
                                </span>
                                <Select
                                    value={typeFilter}
                                    onValueChange={setTypeFilter}
                                >
                                    <SelectTrigger className="h-9 w-[120px] bg-background">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Types
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Balance
                                        </SelectItem>
                                        <SelectItem value="1">
                                            Refund
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Status Select */}
                            <div className="flex shrink-0 items-center gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">
                                    Status:
                                </span>
                                <Select
                                    value={statusFilter}
                                    onValueChange={setStatusFilter}
                                >
                                    <SelectTrigger className="h-9 w-[120px] bg-background">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        <SelectItem value="0">New</SelectItem>
                                        <SelectItem value="1">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="2">
                                            Verified
                                        </SelectItem>
                                        <SelectItem value="3">
                                            Blocked
                                        </SelectItem>
                                        <SelectItem value="4">
                                            Rejected
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Verification Select */}
                            <div className="flex shrink-0 items-center gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">
                                    Identity Check:
                                </span>
                                <Select
                                    value={verifiedFilter}
                                    onValueChange={setVerifiedFilter}
                                >
                                    <SelectTrigger className="h-9 w-[140px] bg-background">
                                        <SelectValue placeholder="Identity Check" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Checks
                                        </SelectItem>
                                        <SelectItem value="1">
                                            Verified Only
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Unverified Only
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    {/* Table Container */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="border-b bg-muted/5">
                                <TableRow>
                                    <TableHead className="py-4 pl-6">
                                        Account Profile
                                    </TableHead>
                                    <TableHead className="py-4">Type</TableHead>
                                    <TableHead className="py-4 text-right">
                                        Available Balance
                                    </TableHead>
                                    <TableHead className="py-4 text-right">
                                        Pending Balance
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Identity Status
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Account Status
                                    </TableHead>
                                    <TableHead className="w-[200px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {wallets.data.length > 0 ? (
                                    wallets.data.map((wallet) => (
                                        <TableRow
                                            key={wallet.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            {/* Merchant Avatar Card */}
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-xs font-bold text-primary uppercase">
                                                        {wallet.user?.image ? (
                                                            <img
                                                                src={
                                                                    wallet.user
                                                                        .image
                                                                }
                                                                alt={
                                                                    wallet.user
                                                                        .full_name
                                                                }
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            wallet.user?.full_name
                                                                ?.charAt(0)
                                                                .toUpperCase() ||
                                                            'M'
                                                        )}
                                                    </div>
                                                    <div className="flex flex-col gap-0.5">
                                                        <span className="text-xs font-extrabold text-foreground">
                                                            {wallet.user
                                                                ?.full_name ||
                                                                'N/A'}
                                                        </span>
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            @
                                                            {wallet.user
                                                                ?.username ||
                                                                'unknown'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>

                                            {/* Type */}
                                            <TableCell className="py-4">
                                                {getTypeBadge(wallet.type)}
                                            </TableCell>

                                            {/* Available Balance */}
                                            <TableCell className="py-4 text-right text-xs font-extrabold text-foreground">
                                                {wallet.balance}{' '}
                                                {wallet.currency}
                                            </TableCell>

                                            {/* Pending Balance */}
                                            <TableCell className="py-4 text-right text-xs font-bold text-amber-600 dark:text-amber-400">
                                                {wallet.pending_balance}{' '}
                                                {wallet.currency}
                                            </TableCell>

                                            {/* Identity Check */}
                                            <TableCell className="py-4 text-center">
                                                {wallet.is_identity_verified ? (
                                                    <div className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                        <IconCircleCheck className="size-3.5 stroke-[2.5]" />
                                                        <span>Verified</span>
                                                    </div>
                                                ) : (
                                                    <div className="inline-flex items-center gap-1 rounded-full bg-muted/10 px-2 py-0.5 text-xs font-semibold text-muted-foreground">
                                                        <span>Unverified</span>
                                                    </div>
                                                )}
                                            </TableCell>

                                            {/* Account status */}
                                            <TableCell className="py-4 text-center">
                                                {getStatusBadge(wallet.status)}
                                            </TableCell>

                                            {/* Actions */}
                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5 font-bold"
                                                        onClick={() =>
                                                            handleOpenPreview(
                                                                wallet,
                                                            )
                                                        }
                                                    >
                                                        Manage
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="xs"
                                                        className="h-7 px-2"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={ShowWalletController.show.url(
                                                                wallet.id,
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
                                            colSpan={7}
                                            className="py-16 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconWallet className="size-12 stroke-[1.5] text-muted-foreground/50" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No wallets found
                                                    </p>
                                                    <p className="text-xs">
                                                        Adjust filters or search
                                                        parameters to view
                                                        wallets.
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
                    {wallets.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {wallets.from} to {wallets.to} of{' '}
                                {wallets.total} wallets
                            </span>

                            <div className="flex items-center gap-1.5">
                                {wallets.links.map((link, idx) => {
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

            {/* Slide-over Manager Sheet */}
            <Sheet open={previewOpen} onOpenChange={setPreviewOpen}>
                <SheetContent className="flex h-full flex-col bg-card sm:max-w-md">
                    <SheetHeader className="border-b pb-4">
                        <SheetTitle className="text-lg font-black text-foreground">
                            Wallet Audit Room
                        </SheetTitle>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Verify legal identity, unlock/block merchant ledger
                            settings, and inspect balance fields.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingWallet ? (
                        <div className="flex flex-grow flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Querying merchant wallets...
                            </span>
                        </div>
                    ) : previewWallet ? (
                        <form
                            onSubmit={handleUpdateWallet}
                            className="flex flex-grow flex-col justify-between"
                        >
                            <div className="flex flex-col gap-6 overflow-y-auto p-4">
                                {/* Profile Widget */}
                                <div className="flex items-center gap-4 rounded-2xl border bg-muted/20 p-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-full border bg-primary/10 text-base font-extrabold text-primary uppercase">
                                        {previewWallet.user?.image ? (
                                            <img
                                                src={previewWallet.user.image}
                                                alt={
                                                    previewWallet.user.full_name
                                                }
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            previewWallet.user?.full_name
                                                ?.charAt(0)
                                                .toUpperCase() || 'M'
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-sm font-extrabold text-foreground">
                                            {previewWallet.user?.full_name ||
                                                'N/A'}
                                        </span>
                                        <span className="text-xs font-semibold text-muted-foreground">
                                            @
                                            {previewWallet.user?.username ||
                                                'unknown'}
                                        </span>
                                        <span className="mt-0.5 text-[10px] font-medium text-muted-foreground">
                                            {previewWallet.user?.email || 'N/A'}
                                        </span>
                                    </div>
                                </div>

                                {/* Account Details Checklist */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="flex flex-col rounded-xl border bg-background/50 p-3">
                                        <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Ledger Balance
                                        </span>
                                        <span className="mt-0.5 text-sm font-black text-foreground">
                                            {previewWallet.balance}{' '}
                                            {previewWallet.currency}
                                        </span>
                                    </div>
                                    <div className="flex flex-col rounded-xl border bg-background/50 p-3">
                                        <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Pending Balance
                                        </span>
                                        <span className="mt-0.5 text-sm font-black text-amber-600 dark:text-amber-400">
                                            {previewWallet.pending_balance}{' '}
                                            {previewWallet.currency}
                                        </span>
                                    </div>
                                </div>

                                {/* Status Selector */}
                                <div className="flex flex-col gap-2">
                                    <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        Account Status
                                    </label>
                                    <Select
                                        value={String(data.status)}
                                        onValueChange={(val) =>
                                            setData('status', Number(val))
                                        }
                                    >
                                        <SelectTrigger className="h-10 w-full bg-background">
                                            <SelectValue placeholder="Update status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="0">
                                                New (Unverified)
                                            </SelectItem>
                                            <SelectItem value="1">
                                                Pending Approval
                                            </SelectItem>
                                            <SelectItem value="2">
                                                Verified (Active)
                                            </SelectItem>
                                            <SelectItem value="3">
                                                Blocked (Frozen Funds)
                                            </SelectItem>
                                            <SelectItem value="4">
                                                Rejected
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && (
                                        <span className="text-xs font-semibold text-rose-500">
                                            {errors.status}
                                        </span>
                                    )}
                                </div>

                                {/* Identity Verification Checkbox */}
                                <div className="flex items-center space-x-3 rounded-xl border bg-muted/10 p-4">
                                    <Checkbox
                                        id="is_identity_verified"
                                        checked={data.is_identity_verified}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'is_identity_verified',
                                                Boolean(checked),
                                            )
                                        }
                                    />
                                    <div className="grid gap-1.5 leading-none">
                                        <label
                                            htmlFor="is_identity_verified"
                                            className="text-xs leading-none font-bold text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Verify Legal Identity
                                        </label>
                                        <p className="text-[10px] text-muted-foreground">
                                            Confirm merchant has provided legal
                                            identification documents.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Footer Panel */}
                            <SheetFooter className="flex flex-col gap-2 border-t bg-muted/10 p-4">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full font-bold shadow-xs"
                                >
                                    {processing
                                        ? 'Saving...'
                                        : 'Sync Wallet Settings'}
                                </Button>

                                <Button
                                    variant="outline"
                                    className="w-full gap-1.5 font-bold"
                                    asChild
                                >
                                    <Link
                                        href={ShowWalletController.show.url(
                                            previewWallet.id,
                                        )}
                                        onClick={() => setPreviewOpen(false)}
                                    >
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
