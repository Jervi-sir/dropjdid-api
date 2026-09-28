import { Head, Link, router } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock,
    Edit3,
    ExternalLink,
    Eye,
    Heart,
    Image as ImageIcon,
    Layers,
    MoreVertical,
    Package,
    PauseCircle,
    PlayCircle,
    RefreshCw,
    Repeat,
    Search,
    Share2,
    ShieldAlert,
    Trash2,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface CreatorUser {
    id: number;
    full_name: string | null;
    username: string | null;
    email: string;
    phone_number: string | null;
    image_url: string | null;
}

interface DropImageItem {
    id: number;
    image: string;
    is_main: boolean;
    sort_order: number;
}

interface DropProductItem {
    id: number;
    name: string;
    price?: string | number;
}

interface DropItem {
    id: number;
    creator_id: number;
    title: string;
    description: string | null;
    drop_status: 'draft' | 'new' | 'published' | 'rejected' | 'paused' | string;
    rejection_reason?: { reason?: string; rejected_at?: string } | null;
    primary_image_url?: string;
    created_at: string;
    updated_at: string;
    creator: CreatorUser | null;
    images: DropImageItem[];
    products: DropProductItem[];
    liked_users_count?: number;
    saved_users_count?: number;
    shares_count?: number;
    reposts_count?: number;
    products_count?: number;
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
    drops: PaginatedData<DropItem>;
    filters: {
        status: string;
        search: string;
    };
    counts: {
        all: number;
        new: number;
        published: number;
        draft: number;
        paused: number;
        rejected: number;
    };
    availableProducts: DropProductItem[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Creators',
        href: '/admin/creators/drops',
    },
    {
        title: 'Drops Management',
        href: '/admin/creators/drops',
    },
];

