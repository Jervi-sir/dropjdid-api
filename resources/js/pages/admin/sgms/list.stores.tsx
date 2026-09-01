import { Head, Link, router } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock,
    Search,
    ShieldAlert,
    Store as StoreIcon,
    UserCheck,
    XCircle,
    X,
    AlertCircle,
} from 'lucide-react';
import React, { useState } from 'react';
import {
    approve,
    index,
    updateStatus,
} from '@/actions/App/Http/Controllers/Admin/Sgm/ListStoreController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface RoleItem {
    id: number;
    code: string;
    en: string;
    fr?: string;
    ar?: string;
}

interface UserItem {
    id: number;
    full_name: string | null;
    username: string | null;
    email: string;
    phone_number: string | null;
    image_url: string | null;
    roles?: RoleItem[];
}

interface StoreItem {
    id: number;
    user_id: number | null;
    name: string | null;
    phone_number: string | null;
    description: string | null;
    image_url: string | null;
    store_status: 'pending' | 'active' | 'suspended' | string | null;
    is_approved: boolean;
    created_at: string;
    updated_at: string;
    user: UserItem | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Props {
    stores: PaginatedData<StoreItem>;
    filters: {
        status: string;
        search: string;
    };
    counts: {
        all: number;
        pending: number;
        active: number;
        suspended: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'SGM / Stores',
        href: index.url(),
    },
    {
        title: 'Stores Directory',
        href: index.url(),
    },
];

export default function StoresListPage({ stores, filters, counts }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [processingId, setProcessingId] = useState<number | null>(null);

    const handleFilterStatus = (status: string) => {
        router.get(
            index.url({
                query: { status, search: search.trim() || undefined },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            index.url({
                query: {
                    status: filters.status || 'all',
                    search: search.trim() || undefined,
                },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    const handleClearSearch = () => {
        setSearch('');
        router.get(
            index.url({ query: { status: filters.status || 'all' } }),
            {},
            { preserveState: true, replace: true },
        );
    };

    const handleApprove = (item: StoreItem) => {
        if (
            confirm(
                `Approve and activate store "${item.name || `Store #${item.id}`}"?`,
            )
        ) {
            setProcessingId(item.id);
            router.post(
                approve.url(item.id),
                {},
                {
                    preserveScroll: true,
                    onFinish: () => setProcessingId(null),
                },
            );
        }
    };

    const handleStatusChange = (item: StoreItem, newStatus: string) => {
        setProcessingId(item.id);
        router.post(
            updateStatus.url(item.id),
            {
                store_status: newStatus,
                is_approved: newStatus === 'active' ? true : item.is_approved,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessingId(null),
            },
        );
    };

    const getInitials = (name?: string | null) => {
        if (!name) return 'S';
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const renderStatusBadge = (status: string | null, isApproved: boolean) => {
        const normalized = status?.toLowerCase() || 'pending';
        if (!isApproved || normalized === 'pending') {
            return (
                <Badge className="gap-1.5 border-amber-500/20 bg-amber-500/15 font-medium text-amber-700 hover:bg-amber-500/20 dark:text-amber-400">
                    <Clock className="size-3.5" /> Pending
                </Badge>
            );
        }
        if (normalized === 'active') {
            return (
                <Badge className="gap-1.5 border-emerald-500/20 bg-emerald-500/15 font-medium text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-400">
                    <CheckCircle2 className="size-3.5" /> Active
                </Badge>
            );
        }
        if (normalized === 'suspended') {
            return (
                <Badge className="gap-1.5 border-rose-500/20 bg-rose-500/15 font-medium text-rose-700 hover:bg-rose-500/20 dark:text-rose-400">
                    <XCircle className="size-3.5" /> Suspended
                </Badge>
            );
        }
        return (
            <Badge variant="outline" className="gap-1.5 font-medium capitalize">
                {normalized}
            </Badge>
        );
    };

    return (
        <>
            <Head title="Stores Directory - Admin" />

            <div className="flex flex-1 flex-col gap-6 p-6">
                {/* Header Title & Metrics */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-foreground">
                            <StoreIcon className="size-6 text-primary" />
                            Stores Directory
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review, approve, and manage stores and status
                            changes on DropJdid.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Badge variant="outline" className="px-3 py-1 text-xs">
                            Total: {counts.all}
                        </Badge>
                        <Badge className="border-amber-500/20 bg-amber-500/15 px-3 py-1 text-xs text-amber-700 dark:text-amber-400">
                            {counts.pending} Pending
                        </Badge>
                        <Badge className="border-emerald-500/20 bg-emerald-500/15 px-3 py-1 text-xs text-emerald-700 dark:text-emerald-400">
                            {counts.active} Active
                        </Badge>
                        <Badge className="border-rose-500/20 bg-rose-500/15 px-3 py-1 text-xs text-rose-700 dark:text-rose-400">
                            {counts.suspended} Suspended
                        </Badge>
                    </div>
                </div>

                {/* Filters and Search Bar Card */}
                <Card>
                    <CardContent className="flex flex-col justify-between gap-4 p-4 md:flex-row md:items-center">
                        {/* Status Filter Tabs */}
                        <div className="flex flex-wrap items-center gap-1.5 rounded-lg bg-muted/60 p-1">
                            {[
                                { key: 'all', label: 'All', count: counts.all },
                                {
                                    key: 'pending',
                                    label: 'Pending',
                                    count: counts.pending,
                                },
                                {
                                    key: 'active',
                                    label: 'Active',
                                    count: counts.active,
                                },
                                {
                                    key: 'suspended',
                                    label: 'Suspended',
                                    count: counts.suspended,
                                },
                            ].map((tab) => {
                                const isActive =
                                    (filters.status || 'all') === tab.key;
                                return (
                                    <button
                                        key={tab.key}
                                        type="button"
                                        onClick={() =>
                                            handleFilterStatus(tab.key)
                                        }
                                        className={`flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${
                                            isActive
                                                ? 'bg-background text-foreground shadow-sm'
                                                : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
                                        }`}
                                    >
                                        {tab.label}
                                        <span
                                            className={`py-0.2 rounded-full px-1.5 text-[10px] ${
                                                isActive
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-muted text-muted-foreground'
                                            }`}
                                        >
                                            {tab.count}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>

                        {/* Search Input */}
                        <form
                            onSubmit={handleSearchSubmit}
                            className="flex min-w-[280px] items-center gap-2"
                        >
                            <div className="relative flex-1">
                                <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Search by store name, phone, owner..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="h-9 pr-8 pl-9 text-xs"
                                />
                                {search ? (
                                    <button
                                        type="button"
                                        onClick={handleClearSearch}
                                        className="absolute top-2.5 right-2.5 text-muted-foreground hover:text-foreground"
                                    >
                                        <X className="size-4" />
                                    </button>
                                ) : null}
                            </div>
                            <Button
                                type="submit"
                                size="sm"
                                variant="secondary"
                                className="h-9 text-xs"
                            >
                                Search
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Stores Table Card */}
                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/40 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-6 py-3.5">Store</th>
                                    <th className="px-6 py-3.5">
                                        Owner / User
                                    </th>
                                    <th className="px-6 py-3.5">
                                        Phone Number
                                    </th>
                                    <th className="px-6 py-3.5">Status</th>
                                    <th className="px-6 py-3.5">Approval</th>
                                    <th className="px-6 py-3.5">
                                        Change Status
                                    </th>
                                    <th className="px-6 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {stores.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-6 py-12 text-center"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <ShieldAlert className="size-10 text-muted-foreground/50" />
                                                <p className="text-base font-semibold text-foreground">
                                                    No stores found
                                                </p>
                                                <p className="max-w-sm text-xs text-muted-foreground">
                                                    {filters.search
                                                        ? `No stores match "${filters.search}". Try clearing your search.`
                                                        : 'There are currently no stores in this category.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    stores.data.map((item) => {
                                        const user = item.user;
                                        const isProcessing =
                                            processingId === item.id;
                                        const currentStatus =
                                            item.store_status || 'pending';

                                        return (
                                            <tr
                                                key={item.id}
                                                className="transition-colors hover:bg-muted/30"
                                            >
                                                {/* Store Info */}
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <Avatar className="size-9 border">
                                                            <AvatarImage
                                                                src={
                                                                    item.image_url ||
                                                                    undefined
                                                                }
                                                                alt={
                                                                    item.name ||
                                                                    'Store'
                                                                }
                                                            />
                                                            <AvatarFallback className="text-xs font-semibold">
                                                                {getInitials(
                                                                    item.name,
                                                                )}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <div className="flex flex-col">
                                                            <span className="text-sm leading-tight font-semibold text-foreground">
                                                                {item.name ||
                                                                    `Store #${item.id}`}
                                                            </span>
                                                            <span className="mt-0.5 text-xs text-muted-foreground">
                                                                ID: #{item.id}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Owner User Info */}
                                                <td className="px-6 py-4">
                                                    {user ? (
                                                        <div className="flex flex-col">
                                                            <span className="text-xs leading-tight font-medium text-foreground">
                                                                {user.full_name ||
                                                                    user.username ||
                                                                    'Unnamed'}
                                                            </span>
                                                            <span className="text-[11px] text-muted-foreground">
                                                                {user.email}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground italic">
                                                            Unlinked / No user
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Phone Number */}
                                                <td className="px-6 py-4 font-mono text-xs font-medium text-foreground">
                                                    {item.phone_number || '-'}
                                                </td>

                                                {/* Status Badge */}
                                                <td className="px-6 py-4">
                                                    {renderStatusBadge(
                                                        item.store_status,
                                                        item.is_approved,
                                                    )}
                                                </td>

                                                {/* Approval status */}
                                                <td className="px-6 py-4">
                                                    {item.is_approved ? (
                                                        <Badge
                                                            variant="outline"
                                                            className="border-emerald-600/30 text-[11px] text-emerald-600"
                                                        >
                                                            Approved
                                                        </Badge>
                                                    ) : (
                                                        <Badge
                                                            variant="outline"
                                                            className="border-amber-600/30 text-[11px] text-amber-600"
                                                        >
                                                            Not Approved
                                                        </Badge>
                                                    )}
                                                </td>

                                                {/* Change Status Select */}
                                                <td className="px-6 py-4">
                                                    <Select
                                                        value={currentStatus}
                                                        disabled={isProcessing}
                                                        onValueChange={(val) =>
                                                            handleStatusChange(
                                                                item,
                                                                val,
                                                            )
                                                        }
                                                    >
                                                        <SelectTrigger
                                                            size="sm"
                                                            className="h-8 min-w-[120px] text-xs"
                                                        >
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="active">
                                                                Active
                                                            </SelectItem>
                                                            <SelectItem value="pending">
                                                                Pending
                                                            </SelectItem>
                                                            <SelectItem value="suspended">
                                                                Suspended
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </td>

                                                {/* Actions */}
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {(!item.is_approved ||
                                                            item.store_status !==
                                                                'active') && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={
                                                                    isProcessing
                                                                }
                                                                onClick={() =>
                                                                    handleApprove(
                                                                        item,
                                                                    )
                                                                }
                                                                className="h-8 border-emerald-600/30 text-xs font-medium text-emerald-600 hover:bg-emerald-500/10 hover:text-emerald-700 dark:text-emerald-400"
                                                            >
                                                                <UserCheck className="mr-1 size-3.5" />
                                                                Approve &
                                                                Activate
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination Footer */}
                    {stores.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/20 px-6 py-4 text-xs text-muted-foreground sm:flex-row">
                            <div>
                                Showing{' '}
                                <span className="font-semibold text-foreground">
                                    {stores.from || 0}
                                </span>{' '}
                                to{' '}
                                <span className="font-semibold text-foreground">
                                    {stores.to || 0}
                                </span>{' '}
                                of{' '}
                                <span className="font-semibold text-foreground">
                                    {stores.total}
                                </span>{' '}
                                stores
                            </div>

                            <div className="flex items-center gap-1">
                                {stores.links.map((link, idx) => {
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={idx}
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                                className="cursor-not-allowed rounded-md px-3 py-1.5 text-xs text-muted-foreground/50"
                                            />
                                        );
                                    }

                                    return (
                                        <Link
                                            key={idx}
                                            href={link.url}
                                            preserveScroll
                                            preserveState
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                            className={`rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${
                                                link.active
                                                    ? 'bg-primary font-semibold text-primary-foreground shadow-sm'
                                                    : 'text-foreground hover:bg-accent'
                                            }`}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </Card>
            </div>
        </>
    );
}
