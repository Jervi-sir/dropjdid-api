import { Head, Link, router } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock,
    Search,
    ShieldAlert,
    Store,
    UserCheck,
    UserX,
    XCircle,
    X,
} from 'lucide-react';
import React, { useState } from 'react';
import {
    approve,
    index,
    reject,
} from '@/actions/App/Http/Controllers/Admin/Sgm/ListRequestController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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

interface SgmRequestItem {
    id: number;
    user_id: number;
    contact: string;
    type: string;
    target: string;
    status: 'pending' | 'approved' | 'rejected' | string;
    note: string | null;
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
    requests: PaginatedData<SgmRequestItem>;
    filters: {
        status: string;
        search: string;
    };
    counts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
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
        title: 'Store Requests',
        href: index.url(),
    },
];

export default function SgmRequestsPage({ requests, filters, counts }: Props) {
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

    const handleApprove = (item: SgmRequestItem) => {
        if (
            confirm(
                `Are you sure you want to approve store / SGM request for "${item.user?.full_name || item.user?.username || item.contact}"?`,
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

    const handleReject = (item: SgmRequestItem) => {
        if (
            confirm(
                `Are you sure you want to reject store / SGM request for "${item.user?.full_name || item.user?.username || item.contact}"?`,
            )
        ) {
            setProcessingId(item.id);
            router.post(
                reject.url(item.id),
                {},
                {
                    preserveScroll: true,
                    onFinish: () => setProcessingId(null),
                },
            );
        }
    };

    const getInitials = (name?: string | null) => {
        if (!name) return 'U';
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const renderStatusBadge = (status: string) => {
        switch (status) {
            case 'approved':
                return (
                    <Badge className="gap-1.5 border-emerald-500/20 bg-emerald-500/15 font-medium text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-400">
                        <CheckCircle2 className="size-3.5" /> Approved
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="gap-1.5 border-rose-500/20 bg-rose-500/15 font-medium text-rose-700 hover:bg-rose-500/20 dark:text-rose-400">
                        <XCircle className="size-3.5" /> Rejected
                    </Badge>
                );
            case 'pending':
            default:
                return (
                    <Badge className="gap-1.5 border-amber-500/20 bg-amber-500/15 font-medium text-amber-700 hover:bg-amber-500/20 dark:text-amber-400">
                        <Clock className="size-3.5" /> Pending
                    </Badge>
                );
        }
    };

    return (
        <>
            <Head title="Store / SGM Requests - Admin" />

            <div className="flex flex-1 flex-col gap-6 p-6">
                {/* Header Title & Metrics */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-foreground">
                            <Store className="size-6 text-primary" />
                            Store / SGM Requests
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review and manage user requests to open stores and
                            become Store General Managers (SGM) on DropJdid.
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
                            {counts.approved} Approved
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
                                    key: 'approved',
                                    label: 'Approved',
                                    count: counts.approved,
                                },
                                {
                                    key: 'rejected',
                                    label: 'Rejected',
                                    count: counts.rejected,
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
                                    placeholder="Search by name, email, contact..."
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

                {/* Requests Table Card */}
                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/40 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-6 py-3.5">User</th>
                                    <th className="px-6 py-3.5">
                                        Contact Phone
                                    </th>
                                    <th className="px-6 py-3.5">
                                        Current Roles
                                    </th>
                                    <th className="px-6 py-3.5">Status</th>
                                    <th className="px-6 py-3.5">
                                        Request Note
                                    </th>
                                    <th className="px-6 py-3.5">Date</th>
                                    <th className="px-6 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {requests.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-6 py-12 text-center"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <ShieldAlert className="size-10 text-muted-foreground/50" />
                                                <p className="text-base font-semibold text-foreground">
                                                    No store / SGM requests
                                                    found
                                                </p>
                                                <p className="max-w-sm text-xs text-muted-foreground">
                                                    {filters.search
                                                        ? `No requests match "${filters.search}". Try clearing your search.`
                                                        : 'There are currently no store / SGM requests in this category.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    requests.data.map((item) => {
                                        const user = item.user;
                                        const isProcessing =
                                            processingId === item.id;
                                        const roles = user?.roles || [];

                                        return (
                                            <tr
                                                key={item.id}
                                                className="transition-colors hover:bg-muted/30"
                                            >
                                                {/* User Info */}
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <Avatar className="size-9 border">
                                                            <AvatarImage
                                                                src={
                                                                    user?.image_url ||
                                                                    undefined
                                                                }
                                                                alt={
                                                                    user?.full_name ||
                                                                    'User'
                                                                }
                                                            />
                                                            <AvatarFallback className="text-xs font-semibold">
                                                                {getInitials(
                                                                    user?.full_name ||
                                                                        user?.username,
                                                                )}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <div className="flex flex-col">
                                                            <span className="text-sm leading-tight font-semibold text-foreground">
                                                                {user?.full_name ||
                                                                    user?.username ||
                                                                    'Unnamed User'}
                                                            </span>
                                                            <span className="mt-0.5 text-xs text-muted-foreground">
                                                                {user?.email ||
                                                                    'No email'}
                                                            </span>
                                                            {user?.username ? (
                                                                <span className="text-[11px] font-medium text-primary">
                                                                    @
                                                                    {
                                                                        user.username
                                                                    }
                                                                </span>
                                                            ) : null}
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Phone Number */}
                                                <td className="px-6 py-4 font-mono text-xs font-medium text-foreground">
                                                    {item.contact ||
                                                        user?.phone_number ||
                                                        '-'}
                                                </td>

                                                {/* Current Roles */}
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-wrap gap-1">
                                                        {roles.length > 0 ? (
                                                            roles.map(
                                                                (role) => (
                                                                    <Badge
                                                                        key={
                                                                            role.id
                                                                        }
                                                                        variant={
                                                                            role.code ===
                                                                            'sgm'
                                                                                ? 'default'
                                                                                : 'secondary'
                                                                        }
                                                                        className="px-2 py-0 text-[11px] capitalize"
                                                                    >
                                                                        {
                                                                            role.code
                                                                        }
                                                                    </Badge>
                                                                ),
                                                            )
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground italic">
                                                                No roles
                                                            </span>
                                                        )}
                                                    </div>
                                                </td>

                                                {/* Status */}
                                                <td className="px-6 py-4">
                                                    {renderStatusBadge(
                                                        item.status,
                                                    )}
                                                </td>

                                                {/* Note */}
                                                <td className="max-w-xs px-6 py-4">
                                                    {item.note ? (
                                                        <p
                                                            className="line-clamp-2 text-xs text-muted-foreground"
                                                            title={item.note}
                                                        >
                                                            {item.note}
                                                        </p>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground italic">
                                                            -
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Date */}
                                                <td className="px-6 py-4 text-xs whitespace-nowrap text-muted-foreground">
                                                    <div>
                                                        {new Date(
                                                            item.created_at,
                                                        ).toLocaleDateString()}
                                                    </div>
                                                    <div className="text-[10px] text-muted-foreground/70">
                                                        {new Date(
                                                            item.created_at,
                                                        ).toLocaleTimeString(
                                                            [],
                                                            {
                                                                hour: '2-digit',
                                                                minute: '2-digit',
                                                            },
                                                        )}
                                                    </div>
                                                </td>

                                                {/* Actions */}
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {item.status !==
                                                            'approved' && (
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
                                                                Approve
                                                            </Button>
                                                        )}

                                                        {item.status !==
                                                            'rejected' && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={
                                                                    isProcessing
                                                                }
                                                                onClick={() =>
                                                                    handleReject(
                                                                        item,
                                                                    )
                                                                }
                                                                className="h-8 border-rose-600/30 text-xs font-medium text-rose-600 hover:bg-rose-500/10 hover:text-rose-700 dark:text-rose-400"
                                                            >
                                                                <UserX className="mr-1 size-3.5" />
                                                                Reject
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
                    {requests.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/20 px-6 py-4 text-xs text-muted-foreground sm:flex-row">
                            <div>
                                Showing{' '}
                                <span className="font-semibold text-foreground">
                                    {requests.from || 0}
                                </span>{' '}
                                to{' '}
                                <span className="font-semibold text-foreground">
                                    {requests.to || 0}
                                </span>{' '}
                                of{' '}
                                <span className="font-semibold text-foreground">
                                    {requests.total}
                                </span>{' '}
                                requests
                            </div>

                            <div className="flex items-center gap-1">
                                {requests.links.map((link, idx) => {
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
