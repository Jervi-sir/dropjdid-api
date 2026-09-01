import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    Archive,
    CheckCircle2,
    Clock,
    DollarSign,
    Eye,
    Layers,
    Package,
    RefreshCw,
    Search,
    ShieldAlert,
    Tag,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
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

interface ImageItem {
    id: number;
    image_url: string;
    is_main: boolean;
    sort_order: number;
}

interface SizeItem {
    id: number;
    code: string;
    en?: string;
    fr?: string;
    ar?: string;
}

interface VariantItem {
    id: number;
    size_id: number;
    stock: number;
    size?: SizeItem;
}

interface LabelItem {
    id: number;
    name?: string;
    en?: string;
    fr?: string;
    ar?: string;
}

interface StoreItem {
    id: number;
    name: string | null;
    phone_number: string | null;
    image_url: string | null;
}

interface CategoryItem {
    id: number;
    name?: string;
    en?: string;
    fr?: string;
    ar?: string;
}

interface QualityItem {
    id: number;
    name?: string;
    en?: string;
    fr?: string;
    ar?: string;
}

interface GenderItem {
    id: number;
    name?: string;
    en?: string;
    fr?: string;
    ar?: string;
}

interface ProductItem {
    id: number;
    store_id: number;
    category_id: number | null;
    gender_id: number | null;
    quality_id: number | null;
    name: string;
    description: string | null;
    price_original: string | number;
    price_shown: string | number;
    price_store: string | number;
    product_status: 'draft' | 'published' | 'archived' | 'rejected' | string;
    rejection_reason: { en?: string; fr?: string; ar?: string } | string | null;
    is_affiliate: boolean;
    refreshed_at: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
    store?: StoreItem | null;
    category?: CategoryItem | null;
    gender?: GenderItem | null;
    quality?: QualityItem | null;
    images?: ImageItem[];
    variants?: VariantItem[];
    labels?: LabelItem[];
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
    products: PaginatedData<ProductItem>;
    filters: {
        status: string;
        search: string;
        store_id?: string | null;
    };
    counts: {
        all: number;
        draft: number;
        published: number;
        rejected: number;
        archived: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'SGM / Stores',
        href: '/admin/sgm/stores',
    },
    {
        title: 'Products Review',
        href: '/admin/sgm/products',
    },
];