export default function AdminDropListPage({ drops, filters, counts }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [isUpdating, setIsUpdating] = useState(false);

    // Edit modal state
    const [editingDrop, setEditingDrop] = useState<DropItem | null>(null);
    const [editForm, setEditForm] = useState({
        title: '',
        description: '',
        drop_status: 'new',
        rejection_reason: '',
    });

    // Quick status reject dialog
    const [rejectingDrop, setRejectingDrop] = useState<DropItem | null>(null);
    const [rejectReason, setRejectReason] = useState('');

    const handleFilterStatus = (status: string) => {
        router.get(
            '/admin/creators/drops',
            {
                status,
                search: search.trim() || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/admin/creators/drops',
            {
                status: filters.status || 'all',
                search: search.trim() || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleClearSearch = () => {
        setSearch('');
        router.get(
            '/admin/creators/drops',
            { status: filters.status || 'all' },
            { preserveState: true, replace: true },
        );
    };

    const openEditModal = (drop: DropItem) => {
        setEditingDrop(drop);
        setEditForm({
            title: drop.title || '',
            description: drop.description || '',
            drop_status: drop.drop_status || 'new',
            rejection_reason: drop.rejection_reason?.reason || '',
        });
    };

    const handleSaveEdit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingDrop) return;

        setIsUpdating(true);
        router.put(
            `/admin/creators/drops/${editingDrop.id}`,
            {
                title: editForm.title,
                description: editForm.description,
                drop_status: editForm.drop_status,
                rejection_reason: editForm.rejection_reason,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingDrop(null);
                },
                onFinish: () => setIsUpdating(false),
            },
        );
    };

    const handleQuickStatusChange = (drop: DropItem, newStatus: string) => {
        if (newStatus === 'rejected') {
            setRejectingDrop(drop);
            setRejectReason('');
            return;
        }

        setIsUpdating(true);
        router.post(
            `/admin/creators/drops/${drop.id}/status`,
            { status: newStatus },
            {
                preserveScroll: true,
                onFinish: () => setIsUpdating(false),
            },
        );
    };

    const handleConfirmReject = (e: React.FormEvent) => {
        e.preventDefault();
        if (!rejectingDrop) return;

        setIsUpdating(true);
        router.post(
            `/admin/creators/drops/${rejectingDrop.id}/status`,
            {
                status: 'rejected',
                rejection_reason: rejectReason || 'Does not meet guidelines',
            },
            {
                preserveScroll: true,
                onSuccess: () => setRejectingDrop(null),
                onFinish: () => setIsUpdating(false),
            },
        );
    };

    const handleDeleteDrop = (drop: DropItem) => {
        if (
            confirm(
                `Are you sure you want to permanently delete "${drop.title || `Drop #${drop.id}`}"?`,
            )
        ) {
            router.delete(`/admin/creators/drops/${drop.id}`, {
                preserveScroll: true,
            });
        }
    };

    const renderStatusBadge = (status: string) => {
        switch (status) {
            case 'published':
                return (
                    <Badge className="gap-1.5 border-emerald-500/20 bg-emerald-500/15 font-medium text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-400">
                        <CheckCircle2 className="size-3.5" /> Published
                    </Badge>
                );
            case 'under-review':
                return (
                    <Badge className="gap-1.5 border-blue-500/20 bg-blue-500/15 font-medium text-blue-700 hover:bg-blue-500/20 dark:text-blue-400">
                        <Clock className="size-3.5" /> Under Review
                    </Badge>
                );
            case 'new':
                return (
                    <Badge className="gap-1.5 border-amber-500/20 bg-amber-500/15 font-medium text-amber-700 hover:bg-amber-500/20 dark:text-amber-400">
                        <Clock className="size-3.5" /> New / Pending
                    </Badge>
                );
            case 'paused':
                return (
                    <Badge className="gap-1.5 border-slate-500/20 bg-slate-500/15 font-medium text-slate-700 hover:bg-slate-500/20 dark:text-slate-400">
                        <PauseCircle className="size-3.5" /> Paused
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="gap-1.5 border-rose-500/20 bg-rose-500/15 font-medium text-rose-700 hover:bg-rose-500/20 dark:text-rose-400">
                        <XCircle className="size-3.5" /> Rejected
                    </Badge>
                );
            case 'draft':
            default:
                return (
                    <Badge
                        variant="outline"
                        className="gap-1.5 font-medium text-muted-foreground"
                    >
                        <Edit3 className="size-3.5" /> Draft
                    </Badge>
                );
        }
    };

    return (
        <>
            <Head title="Creator Drops Management - Admin" />

            <div className="flex flex-1 flex-col gap-6 p-6">
                {/* Header Title & Metrics */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-foreground">
                            <Layers className="size-6 text-primary" />
                            Creator Drops
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review, approve, edit details, and manage creator
                            drops status across DropJdid.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="outline" className="px-3 py-1 text-xs">
                            Total: {counts.all}
                        </Badge>
                        <Badge className="border-amber-500/20 bg-amber-500/15 px-3 py-1 text-xs text-amber-700 dark:text-amber-400">
                            {counts.new} New
                        </Badge>
                        <Badge className="border-emerald-500/20 bg-emerald-500/15 px-3 py-1 text-xs text-emerald-700 dark:text-emerald-400">
                            {counts.published} Published
                        </Badge>
                    </div>
                </div>

                {/* Status Tabs and Search */}
                <Card>
                    <CardContent className="flex flex-col justify-between gap-4 p-4 md:flex-row md:items-center">
                        <div className="flex flex-wrap items-center gap-1.5 rounded-lg bg-muted/60 p-1">
                            {[
                                { key: 'all', label: 'All', count: counts.all },
                                { key: 'new', label: 'New', count: counts.new },
                                {
                                    key: 'under-review',
                                    label: 'Under Review',
                                    count: (counts as any)['under-review'] ?? 0,
                                },
                                {
                                    key: 'published',
                                    label: 'Published',
                                    count: counts.published,
                                },
                                {
                                    key: 'draft',
                                    label: 'Drafts',
                                    count: counts.draft,
                                },
                                {
                                    key: 'paused',
                                    label: 'Paused',
                                    count: counts.paused,
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
                                    placeholder="Search by title, creator..."
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

                {/* Drops Table Card */}
                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/40 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-6 py-3.5">Drop</th>
                                    <th className="px-6 py-3.5">Creator</th>
                                    <th className="px-6 py-3.5">Status</th>
                                    <th className="px-6 py-3.5">Products</th>
                                    <th className="px-6 py-3.5">
                                        Engagement
                                    </th>
                                    <th className="px-6 py-3.5">Created At</th>
                                    <th className="px-6 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {drops.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-6 py-12 text-center"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <ShieldAlert className="size-10 text-muted-foreground/50" />
                                                <p className="text-base font-semibold text-foreground">
                                                    No drops found
                                                </p>
                                                <p className="max-w-sm text-xs text-muted-foreground">
                                                    {filters.search
                                                        ? `No drops match "${filters.search}". Try another keyword or filter.`
                                                        : 'There are currently no drops in this view.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    drops.data.map((drop) => {
                                        const creator = drop.creator;
                                        return (
                                            <tr
                                                key={drop.id}
                                                className="transition-colors hover:bg-muted/30"
                                            >
                                                {/* Drop Cover & Title */}
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="size-12 shrink-0 overflow-hidden rounded-lg border bg-muted">
                                                            {drop.primary_image_url ? (
                                                                <img
                                                                    src={
                                                                        drop.primary_image_url
                                                                    }
                                                                    alt={
                                                                        drop.title
                                                                    }
                                                                    className="size-full object-cover"
                                                                />
                                                            ) : (
                                                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                                                    <ImageIcon className="size-5" />
                                                                </div>
                                                            )}
                                                        </div>
                                                        <div className="flex flex-col">
                                                            <span className="text-sm leading-tight font-semibold text-foreground">
                                                                {drop.title ||
                                                                    `Drop #${drop.id}`}
                                                            </span>
                                                            <span className="mt-0.5 line-clamp-1 max-w-[220px] text-xs text-muted-foreground">
                                                                {drop.description ||
                                                                    'No description provided'}
                                                            </span>
                                                            <span className="font-mono text-[11px] text-muted-foreground/70">
                                                                ID: #{drop.id}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Creator */}
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-2.5">
                                                        <Avatar className="size-7 border">
                                                            <AvatarImage
                                                                src={
                                                                    creator?.image_url ||
                                                                    undefined
                                                                }
                                                            />
                                                            <AvatarFallback className="text-[10px] font-bold">
                                                                {creator?.username?.[0]?.toUpperCase() ||
                                                                    'C'}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <div className="flex flex-col">
                                                            <span className="text-xs leading-tight font-semibold text-foreground">
                                                                {creator?.full_name ||
                                                                    creator?.username ||
                                                                    'Creator'}
                                                            </span>
                                                            {creator?.username && (
                                                                <span className="text-[11px] font-medium text-primary">
                                                                    @
                                                                    {
                                                                        creator.username
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Status */}
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-col items-start gap-1">
                                                        {renderStatusBadge(
                                                            drop.drop_status,
                                                        )}
                                                        {drop.drop_status ===
                                                            'rejected' &&
                                                            drop
                                                                .rejection_reason
                                                                ?.reason && (
                                                                <span
                                                                    className="line-clamp-1 max-w-[150px] text-[11px] text-rose-500"
                                                                    title={
                                                                        drop
                                                                            .rejection_reason
                                                                            .reason
                                                                    }
                                                                >
                                                                    {
                                                                        drop
                                                                            .rejection_reason
                                                                            .reason
                                                                    }
                                                                </span>
                                                            )}
                                                    </div>
                                                </td>

                                                {/* Products Count */}
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-1 text-xs font-medium text-foreground">
                                                        <Package className="size-3.5 text-muted-foreground" />
                                                        <span>
                                                            {drop.products_count ??
                                                                drop.products
                                                                    ?.length ??
                                                                0}{' '}
                                                            Products
                                                        </span>
                                                    </div>
                                                </td>

                                                {/* Engagement: Likes, Saves, Shares, Reposts */}
                                                <td className="px-6 py-4 text-xs text-muted-foreground">
                                                    <div className="flex flex-wrap items-center gap-2.5">
                                                        <span
                                                            className="flex items-center gap-1 font-medium text-foreground"
                                                            title="Likes"
                                                        >
                                                            <Heart className="size-3 fill-rose-500 text-rose-500" />
                                                            {drop.liked_users_count ??
                                                                0}
                                                        </span>
                                                        <span
                                                            className="flex items-center gap-1 text-muted-foreground"
                                                            title="Saves"
                                                        >
                                                            <span className="text-[11px] font-semibold text-muted-foreground/80">
                                                                🔖
                                                            </span>
                                                            {drop.saved_users_count ??
                                                                0}
                                                        </span>
                                                        <span
                                                            className="flex items-center gap-1 text-muted-foreground"
                                                            title="Shares"
                                                        >
                                                            <Share2 className="size-3 text-sky-500" />
                                                            {drop.shares_count ??
                                                                0}
                                                        </span>
                                                        <span
                                                            className="flex items-center gap-1 text-muted-foreground"
                                                            title="Reposts"
                                                        >
                                                            <Repeat className="size-3 text-emerald-500" />
                                                            {drop.reposts_count ??
                                                                0}
                                                        </span>
                                                    </div>
                                                </td>

                                                {/* Date */}
                                                <td className="px-6 py-4 text-xs whitespace-nowrap text-muted-foreground">
                                                    <div>
                                                        {new Date(
                                                            drop.created_at,
                                                        ).toLocaleDateString()}
                                                    </div>
                                                    <div className="text-[10px] text-muted-foreground/70">
                                                        {new Date(
                                                            drop.created_at,
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
                                                        {/* Quick Action: Approve */}
                                                        {drop.drop_status !==
                                                            'published' && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={
                                                                    isUpdating
                                                                }
                                                                onClick={() =>
                                                                    handleQuickStatusChange(
                                                                        drop,
                                                                        'published',
                                                                    )
                                                                }
                                                                className="h-8 border-emerald-600/30 text-xs font-medium text-emerald-600 hover:bg-emerald-500/10 hover:text-emerald-700 dark:text-emerald-400"
                                                            >
                                                                <CheckCircle2 className="mr-1 size-3.5" />
                                                                Approve
                                                            </Button>
                                                        )}

                                                        {/* Edit Button */}
                                                        <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            onClick={() =>
                                                                openEditModal(
                                                                    drop,
                                                                )
                                                            }
                                                            className="h-8 text-xs font-medium"
                                                        >
                                                            <Edit3 className="mr-1 size-3.5" />
                                                            Edit
                                                        </Button>

                                                        {/* More Options Dropdown */}
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger
                                                                asChild
                                                            >
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="size-8"
                                                                >
                                                                    <MoreVertical className="size-4" />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent
                                                                align="end"
                                                                className="w-44"
                                                            >
                                                                <DropdownMenuLabel className="text-xs">
                                                                    Drop Actions
                                                                </DropdownMenuLabel>
                                                                <DropdownMenuSeparator />

                                                                {drop.drop_status ===
                                                                    'published' && (
                                                                    <DropdownMenuItem
                                                                        onClick={() =>
                                                                            handleQuickStatusChange(
                                                                                drop,
                                                                                'paused',
                                                                            )
                                                                        }
                                                                    >
                                                                        <PauseCircle className="mr-2 size-4 text-slate-500" />
                                                                        Pause
                                                                        Drop
                                                                    </DropdownMenuItem>
                                                                )}

                                                                {drop.drop_status ===
                                                                    'paused' && (
                                                                    <DropdownMenuItem
                                                                        onClick={() =>
                                                                            handleQuickStatusChange(
                                                                                drop,
                                                                                'published',
                                                                            )
                                                                        }
                                                                    >
                                                                        <PlayCircle className="mr-2 size-4 text-emerald-500" />
                                                                        Resume /
                                                                        Publish
                                                                    </DropdownMenuItem>
                                                                )}

                                                                {drop.drop_status !==
                                                                    'rejected' && (
                                                                    <DropdownMenuItem
                                                                        onClick={() =>
                                                                            handleQuickStatusChange(
                                                                                drop,
                                                                                'rejected',
                                                                            )
                                                                        }
                                                                    >
                                                                        <XCircle className="mr-2 size-4 text-rose-500" />
                                                                        Reject
                                                                        Drop
                                                                    </DropdownMenuItem>
                                                                )}

                                                                {drop.drop_status !==
                                                                    'draft' && (
                                                                    <DropdownMenuItem
                                                                        onClick={() =>
                                                                            handleQuickStatusChange(
                                                                                drop,
                                                                                'draft',
                                                                            )
                                                                        }
                                                                    >
                                                                        <Edit3 className="mr-2 size-4 text-muted-foreground" />
                                                                        Mark as
                                                                        Draft
                                                                    </DropdownMenuItem>
                                                                )}

                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    onClick={() =>
                                                                        handleDeleteDrop(
                                                                            drop,
                                                                        )
                                                                    }
                                                                    className="text-rose-600 focus:bg-rose-500/10 focus:text-rose-600"
                                                                >
                                                                    <Trash2 className="mr-2 size-4" />
                                                                    Delete Drop
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
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
                    {drops.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/20 px-6 py-4 text-xs text-muted-foreground sm:flex-row">
                            <div>
                                Showing{' '}
                                <span className="font-semibold text-foreground">
                                    {drops.from || 0}
                                </span>{' '}
                                to{' '}
                                <span className="font-semibold text-foreground">
                                    {drops.to || 0}
                                </span>{' '}
                                of{' '}
                                <span className="font-semibold text-foreground">
                                    {drops.total}
                                </span>{' '}
                                drops
                            </div>

                            <div className="flex items-center gap-1">
                                {drops.links.map((link, idx) => {
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

            {/* Edit Drop Dialog Modal */}
            <Dialog
                open={!!editingDrop}
                onOpenChange={(open) => !open && setEditingDrop(null)}
            >
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Edit Drop #{editingDrop?.id}</DialogTitle>
                        <DialogDescription>
                            Update drop name, description, and status for
                            creator @
                            {editingDrop?.creator?.username || 'creator'}.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSaveEdit} className="space-y-4 py-2">
                        {/* Title */}
                        <div className="space-y-1.5">
                            <Label htmlFor="drop-title">
                                Drop Name (Username format)
                            </Label>
                            <Input
                                id="drop-title"
                                value={editForm.title}
                                onChange={(e) =>
                                    setEditForm((prev) => ({
                                        ...prev,
                                        title: e.target.value
                                            .toLowerCase()
                                            .replace(/\s+/g, '_'),
                                    }))
                                }
                                placeholder="e.g. vintage_summer"
                                required
                            />
                        </div>

                        {/* Status */}
                        <div className="space-y-1.5">
                            <Label htmlFor="drop-status">Status</Label>
                            <Select
                                value={editForm.drop_status}
                                onValueChange={(val) =>
                                    setEditForm((prev) => ({
                                        ...prev,
                                        drop_status: val,
                                    }))
                                }
                            >
                                <SelectTrigger
                                    id="drop-status"
                                    className="w-full"
                                >
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="new">
                                        New / Pending
                                    </SelectItem>
                                    <SelectItem value="under-review">
                                        Under Review
                                    </SelectItem>
                                    <SelectItem value="published">
                                        Published / Approved
                                    </SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="paused">
                                        Paused
                                    </SelectItem>
                                    <SelectItem value="rejected">
                                        Rejected
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Rejection Reason (shown if rejected) */}
                        {editForm.drop_status === 'rejected' && (
                            <div className="space-y-1.5">
                                <Label htmlFor="drop-reject-reason">
                                    Rejection Reason
                                </Label>
                                <Textarea
                                    id="drop-reject-reason"
                                    value={editForm.rejection_reason}
                                    onChange={(e) =>
                                        setEditForm((prev) => ({
                                            ...prev,
                                            rejection_reason: e.target.value,
                                        }))
                                    }
                                    placeholder="Explain why this drop was rejected..."
                                    rows={3}
                                />
                            </div>
                        )}

                        {/* Description */}
                        <div className="space-y-1.5">
                            <Label htmlFor="drop-description">
                                Description
                            </Label>
                            <Textarea
                                id="drop-description"
                                value={editForm.description}
                                onChange={(e) =>
                                    setEditForm((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                                placeholder="Drop description..."
                                rows={4}
                            />
                        </div>

                        <DialogFooter className="pt-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setEditingDrop(null)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={isUpdating}>
                                {isUpdating ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Reject Drop Quick Modal */}
            <Dialog
                open={!!rejectingDrop}
                onOpenChange={(open) => !open && setRejectingDrop(null)}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>
                            Reject Drop #{rejectingDrop?.id}
                        </DialogTitle>
                        <DialogDescription>
                            Please provide a reason for rejecting this drop.
                        </DialogDescription>
                    </DialogHeader>

                    <form
                        onSubmit={handleConfirmReject}
                        className="space-y-4 py-2"
                    >
                        <div className="space-y-1.5">
                            <Label htmlFor="quick-reject-reason">Reason</Label>
                            <Textarea
                                id="quick-reject-reason"
                                value={rejectReason}
                                onChange={(e) =>
                                    setRejectReason(e.target.value)
                                }
                                placeholder="E.g. Inappropriate images, missing details, copyright issue..."
                                rows={3}
                                required
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setRejectingDrop(null)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={isUpdating}
                            >
                                {isUpdating
                                    ? 'Rejecting...'
                                    : 'Confirm Rejection'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