export default function ListProducts({ products, filters, counts }: Props) {
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [activeStatus, setActiveStatus] = useState(filters.status || 'all');
    const [selectedProduct, setSelectedProduct] = useState<ProductItem | null>(
        null,
    );
    const [previewImage, setPreviewImage] = useState<string | null>(null);

    // Modal state for Reject / Archive comments from Card buttons
    const [reasonModalOpen, setReasonModalOpen] = useState(false);
    const [modalActionType, setModalActionType] = useState<
        'reject' | 'archive'
    >('reject');
    const [reasonText, setReasonText] = useState('');
    const [actionLoading, setActionLoading] = useState(false);

    // Detail dialog inline status state
    const [targetStatus, setTargetStatus] = useState<string>('');
    const [inlineReason, setInlineReason] = useState<string>('');

    // Sync target status when opening a product
    const handleSelectProduct = (product: ProductItem | null) => {
        setSelectedProduct(product);
        if (product) {
            setTargetStatus(product.product_status);
            setInlineReason('');
            setPreviewImage(null);
        }
    };

    // Apply filter helper
    const handleFilter = (newStatus?: string, newSearch?: string) => {
        const query: Record<string, string> = {};
        const statusToApply =
            newStatus !== undefined ? newStatus : activeStatus;
        const searchToApply = newSearch !== undefined ? newSearch : searchQuery;

        if (statusToApply && statusToApply !== 'all') {
            query.status = statusToApply;
        }
        if (searchToApply.trim() !== '') {
            query.search = searchToApply.trim();
        }

        router.get('/admin/sgm/products', query, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter(activeStatus, searchQuery);
    };

    const handleClearFilters = () => {
        setSearchQuery('');
        setActiveStatus('all');
        router.get(
            '/admin/sgm/products',
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    // Approve Action
    const handleApprove = (product: ProductItem) => {
        if (
            confirm(
                `Are you sure you want to approve and publish "${product.name}"?`,
            )
        ) {
            setActionLoading(true);
            router.post(
                `/admin/sgm/products/${product.id}/approve`,
                {},
                {
                    preserveScroll: true,
                    onFinish: () => {
                        setActionLoading(false);
                        if (selectedProduct?.id === product.id) {
                            handleSelectProduct(null);
                        }
                    },
                },
            );
        }
    };

    // Open Reject Dialog Modal (from card quick buttons)
    const openRejectModal = (product: ProductItem) => {
        handleSelectProduct(product);
        setModalActionType('reject');
        setReasonText('');
        setReasonModalOpen(true);
    };

    // Open Archive Dialog Modal (from card quick buttons)
    const openArchiveModal = (product: ProductItem) => {
        handleSelectProduct(product);
        setModalActionType('archive');
        setReasonText('');
        setReasonModalOpen(true);
    };

    // Handle Confirm Reason from Modal
    const handleReasonSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedProduct || !reasonText.trim()) return;

        setActionLoading(true);
        const endpoint =
            modalActionType === 'reject'
                ? `/admin/sgm/products/${selectedProduct.id}/reject`
                : `/admin/sgm/products/${selectedProduct.id}/archive`;

        router.post(
            endpoint,
            { reason: reasonText.trim() },
            {
                preserveScroll: true,
                onFinish: () => {
                    setActionLoading(false);
                    setReasonModalOpen(false);
                    handleSelectProduct(null);
                },
            },
        );
    };

    // Detail dialog inline status submit
    const handleInlineStatusSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedProduct || !targetStatus) return;

        const isReasonRequired =
            targetStatus === 'rejected' || targetStatus === 'archived';
        if (isReasonRequired && !inlineReason.trim()) {
            return;
        }

        setActionLoading(true);
        router.post(
            `/admin/sgm/products/${selectedProduct.id}/status`,
            {
                product_status: targetStatus,
                reason: inlineReason.trim() || undefined,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    setActionLoading(false);
                    handleSelectProduct(null);
                },
            },
        );
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'published':
                return (
                    <Badge className="inline-flex items-center gap-1 border-emerald-200 bg-emerald-500/15 font-medium text-emerald-700 hover:bg-emerald-500/25 dark:border-emerald-800 dark:text-emerald-300">
                        <CheckCircle2 className="h-3.5 w-3.5" /> Published
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge className="inline-flex items-center gap-1 border-amber-200 bg-amber-500/15 font-medium text-amber-700 hover:bg-amber-500/25 dark:border-amber-800 dark:text-amber-300">
                        <Clock className="h-3.5 w-3.5" /> Draft / Pending
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="inline-flex items-center gap-1 border-rose-200 bg-rose-500/15 font-medium text-rose-700 hover:bg-rose-500/25 dark:border-rose-800 dark:text-rose-300">
                        <XCircle className="h-3.5 w-3.5" /> Rejected
                    </Badge>
                );
            case 'archived':
                return (
                    <Badge className="inline-flex items-center gap-1 border-slate-200 bg-slate-500/15 font-medium text-slate-700 hover:bg-slate-500/25 dark:border-slate-800 dark:text-slate-300">
                        <Archive className="h-3.5 w-3.5" /> Archived
                    </Badge>
                );
            default:
                return (
                    <Badge variant="outline" className="font-medium capitalize">
                        {status}
                    </Badge>
                );
        }
    };

    const formatReason = (reason: any) => {
        if (!reason) return null;
        if (typeof reason === 'string') return reason;
        if (Array.isArray(reason)) {
            const lines = reason
                .map((item: any) => {
                    const inst = item?.instruction || item;
                    if (typeof inst === 'string') return inst;
                    return inst?.en || inst?.fr || inst?.ar || '';
                })
                .filter(Boolean);
            if (lines.length > 0) {
                return lines.join('\n');
            }
        }
        return reason.en || reason.fr || reason.ar || reason.message || JSON.stringify(reason);
    };

    const getLocalizedName = (
        item:
            | { en?: string; fr?: string; ar?: string; name?: string }
            | null
            | undefined,
    ) => {
        if (!item) return null;
        return item.en || item.fr || item.ar || item.name || null;
    };

    return (
        <>
            <Head title="Products Review & Approval" />

            <div className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-6 p-4 md:p-8">
                {/* Header Title Section */}
                <div className="flex flex-col justify-between gap-4 border-b border-border pb-5 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                            <Package className="h-7 w-7 text-primary" />
                            Products Review
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review, verify and approve store products before
                            publishing to the marketplace catalog.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.reload()}
                            className="gap-1.5"
                        >
                            <RefreshCw className="h-4 w-4" /> Refresh
                        </Button>
                    </div>
                </div>

                {/* Filter and Search Bar */}
                <div className="flex flex-col gap-4">
                    {/* Status Tabs */}
                    <div className="flex flex-wrap items-center gap-2">
                        {[
                            {
                                key: 'all',
                                label: 'All Products',
                                count: counts.all,
                                icon: Layers,
                            },
                            {
                                key: 'draft',
                                label: 'Draft / Pending',
                                count: counts.draft,
                                icon: Clock,
                            },
                            {
                                key: 'published',
                                label: 'Published',
                                count: counts.published,
                                icon: CheckCircle2,
                            },
                            {
                                key: 'rejected',
                                label: 'Rejected',
                                count: counts.rejected,
                                icon: XCircle,
                            },
                            {
                                key: 'archived',
                                label: 'Archived',
                                count: counts.archived,
                                icon: Archive,
                            },
                        ].map((tab) => {
                            const TabIcon = tab.icon;
                            const isActive = activeStatus === tab.key;
                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => {
                                        setActiveStatus(tab.key);
                                        handleFilter(tab.key, searchQuery);
                                    }}
                                    className={`flex items-center gap-2 rounded-xl border px-3.5 py-2 text-xs font-semibold transition-all ${
                                        isActive
                                            ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                            : 'border-border bg-card text-muted-foreground hover:border-foreground/20 hover:text-foreground'
                                    }`}
                                >
                                    <TabIcon className="h-3.5 w-3.5" />
                                    <span>{tab.label}</span>
                                    <span
                                        className={`rounded-full px-1.5 py-0.5 text-[10px] font-bold ${
                                            isActive
                                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                                : 'bg-muted text-muted-foreground'
                                        }`}
                                    >
                                        {tab.count}
                                    </span>
                                </button>
                            );
                        })}
                    </div>

                    {/* Search & Actions */}
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <form
                            onSubmit={handleSearchSubmit}
                            className="relative flex-1"
                        >
                            <Search className="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search by product name, description, store or category..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pr-8 pl-9"
                            />
                            {searchQuery && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearchQuery('');
                                        handleFilter(activeStatus, '');
                                    }}
                                    className="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            )}
                        </form>

                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                onClick={handleSearchSubmit}
                                variant="secondary"
                            >
                                Search
                            </Button>
                            {(activeStatus !== 'all' || searchQuery !== '') && (
                                <Button
                                    type="button"
                                    onClick={handleClearFilters}
                                    variant="ghost"
                                    size="sm"
                                    className="text-xs text-muted-foreground hover:text-foreground"
                                >
                                    Reset Filters
                                </Button>
                            )}
                        </div>
                    </div>
                </div>

                {/* Product Grid / Listings */}
                {products.data.length === 0 ? (
                    <Card className="border-dashed py-14">
                        <CardContent className="flex flex-col items-center justify-center text-center">
                            <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground">
                                <Package className="h-7 w-7" />
                            </div>
                            <h3 className="text-base font-semibold text-foreground">
                                No products found
                            </h3>
                            <p className="mt-1 mb-4 max-w-sm text-sm text-muted-foreground">
                                {searchQuery || activeStatus !== 'all'
                                    ? 'No products match your active search and filter criteria.'
                                    : 'There are currently no store products to review.'}
                            </p>
                            {(searchQuery || activeStatus !== 'all') && (
                                <Button
                                    onClick={handleClearFilters}
                                    variant="outline"
                                    size="sm"
                                >
                                    Clear all filters
                                </Button>
                            )}
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.data.map((product) => {
                            const mainImg =
                                product.images?.find((img) => img.is_main)
                                    ?.image_url ||
                                product.images?.[0]?.image_url;
                            const totalStock =
                                product.variants?.reduce(
                                    (sum, v) => sum + (v.stock || 0),
                                    0,
                                ) ?? 0;
                            const categoryName = getLocalizedName(
                                product.category,
                            );

                            return (
                                <Card
                                    key={product.id}
                                    className="group flex flex-col overflow-hidden border-border/80 transition-all hover:shadow-md"
                                >
                                    {/* Image & Badges Banner */}
                                    <div className="relative flex aspect-4/3 w-full items-center justify-center overflow-hidden border-b border-border/50 bg-muted/40">
                                        {mainImg ? (
                                            <img
                                                src={mainImg}
                                                alt={product.name}
                                                className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex flex-col items-center justify-center p-4 text-muted-foreground/60">
                                                <Package className="mb-1 h-10 w-10 stroke-1" />
                                                <span className="text-xs">
                                                    No image
                                                </span>
                                            </div>
                                        )}

                                        {/* Status badge top-left */}
                                        <div className="absolute top-2.5 left-2.5">
                                            {getStatusBadge(
                                                product.product_status,
                                            )}
                                        </div>

                                        {/* Affiliate indicator top-right */}
                                        {product.is_affiliate && (
                                            <div className="absolute top-2.5 right-2.5">
                                                <Badge className="bg-violet-600 px-2 py-0.5 text-[10px] text-white">
                                                    Affiliate
                                                </Badge>
                                            </div>
                                        )}

                                        {/* Image count badge */}
                                        {product.images &&
                                            product.images.length > 1 && (
                                                <div className="absolute right-2.5 bottom-2.5 flex items-center gap-1 rounded bg-black/60 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur-sm">
                                                    <span>
                                                        {product.images.length}{' '}
                                                        photos
                                                    </span>
                                                </div>
                                            )}
                                    </div>

                                    {/* Product Body */}
                                    <CardContent className="flex flex-1 flex-col justify-between gap-3 p-4">
                                        <div className="space-y-2">
                                            {/* Store Information & ID */}
                                            <div className="flex items-center justify-between gap-2">
                                                {product.store && (
                                                    <div className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground truncate">
                                                        <Avatar className="h-4 w-4 rounded-full">
                                                            <AvatarImage
                                                                src={
                                                                    product.store
                                                                        .image_url ||
                                                                    undefined
                                                                }
                                                            />
                                                            <AvatarFallback className="text-[9px]">
                                                                {product.store.name
                                                                    ?.substring(
                                                                        0,
                                                                        2,
                                                                    )
                                                                    .toUpperCase() ||
                                                                    'ST'}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <span className="truncate">
                                                            {product.store.name ||
                                                                'Store'}
                                                        </span>
                                                    </div>
                                                )}
                                                <span className="shrink-0 rounded bg-muted/80 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-muted-foreground">
                                                    #{product.id}
                                                </span>
                                            </div>

                                            {/* Title & Category */}
                                            <div>
                                                <h3
                                                    className="line-clamp-1 text-sm font-semibold text-foreground"
                                                    title={product.name}
                                                >
                                                    {product.name}
                                                </h3>
                                                {categoryName && (
                                                    <span className="mt-0.5 flex items-center gap-1 text-[11px] text-muted-foreground">
                                                        <Tag className="h-3 w-3" />
                                                        {categoryName}
                                                    </span>
                                                )}
                                            </div>

                                            {/* Pricing & Stock */}
                                            <div className="flex items-baseline justify-between border-t border-border/40 pt-1">
                                                <div className="flex items-baseline gap-1.5">
                                                    <span className="text-base font-bold text-foreground">
                                                        {Number(
                                                            product.price_shown,
                                                        ).toLocaleString()}{' '}
                                                        DA
                                                    </span>
                                                    {Number(
                                                        product.price_original,
                                                    ) >
                                                        Number(
                                                            product.price_shown,
                                                        ) && (
                                                        <span className="text-xs text-muted-foreground line-through">
                                                            {Number(
                                                                product.price_original,
                                                            ).toLocaleString()}{' '}
                                                            DA
                                                        </span>
                                                    )}
                                                </div>
                                                <span className="text-[11px] text-muted-foreground">
                                                    {product.variants &&
                                                    product.variants.length > 0
                                                        ? `${totalStock} in stock`
                                                        : 'No stock'}
                                                </span>
                                            </div>

                                            {/* Variants/Sizes Preview */}
                                            {product.variants &&
                                                product.variants.length > 0 && (
                                                    <div className="flex flex-wrap gap-1 pt-1">
                                                        {product.variants
                                                            .slice(0, 4)
                                                            .map((variant) => (
                                                                <span
                                                                    key={
                                                                        variant.id
                                                                    }
                                                                    className="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] text-muted-foreground"
                                                                >
                                                                    {variant
                                                                        .size
                                                                        ?.code ||
                                                                        variant
                                                                            .size
                                                                            ?.en ||
                                                                        'Size'}
                                                                    :{' '}
                                                                    {
                                                                        variant.stock
                                                                    }
                                                                </span>
                                                            ))}
                                                        {product.variants
                                                            .length > 4 && (
                                                            <span className="rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                                                +
                                                                {product
                                                                    .variants
                                                                    .length -
                                                                    4}{' '}
                                                                more
                                                            </span>
                                                        )}
                                                    </div>
                                                )}

                                            {/* Reason Note Warning if Rejected or Archived */}
                                            {(product.product_status ===
                                                'rejected' ||
                                                product.product_status ===
                                                    'archived') &&
                                                product.rejection_reason && (
                                                    <div
                                                        className={`flex items-start gap-1.5 rounded border p-2 text-[11px] ${
                                                            product.product_status ===
                                                            'rejected'
                                                                ? 'border-rose-200 bg-rose-500/10 text-rose-700 dark:border-rose-900/50 dark:text-rose-300'
                                                                : 'border-slate-200 bg-slate-500/10 text-slate-700 dark:border-slate-900/50 dark:text-slate-300'
                                                        }`}
                                                    >
                                                        <AlertCircle className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                                        <p className="line-clamp-2">
                                                            <span className="font-semibold capitalize">
                                                                {
                                                                    product.product_status
                                                                }{' '}
                                                                note:{' '}
                                                            </span>
                                                            {formatReason(
                                                                product.rejection_reason,
                                                            )}
                                                        </p>
                                                    </div>
                                                )}
                                        </div>

                                        {/* Action Buttons */}
                                        <div className="flex items-center justify-between gap-1.5 border-t border-border pt-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    handleSelectProduct(product)
                                                }
                                                className="h-8 flex-1 gap-1 text-xs"
                                            >
                                                <Eye className="h-3.5 w-3.5" />{' '}
                                                Review
                                            </Button>

                                            {product.product_status !==
                                                'published' && (
                                                <Button
                                                    variant="default"
                                                    size="sm"
                                                    disabled={actionLoading}
                                                    onClick={() =>
                                                        handleApprove(product)
                                                    }
                                                    className="h-8 bg-emerald-600 px-2.5 text-xs text-white hover:bg-emerald-700"
                                                    title="Approve and Publish"
                                                >
                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                </Button>
                                            )}

                                            {product.product_status !==
                                                'rejected' && (
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    disabled={actionLoading}
                                                    onClick={() =>
                                                        openRejectModal(product)
                                                    }
                                                    className="h-8 px-2.5 text-xs"
                                                    title="Reject Product with Reason"
                                                >
                                                    <XCircle className="h-3.5 w-3.5" />
                                                </Button>
                                            )}

                                            {product.product_status !==
                                                'archived' && (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    disabled={actionLoading}
                                                    onClick={() =>
                                                        openArchiveModal(
                                                            product,
                                                        )
                                                    }
                                                    className="h-8 px-2.5 text-xs hover:bg-slate-200 dark:hover:bg-slate-800"
                                                    title="Archive Product with Reason"
                                                >
                                                    <Archive className="h-3.5 w-3.5" />
                                                </Button>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}

                {/* Pagination Controls */}
                {products.links && products.links.length > 3 && (
                    <div className="flex flex-col items-center justify-between gap-4 border-t border-border pt-4 sm:flex-row">
                        <div className="text-xs text-muted-foreground">
                            Showing{' '}
                            <span className="font-semibold text-foreground">
                                {products.from || 0}
                            </span>{' '}
                            to{' '}
                            <span className="font-semibold text-foreground">
                                {products.to || 0}
                            </span>{' '}
                            of{' '}
                            <span className="font-semibold text-foreground">
                                {products.total}
                            </span>{' '}
                            products
                        </div>

                        <div className="flex flex-wrap items-center justify-center gap-1">
                            {products.links.map((link, idx) => {
                                if (!link.url) {
                                    return (
                                        <span
                                            key={idx}
                                            className="border border-transparent px-3 py-1.5 text-xs text-muted-foreground/50 select-none"
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    );
                                }

                                return (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() =>
                                            router.get(
                                                link.url!,
                                                {},
                                                {
                                                    preserveState: true,
                                                    preserveScroll: true,
                                                },
                                            )
                                        }
                                        className={`rounded-lg border px-3 py-1.5 text-xs transition-all ${
                                            link.active
                                                ? 'border-primary bg-primary font-semibold text-primary-foreground'
                                                : 'border-border bg-card text-foreground hover:bg-muted'
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            {/* Product Detail & Review Modal Dialog */}
            <Dialog
                open={!!selectedProduct && !reasonModalOpen}
                onOpenChange={(open) => !open && handleSelectProduct(null)}
            >
                <DialogContent className="max-h-[90vh] max-w-3xl overflow-y-auto">
                    {selectedProduct && (
                        <div className="space-y-6">
                            <DialogHeader>
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <div className="mb-1.5 flex items-center gap-2 flex-wrap">
                                            <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-bold text-foreground">
                                                ID: #{selectedProduct.id}
                                            </span>
                                            {getStatusBadge(
                                                selectedProduct.product_status,
                                            )}
                                            {selectedProduct.is_affiliate && (
                                                <Badge className="bg-violet-600 text-xs text-white">
                                                    Affiliate
                                                </Badge>
                                            )}
                                        </div>
                                        <DialogTitle className="text-xl font-bold">
                                            {selectedProduct.name}
                                        </DialogTitle>
                                        <DialogDescription className="mt-1">
                                            Store:{' '}
                                            <span className="font-medium text-foreground">
                                                {selectedProduct.store?.name ||
                                                    'N/A'}
                                            </span>
                                            {selectedProduct.store
                                                ?.phone_number &&
                                                ` • ${selectedProduct.store.phone_number}`}
                                        </DialogDescription>
                                    </div>
                                </div>
                            </DialogHeader>

                            {/* Images Gallery */}
                            {selectedProduct.images &&
                                selectedProduct.images.length > 0 && (
                                    <div className="space-y-3">
                                        <h4 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                            Product Images
                                        </h4>
                                        <div className="grid grid-cols-4 gap-2 sm:grid-cols-6">
                                            {selectedProduct.images.map(
                                                (img) => (
                                                    <button
                                                        key={img.id}
                                                        type="button"
                                                        onClick={() =>
                                                            setPreviewImage(
                                                                img.image_url,
                                                            )
                                                        }
                                                        className={`relative aspect-square overflow-hidden rounded-lg border-2 transition-all ${
                                                            img.is_main
                                                                ? 'border-primary'
                                                                : 'border-border hover:border-foreground/40'
                                                        }`}
                                                    >
                                                        <img
                                                            src={img.image_url}
                                                            alt=""
                                                            className="h-full w-full object-cover"
                                                        />
                                                        {img.is_main && (
                                                            <span className="absolute right-1 bottom-1 left-1 rounded bg-primary py-0.5 text-center text-[8px] font-bold text-primary-foreground">
                                                                Main
                                                            </span>
                                                        )}
                                                    </button>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}

                            {/* Full Image Preview if clicked */}
                            {previewImage && (
                                <div className="relative flex aspect-video max-h-72 w-full items-center justify-center overflow-hidden rounded-xl border border-border bg-black/5">
                                    <img
                                        src={previewImage}
                                        alt="Preview"
                                        className="max-h-full object-contain"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setPreviewImage(null)}
                                        className="absolute top-2 right-2 rounded-full bg-black/60 p-1 text-white hover:bg-black"
                                    >
                                        <X className="h-4 w-4" />
                                    </button>
                                </div>
                            )}

                            {/* Product Attributes Grid */}
                            <div className="grid grid-cols-2 gap-3 rounded-xl border border-border bg-muted/40 p-4 sm:grid-cols-4">
                                <div>
                                    <span className="block text-[11px] text-muted-foreground">
                                        Category
                                    </span>
                                    <span className="text-xs font-semibold text-foreground">
                                        {getLocalizedName(
                                            selectedProduct.category,
                                        ) || 'N/A'}
                                    </span>
                                </div>
                                <div>
                                    <span className="block text-[11px] text-muted-foreground">
                                        Gender
                                    </span>
                                    <span className="text-xs font-semibold text-foreground">
                                        {getLocalizedName(
                                            selectedProduct.gender,
                                        ) || 'Unisex / All'}
                                    </span>
                                </div>
                                <div>
                                    <span className="block text-[11px] text-muted-foreground">
                                        Quality
                                    </span>
                                    <span className="text-xs font-semibold text-foreground">
                                        {getLocalizedName(
                                            selectedProduct.quality,
                                        ) || 'Standard'}
                                    </span>
                                </div>
                                <div>
                                    <span className="block text-[11px] text-muted-foreground">
                                        Created At
                                    </span>
                                    <span className="text-xs font-semibold text-foreground">
                                        {new Date(
                                            selectedProduct.created_at,
                                        ).toLocaleDateString()}
                                    </span>
                                </div>
                            </div>

                            {/* Price Breakdown */}
                            <div className="space-y-2">
                                <h4 className="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                    <DollarSign className="h-3.5 w-3.5" />{' '}
                                    Pricing Details
                                </h4>
                                <div className="grid grid-cols-3 gap-3">
                                    <div className="rounded-lg border border-border bg-card p-3">
                                        <span className="block text-[11px] text-muted-foreground">
                                            Shown Price
                                        </span>
                                        <span className="text-base font-bold text-foreground">
                                            {Number(
                                                selectedProduct.price_shown,
                                            ).toLocaleString()}{' '}
                                            DA
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-card p-3">
                                        <span className="block text-[11px] text-muted-foreground">
                                            Original Price
                                        </span>
                                        <span className="text-base font-semibold text-muted-foreground">
                                            {Number(
                                                selectedProduct.price_original,
                                            ).toLocaleString()}{' '}
                                            DA
                                        </span>
                                    </div>
                                    <div className="rounded-lg border border-border bg-card p-3">
                                        <span className="block text-[11px] text-muted-foreground">
                                            Store Payout
                                        </span>
                                        <span className="text-base font-semibold text-emerald-600 dark:text-emerald-400">
                                            {Number(
                                                selectedProduct.price_store,
                                            ).toLocaleString()}{' '}
                                            DA
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Variants & Stocks */}
                            {selectedProduct.variants &&
                                selectedProduct.variants.length > 0 && (
                                    <div className="space-y-2">
                                        <h4 className="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                            <Layers className="h-3.5 w-3.5" />{' '}
                                            Sizes & Inventory
                                        </h4>
                                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                            {selectedProduct.variants.map(
                                                (variant) => (
                                                    <div
                                                        key={variant.id}
                                                        className="flex items-center justify-between rounded-lg border border-border bg-card p-2.5 text-xs"
                                                    >
                                                        <span className="font-medium">
                                                            Size{' '}
                                                            {variant.size
                                                                ?.code ||
                                                                variant.size
                                                                    ?.en ||
                                                                'N/A'}
                                                        </span>
                                                        <Badge
                                                            variant="secondary"
                                                            className="font-mono text-[11px]"
                                                        >
                                                            {variant.stock}{' '}
                                                            units
                                                        </Badge>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}

                            {/* Description */}
                            {selectedProduct.description && (
                                <div className="space-y-1.5">
                                    <h4 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                        Description
                                    </h4>
                                    <div className="rounded-lg border border-border bg-muted/20 p-3.5 text-xs leading-relaxed whitespace-pre-wrap text-foreground">
                                        {selectedProduct.description}
                                    </div>
                                </div>
                            )}

                            {/* Reason display if rejected or archived */}
                            {(selectedProduct.product_status === 'rejected' ||
                                selectedProduct.product_status ===
                                    'archived') &&
                                selectedProduct.rejection_reason && (
                                    <div
                                        className={`space-y-1 rounded-lg border p-3.5 ${
                                            selectedProduct.product_status ===
                                            'rejected'
                                                ? 'border-rose-200 bg-rose-500/10 dark:border-rose-900/50'
                                                : 'border-slate-200 bg-slate-500/10 dark:border-slate-900/50'
                                        }`}
                                    >
                                        <h5
                                            className={`flex items-center gap-1.5 text-xs font-bold capitalize ${
                                                selectedProduct.product_status ===
                                                'rejected'
                                                    ? 'text-rose-700 dark:text-rose-300'
                                                    : 'text-slate-700 dark:text-slate-300'
                                            }`}
                                        >
                                            <AlertCircle className="h-4 w-4" />{' '}
                                            Current{' '}
                                            {selectedProduct.product_status}{' '}
                                            Note
                                        </h5>
                                        <p
                                            className={`text-xs ${
                                                selectedProduct.product_status ===
                                                'rejected'
                                                    ? 'text-rose-600 dark:text-rose-400'
                                                    : 'text-slate-600 dark:text-slate-400'
                                            }`}
                                        >
                                            {formatReason(
                                                selectedProduct.rejection_reason,
                                            )}
                                        </p>
                                    </div>
                                )}

                            {/* Inline Change Status Form (Select -> Textarea if needed -> Submit) */}
                            <form
                                onSubmit={handleInlineStatusSubmit}
                                className="space-y-3 rounded-xl border border-border bg-muted/40 p-4"
                            >
                                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                    <div>
                                        <label className="block text-xs font-semibold text-foreground">
                                            Change Product Status
                                        </label>
                                        <span className="text-[11px] text-muted-foreground">
                                            Select a target status, provide a
                                            note if required, and click apply.
                                        </span>
                                    </div>
                                    <Select
                                        value={targetStatus}
                                        onValueChange={(val) =>
                                            setTargetStatus(val)
                                        }
                                    >
                                        <SelectTrigger className="h-9 w-full text-xs sm:w-[200px]">
                                            <SelectValue placeholder="Select Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">
                                                Draft / Pending
                                            </SelectItem>
                                            <SelectItem value="published">
                                                Published
                                            </SelectItem>
                                            <SelectItem value="rejected">
                                                Rejected
                                            </SelectItem>
                                            <SelectItem value="archived">
                                                Archived
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Conditionally display reason textarea if status is rejected or archived */}
                                {(targetStatus === 'rejected' ||
                                    targetStatus === 'archived') && (
                                    <div className="space-y-1.5 border-t border-border/50 pt-2">
                                        <label className="flex items-center gap-1 text-xs font-semibold text-foreground">
                                            {targetStatus === 'rejected'
                                                ? 'Rejection Reason / Note'
                                                : 'Archiving Reason / Note'}
                                            <span className="text-rose-500">
                                                *
                                            </span>
                                        </label>
                                        <Textarea
                                            rows={3}
                                            required
                                            placeholder={
                                                targetStatus === 'rejected'
                                                    ? 'Provide explanation why this product was rejected...'
                                                    : 'Provide reason why this product was archived...'
                                            }
                                            value={inlineReason}
                                            onChange={(e) =>
                                                setInlineReason(e.target.value)
                                            }
                                            className="text-xs"
                                        />
                                    </div>
                                )}

                                {/* Apply button if status differs or reason is being provided */}
                                {(targetStatus !==
                                    selectedProduct.product_status ||
                                    ((targetStatus === 'rejected' ||
                                        targetStatus === 'archived') &&
                                        inlineReason.trim().length > 0)) && (
                                    <div className="flex justify-end pt-1">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            disabled={
                                                actionLoading ||
                                                ((targetStatus === 'rejected' ||
                                                    targetStatus ===
                                                        'archived') &&
                                                    !inlineReason.trim())
                                            }
                                            className={`gap-1.5 text-xs ${
                                                targetStatus === 'rejected'
                                                    ? 'bg-rose-600 text-white hover:bg-rose-700'
                                                    : targetStatus ===
                                                        'published'
                                                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                                      : ''
                                            }`}
                                        >
                                            <CheckCircle2 className="h-3.5 w-3.5" />{' '}
                                            Apply Status Change
                                        </Button>
                                    </div>
                                )}
                            </form>

                            {/* Modal Footer Actions */}
                            <DialogFooter className="flex flex-col items-center justify-between gap-2 border-t border-border pt-4 sm:flex-row">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handleSelectProduct(null)}
                                >
                                    Close
                                </Button>

                                <div className="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                                    {selectedProduct.product_status !==
                                        'archived' && (
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={() =>
                                                openArchiveModal(
                                                    selectedProduct,
                                                )
                                            }
                                            className="flex-1 gap-1.5 sm:flex-none"
                                        >
                                            <Archive className="h-4 w-4" />{' '}
                                            Archive
                                        </Button>
                                    )}

                                    {selectedProduct.product_status !==
                                        'rejected' && (
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() =>
                                                openRejectModal(selectedProduct)
                                            }
                                            className="flex-1 gap-1.5 sm:flex-none"
                                        >
                                            <XCircle className="h-4 w-4" />{' '}
                                            Reject Product
                                        </Button>
                                    )}

                                    {selectedProduct.product_status !==
                                        'published' && (
                                        <Button
                                            variant="default"
                                            size="sm"
                                            disabled={actionLoading}
                                            onClick={() =>
                                                handleApprove(selectedProduct)
                                            }
                                            className="flex-1 gap-1.5 bg-emerald-600 text-white hover:bg-emerald-700 sm:flex-none"
                                        >
                                            <CheckCircle2 className="h-4 w-4" />{' '}
                                            Approve & Publish
                                        </Button>
                                    )}
                                </div>
                            </DialogFooter>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* Rejection / Archive Reason Modal Dialog */}
            <Dialog open={reasonModalOpen} onOpenChange={setReasonModalOpen}>
                <DialogContent className="max-w-md">
                    <form onSubmit={handleReasonSubmit} className="space-y-4">
                        <DialogHeader>
                            <DialogTitle
                                className={`flex items-center gap-2 ${
                                    modalActionType === 'reject'
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : 'text-slate-700 dark:text-slate-300'
                                }`}
                            >
                                {modalActionType === 'reject' ? (
                                    <ShieldAlert className="h-5 w-5" />
                                ) : (
                                    <Archive className="h-5 w-5" />
                                )}
                                {modalActionType === 'reject'
                                    ? 'Reject Product'
                                    : 'Archive Product'}
                            </DialogTitle>
                            <DialogDescription>
                                Please provide a reason for{' '}
                                {modalActionType === 'reject'
                                    ? 'rejecting'
                                    : 'archiving'}{' '}
                                &quot;{selectedProduct?.name}&quot;. This
                                feedback will be saved with the product record.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-2">
                            <label className="text-xs font-semibold text-foreground">
                                Reason / Note{' '}
                                <span className="text-rose-500">*</span>
                            </label>
                            <Textarea
                                rows={4}
                                required
                                placeholder={
                                    modalActionType === 'reject'
                                        ? 'e.g. Inappropriate images, incorrect pricing, copyright violation, low quality...'
                                        : 'e.g. Product out of season, store discontinued item, catalog cleanup...'
                                }
                                value={reasonText}
                                onChange={(e) => setReasonText(e.target.value)}
                                className="text-xs"
                            />
                        </div>

                        <DialogFooter className="flex items-center justify-end gap-2 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => setReasonModalOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                variant={
                                    modalActionType === 'reject'
                                        ? 'destructive'
                                        : 'default'
                                }
                                size="sm"
                                disabled={actionLoading || !reasonText.trim()}
                                className="gap-1.5"
                            >
                                {modalActionType === 'reject' ? (
                                    <>
                                        <XCircle className="h-4 w-4" /> Confirm
                                        Rejection
                                    </>
                                ) : (
                                    <>
                                        <Archive className="h-4 w-4" /> Confirm
                                        Archive
                                    </>
                                )}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
