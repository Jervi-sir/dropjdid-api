import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    Archive,
    Award,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Eye,
    Image as ImageIcon,
    Layers,
    Package,
    Percent,
    RefreshCw,
    Save,
    Search,
    ShieldAlert,
    Sparkles,
    Tag,
    TrendingUp,
    UserCheck,
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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
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

interface ClassificationItem {
    id: number;
    code: string;
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
    classification_code?: string | null;
    name: string;
    description: string | null;
    price_store: string | number | null;
    price_selling: string | number | null;
    price_original: string | number | null;
    price_shown: string | number | null;
    discount_price: string | number | null;
    discount_percentage: string | number | null;
    creator_earning_type: 'fixed' | 'percentage' | string | null;
    creator_earning_value: string | number | null;
    platform_earning: string | number | null;
    event_name: string | null;
    event_price: string | number | null;
    event_start_at: string | null;
    event_end_at: string | null;
    effective_price?: string | number | null;
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
    classification?: ClassificationItem | null;
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
    classifications?: ClassificationItem[];
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

export default function ListProducts({
    products,
    classifications = [],
    filters,
    counts,
}: Props) {
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

    // Price & classification editing form state
    const [priceForm, setPriceForm] = useState({
        classification_code: '',
        price_store: '',
        price_selling: '',
        price_original: '',
        discount_price: '',
        discount_percentage: '',
        creator_earning_type: 'fixed',
        creator_earning_value: '',
        platform_earning: '',
        event_name: '',
        event_price: '',
        event_start_at: '',
        event_end_at: '',
    });
    const [priceSaving, setPriceSaving] = useState(false);
    const [priceSuccessMsg, setPriceSuccessMsg] = useState(false);

    // Sync target status and prices when opening a product
    const handleSelectProduct = (product: ProductItem | null) => {
        setSelectedProduct(product);
        if (product) {
            setTargetStatus(product.product_status);
            setInlineReason('');
            setPreviewImage(null);
            setPriceSuccessMsg(false);

            // Format date for datetime-local input (YYYY-MM-DDTHH:mm)
            const formatDateForInput = (dateStr: string | null | undefined) => {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return '';
                const pad = (n: number) => n.toString().padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            };

            setPriceForm({
                classification_code:
                    product.classification_code ||
                    (product.classification?.code ?? ''),
                price_store:
                    product.price_store !== null &&
                    product.price_store !== undefined
                        ? String(product.price_store)
                        : '',
                price_selling:
                    product.price_selling !== null &&
                    product.price_selling !== undefined
                        ? String(product.price_selling)
                        : product.price_shown !== null &&
                            product.price_shown !== undefined
                          ? String(product.price_shown)
                          : '',
                price_original:
                    product.price_original !== null &&
                    product.price_original !== undefined
                        ? String(product.price_original)
                        : '',
                discount_price:
                    product.discount_price !== null &&
                    product.discount_price !== undefined
                        ? String(product.discount_price)
                        : '',
                discount_percentage:
                    product.discount_percentage !== null &&
                    product.discount_percentage !== undefined
                        ? String(product.discount_percentage)
                        : '',
                creator_earning_type:
                    (product.creator_earning_type as string) || 'fixed',
                creator_earning_value:
                    product.creator_earning_value !== null &&
                    product.creator_earning_value !== undefined
                        ? String(product.creator_earning_value)
                        : '',
                platform_earning:
                    product.platform_earning !== null &&
                    product.platform_earning !== undefined
                        ? String(product.platform_earning)
                        : '',
                event_name: product.event_name || '',
                event_price:
                    product.event_price !== null &&
                    product.event_price !== undefined
                        ? String(product.event_price)
                        : '',
                event_start_at: formatDateForInput(product.event_start_at),
                event_end_at: formatDateForInput(product.event_end_at),
            });
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

    // Save customized pricing configuration
    const handlePriceSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedProduct) return;

        setPriceSaving(true);
        setPriceSuccessMsg(false);

        router.post(
            `/admin/sgm/products/${selectedProduct.id}/prices`,
            {
                classification_code:
                    priceForm.classification_code.trim() || null,
                price_store:
                    priceForm.price_store !== ''
                        ? parseFloat(priceForm.price_store)
                        : null,
                price_selling:
                    priceForm.price_selling !== ''
                        ? parseFloat(priceForm.price_selling)
                        : null,
                price_original:
                    priceForm.price_original !== ''
                        ? parseFloat(priceForm.price_original)
                        : null,
                discount_price:
                    priceForm.discount_price !== ''
                        ? parseFloat(priceForm.discount_price)
                        : null,
                discount_percentage:
                    priceForm.discount_percentage !== ''
                        ? parseFloat(priceForm.discount_percentage)
                        : null,
                creator_earning_type: priceForm.creator_earning_type,
                creator_earning_value:
                    priceForm.creator_earning_value !== ''
                        ? parseFloat(priceForm.creator_earning_value)
                        : null,
                platform_earning:
                    priceForm.platform_earning !== ''
                        ? parseFloat(priceForm.platform_earning)
                        : null,
                event_name: priceForm.event_name.trim() || null,
                event_price:
                    priceForm.event_price !== ''
                        ? parseFloat(priceForm.event_price)
                        : null,
                event_start_at: priceForm.event_start_at
                    ? new Date(priceForm.event_start_at).toISOString()
                    : null,
                event_end_at: priceForm.event_end_at
                    ? new Date(priceForm.event_end_at).toISOString()
                    : null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setPriceSuccessMsg(true);
                    setTimeout(() => setPriceSuccessMsg(false), 3000);
                },
                onFinish: () => {
                    setPriceSaving(false);
                },
            },
        );
    };

    const getClassificationBadge = (
        code?: string | null,
        classificationObj?: ClassificationItem | null,
    ) => {
        if (!code) return null;
        const matched =
            classificationObj ||
            classifications.find((item) => item.code === code);
        const label =
            matched?.en || matched?.fr || matched?.ar || code.replace(/_/g, ' ');

        switch (code) {
            case 'first_choice':
                return (
                    <Badge className="inline-flex items-center gap-1 border-blue-200 bg-blue-500/15 text-[10px] font-semibold text-blue-700 hover:bg-blue-500/25 dark:border-blue-800 dark:text-blue-300">
                        <Sparkles className="h-3 w-3 text-blue-600 dark:text-blue-400" />
                        {label}
                    </Badge>
                );
            case 'quantity_limited':
                return (
                    <Badge className="inline-flex items-center gap-1 border-rose-200 bg-rose-500/15 text-[10px] font-semibold text-rose-700 hover:bg-rose-500/25 dark:border-rose-800 dark:text-rose-300">
                        <Clock className="h-3 w-3 text-rose-600 dark:text-rose-400" />
                        {label}
                    </Badge>
                );
            case 'customer_favorite':
                return (
                    <Badge className="inline-flex items-center gap-1 border-purple-200 bg-purple-500/15 text-[10px] font-semibold text-purple-700 hover:bg-purple-500/25 dark:border-purple-800 dark:text-purple-300">
                        <Award className="h-3 w-3 text-purple-600 dark:text-purple-400" />
                        {label}
                    </Badge>
                );
            case 'selling_fast':
                return (
                    <Badge className="inline-flex items-center gap-1 border-amber-200 bg-amber-500/15 text-[10px] font-semibold text-amber-700 hover:bg-amber-500/25 dark:border-amber-800 dark:text-amber-300">
                        <TrendingUp className="h-3 w-3 text-amber-600 dark:text-amber-400" />
                        {label}
                    </Badge>
                );
            case 'best_value':
                return (
                    <Badge className="inline-flex items-center gap-1 border-emerald-200 bg-emerald-500/15 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-500/25 dark:border-emerald-800 dark:text-emerald-300">
                        <DollarSign className="h-3 w-3 text-emerald-600 dark:text-emerald-400" />
                        {label}
                    </Badge>
                );
            case 'new_arrival':
                return (
                    <Badge className="inline-flex items-center gap-1 border-cyan-200 bg-cyan-500/15 text-[10px] font-semibold text-cyan-700 hover:bg-cyan-500/25 dark:border-cyan-800 dark:text-cyan-300">
                        <Tag className="h-3 w-3 text-cyan-600 dark:text-cyan-400" />
                        {label}
                    </Badge>
                );
            case 'highly_rated':
                return (
                    <Badge className="inline-flex items-center gap-1 border-yellow-200 bg-yellow-500/15 text-[10px] font-semibold text-yellow-700 hover:bg-yellow-500/25 dark:border-yellow-800 dark:text-yellow-300">
                        <Sparkles className="h-3 w-3 text-yellow-500" />
                        {label}
                    </Badge>
                );
            default:
                return (
                    <Badge
                        variant="outline"
                        className="inline-flex items-center gap-1 text-[10px] font-semibold"
                    >
                        <Tag className="h-3 w-3" />
                        {label}
                    </Badge>
                );
        }
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

                {/* Products Table Card */}
                <Card className="overflow-hidden border border-border">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/40 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-5 py-3.5">Product</th>
                                    <th className="px-5 py-3.5">Store</th>
                                    <th className="px-5 py-3.5">Price</th>
                                    <th className="px-5 py-3.5">Stock & Sizes</th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5">Created At</th>
                                    <th className="px-5 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {products.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-6 py-14 text-center"
                                        >
                                            <div className="flex flex-col items-center justify-center text-center">
                                                <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground">
                                                    <Package className="h-7 w-7" />
                                                </div>
                                                <h3 className="text-base font-semibold text-foreground">
                                                    No products found
                                                </h3>
                                                <p className="mt-1 mb-4 max-w-sm text-sm text-muted-foreground">
                                                    {searchQuery ||
                                                    activeStatus !== 'all'
                                                        ? 'No products match your active search and filter criteria.'
                                                        : 'There are currently no store products to review.'}
                                                </p>
                                                {(searchQuery ||
                                                    activeStatus !==
                                                        'all') && (
                                                    <Button
                                                        onClick={
                                                            handleClearFilters
                                                        }
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        Clear all filters
                                                    </Button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    products.data.map((product) => {
                                        const mainImg =
                                            product.images?.find(
                                                (img) => img.is_main,
                                            )?.image_url ||
                                            product.images?.[0]?.image_url;
                                        const totalStock =
                                            product.variants?.reduce(
                                                (sum, v) =>
                                                    sum + (v.stock || 0),
                                                0,
                                            ) ?? 0;
                                        const categoryName = getLocalizedName(
                                            product.category,
                                        );

                                        return (
                                            <tr
                                                key={product.id}
                                                className="transition-colors hover:bg-muted/30"
                                            >
                                                {/* Product Info */}
                                                <td className="px-5 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="relative size-12 shrink-0 overflow-hidden rounded-lg border bg-muted">
                                                            {mainImg ? (
                                                                <img
                                                                    src={
                                                                        mainImg
                                                                    }
                                                                    alt={
                                                                        product.name
                                                                    }
                                                                    className="size-full object-cover"
                                                                />
                                                            ) : (
                                                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                                                    <ImageIcon className="size-5" />
                                                                </div>
                                                            )}
                                                            {product.images &&
                                                                product.images
                                                                    .length >
                                                                    1 && (
                                                                    <span className="absolute right-0.5 bottom-0.5 rounded bg-black/70 px-1 py-0.2 text-[9px] font-medium text-white">
                                                                        {
                                                                            product
                                                                                .images
                                                                                .length
                                                                        }
                                                                    </span>
                                                                )}
                                                        </div>
                                                        <div className="flex flex-col">
                                                            <div className="flex flex-wrap items-center gap-1.5">
                                                                <span
                                                                    className="line-clamp-1 max-w-[240px] text-sm font-semibold text-foreground"
                                                                    title={
                                                                        product.name
                                                                    }
                                                                >
                                                                    {
                                                                        product.name
                                                                    }
                                                                </span>
                                                                {product.is_affiliate && (
                                                                    <Badge className="bg-violet-600 px-1.5 py-0 text-[9px] text-white">
                                                                        Affiliate
                                                                    </Badge>
                                                                )}
                                                                {getClassificationBadge(
                                                                    product.classification_code,
                                                                    product.classification,
                                                                )}
                                                            </div>
                                                            <div className="mt-0.5 flex items-center gap-2">
                                                                {categoryName && (
                                                                    <span className="flex items-center gap-1 text-xs text-muted-foreground">
                                                                        <Tag className="h-3 w-3" />
                                                                        {
                                                                            categoryName
                                                                        }
                                                                    </span>
                                                                )}
                                                                <span className="font-mono text-[11px] text-muted-foreground/70">
                                                                    #{product.id}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Store Info */}
                                                <td className="px-5 py-4">
                                                    {product.store ? (
                                                        <div className="flex items-center gap-2">
                                                            <Avatar className="size-7 border">
                                                                <AvatarImage
                                                                    src={
                                                                        product
                                                                            .store
                                                                            .image_url ||
                                                                        undefined
                                                                    }
                                                                />
                                                                <AvatarFallback className="text-[10px] font-bold">
                                                                    {product.store.name
                                                                        ?.substring(
                                                                            0,
                                                                            2,
                                                                        )
                                                                        .toUpperCase() ||
                                                                        'ST'}
                                                                </AvatarFallback>
                                                            </Avatar>
                                                            <div className="flex flex-col">
                                                                <span className="max-w-[140px] truncate text-xs leading-tight font-semibold text-foreground">
                                                                    {product
                                                                        .store
                                                                        .name ||
                                                                        'Store'}
                                                                </span>
                                                                {product.store
                                                                    .phone_number && (
                                                                    <span className="font-mono text-[11px] text-muted-foreground">
                                                                        {
                                                                            product
                                                                                .store
                                                                                .phone_number
                                                                        }
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">
                                                            —
                                                        </span>
                                                    )}
                                                </td>                                                {/* Price */}
                                                <td className="px-5 py-4">
                                                    <div className="flex flex-col">
                                                        <div className="flex items-baseline gap-1.5">
                                                            <span className="text-sm font-bold text-foreground">
                                                                {Number(
                                                                    product.price_selling ||
                                                                        product.price_shown ||
                                                                        product.price_store ||
                                                                        0,
                                                                ).toLocaleString()}{' '}
                                                                DA
                                                            </span>
                                                            {Number(
                                                                product.price_original,
                                                            ) >
                                                                Number(
                                                                    product.price_selling ||
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
                                                        <div className="mt-0.5 flex flex-wrap items-center gap-1.5 text-[11px] text-muted-foreground">
                                                            <span>
                                                                Store:{' '}
                                                                <span className="font-semibold text-foreground/80">
                                                                    {Number(
                                                                        product.price_store ||
                                                                            0,
                                                                    ).toLocaleString()}{' '}
                                                                    DA
                                                                </span>
                                                            </span>
                                                            {product.discount_percentage && (
                                                                <span className="rounded bg-rose-500/10 px-1 py-0.2 font-bold text-rose-600 dark:text-rose-400">
                                                                    -
                                                                    {
                                                                        product.discount_percentage
                                                                    }
                                                                    %
                                                                </span>
                                                            )}
                                                            {product.event_price && (
                                                                <span className="rounded bg-amber-500/15 px-1 py-0.2 font-semibold text-amber-700 dark:text-amber-400">
                                                                    ⚡ Event
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Stock & Variants */}
                                                <td className="px-5 py-4">
                                                    <div className="flex flex-col gap-1">
                                                        <span className="text-xs font-semibold text-foreground">
                                                            {product.variants &&
                                                            product.variants
                                                                .length > 0
                                                                ? `${totalStock} in stock`
                                                                : 'No stock'}
                                                        </span>
                                                        {product.variants &&
                                                            product.variants
                                                                .length > 0 && (
                                                                <div className="flex flex-wrap gap-1">
                                                                    {product.variants
                                                                        .slice(
                                                                            0,
                                                                            3,
                                                                        )
                                                                        .map(
                                                                            (
                                                                                variant,
                                                                            ) => (
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
                                                                                    :
                                                                                    {
                                                                                        variant.stock
                                                                                    }
                                                                                </span>
                                                                            ),
                                                                        )}
                                                                    {product
                                                                        .variants
                                                                        .length >
                                                                        3 && (
                                                                        <span className="rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                                                            +
                                                                            {product
                                                                                .variants
                                                                                .length -
                                                                                3}
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            )}
                                                    </div>
                                                </td>

                                                {/* Status */}
                                                <td className="px-5 py-4">
                                                    <div className="flex flex-col items-start gap-1">
                                                        {getStatusBadge(
                                                            product.product_status,
                                                        )}
                                                        {(product.product_status ===
                                                            'rejected' ||
                                                            product.product_status ===
                                                                'archived') &&
                                                            product.rejection_reason && (
                                                                <span
                                                                    className={`line-clamp-1 max-w-[160px] text-[11px] ${
                                                                        product.product_status ===
                                                                        'rejected'
                                                                            ? 'text-rose-500'
                                                                            : 'text-slate-500'
                                                                    }`}
                                                                    title={formatReason(
                                                                        product.rejection_reason,
                                                                    )}
                                                                >
                                                                    {formatReason(
                                                                        product.rejection_reason,
                                                                    )}
                                                                </span>
                                                            )}
                                                        {product.expires_at && (
                                                            <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                                                                <Clock className="h-3 w-3" />
                                                                {new Date(
                                                                    product.expires_at,
                                                                ).toLocaleDateString()}
                                                            </span>
                                                        )}
                                                    </div>
                                                </td>

                                                {/* Created At */}
                                                <td className="px-5 py-4 text-xs whitespace-nowrap text-muted-foreground">
                                                    {new Date(
                                                        product.created_at,
                                                    ).toLocaleDateString()}
                                                </td>

                                                {/* Actions */}
                                                <td className="px-5 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                handleSelectProduct(
                                                                    product,
                                                                )
                                                            }
                                                            className="h-8 gap-1 text-xs font-semibold"
                                                        >
                                                            <Eye className="h-3.5 w-3.5" />
                                                            Review
                                                        </Button>

                                                        {product.product_status !==
                                                            'published' && (
                                                            <Button
                                                                variant="default"
                                                                size="sm"
                                                                disabled={
                                                                    actionLoading
                                                                }
                                                                onClick={() =>
                                                                    handleApprove(
                                                                        product,
                                                                    )
                                                                }
                                                                className="h-8 bg-emerald-600 px-2.5 text-xs text-white hover:bg-emerald-700"
                                                                title="Quick Approve"
                                                            >
                                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                            </Button>
                                                        )}

                                                        {product.product_status !==
                                                            'rejected' && (
                                                            <Button
                                                                variant="destructive"
                                                                size="sm"
                                                                disabled={
                                                                    actionLoading
                                                                }
                                                                onClick={() =>
                                                                    openRejectModal(
                                                                        product,
                                                                    )
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
                                                                disabled={
                                                                    actionLoading
                                                                }
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
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </Card>

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

            {/* Product Detail & Review Right Side Sheet */}
            <Sheet
                open={!!selectedProduct && !reasonModalOpen}
                onOpenChange={(open) => !open && handleSelectProduct(null)}
            >
                <SheetContent
                    side="right"
                    className="flex w-full flex-col gap-0 overflow-hidden p-0 sm:max-w-2xl md:max-w-3xl"
                >
                    {selectedProduct && (
                        <div className="flex h-full flex-col">
                            {/* Sheet Header */}
                            <SheetHeader className="border-b border-border bg-card p-6 pb-4">
                                <div className="flex items-start justify-between gap-4">
                                    <div className="space-y-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="rounded-md bg-muted px-2.5 py-0.5 font-mono text-xs font-bold text-foreground">
                                                #{selectedProduct.id}
                                            </span>
                                            {getStatusBadge(
                                                selectedProduct.product_status,
                                            )}
                                            {selectedProduct.is_affiliate && (
                                                <Badge className="bg-violet-600 text-xs font-semibold text-white">
                                                    Affiliate Ready
                                                </Badge>
                                            )}
                                        </div>
                                        <SheetTitle className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                                            {selectedProduct.name}
                                        </SheetTitle>
                                        <SheetDescription className="flex items-center gap-2 text-xs text-muted-foreground">
                                            <span>
                                                Store:{' '}
                                                <strong className="text-foreground">
                                                    {selectedProduct.store
                                                        ?.name || 'N/A'}
                                                </strong>
                                            </span>
                                            {selectedProduct.store
                                                ?.phone_number && (
                                                <>
                                                    <span>•</span>
                                                    <span className="font-mono">
                                                        {
                                                            selectedProduct
                                                                .store
                                                                .phone_number
                                                        }
                                                    </span>
                                                </>
                                            )}
                                        </SheetDescription>
                                    </div>
                                </div>
                            </SheetHeader>

                            {/* Scrollable Sheet Body */}
                            <div className="flex-1 space-y-6 overflow-y-auto p-6">
                                {/* Images Gallery */}
                                {selectedProduct.images &&
                                    selectedProduct.images.length > 0 && (
                                        <div className="space-y-2.5">
                                            <h4 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Product Images (
                                                {selectedProduct.images.length})
                                            </h4>
                                            <div className="grid grid-cols-4 gap-2.5 sm:grid-cols-6">
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
                                                            className={`group relative aspect-square overflow-hidden rounded-xl border-2 transition-all ${
                                                                img.is_main
                                                                    ? 'border-primary ring-2 ring-primary/20'
                                                                    : 'border-border hover:border-foreground/40'
                                                            }`}
                                                        >
                                                            <img
                                                                src={
                                                                    img.image_url
                                                                }
                                                                alt=""
                                                                className="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                                            />
                                                            {img.is_main && (
                                                                <span className="absolute right-1 bottom-1 left-1 rounded bg-primary py-0.5 text-center text-[9px] font-bold text-primary-foreground shadow-sm">
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
                                    <div className="relative flex aspect-video max-h-72 w-full items-center justify-center overflow-hidden rounded-2xl border border-border bg-black/5 shadow-inner">
                                        <img
                                            src={previewImage}
                                            alt="Preview"
                                            className="max-h-full object-contain"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setPreviewImage(null)
                                            }
                                            className="absolute top-3 right-3 rounded-full bg-black/70 p-1.5 text-white transition-colors hover:bg-black"
                                        >
                                            <X className="h-4 w-4" />
                                        </button>
                                    </div>
                                )}

                                {/* Product Attributes Grid */}
                                <div className="grid grid-cols-2 gap-3 rounded-2xl border border-border bg-muted/40 p-4 sm:grid-cols-5">
                                    <div className="space-y-0.5">
                                        <span className="block text-[11px] font-medium text-muted-foreground uppercase">
                                            Category
                                        </span>
                                        <span className="text-xs font-semibold text-foreground">
                                            {getLocalizedName(
                                                selectedProduct.category,
                                            ) || 'N/A'}
                                        </span>
                                    </div>
                                    <div className="space-y-0.5">
                                        <span className="block text-[11px] font-medium text-muted-foreground uppercase">
                                            Gender
                                        </span>
                                        <span className="text-xs font-semibold text-foreground">
                                            {getLocalizedName(
                                                selectedProduct.gender,
                                            ) || 'Unisex / All'}
                                        </span>
                                    </div>
                                    <div className="space-y-0.5">
                                        <span className="block text-[11px] font-medium text-muted-foreground uppercase">
                                            Quality
                                        </span>
                                        <span className="text-xs font-semibold text-foreground">
                                            {getLocalizedName(
                                                selectedProduct.quality,
                                            ) || 'Standard'}
                                        </span>
                                    </div>
                                    <div className="space-y-0.5">
                                        <span className="block text-[11px] font-medium text-muted-foreground uppercase">
                                            Classification
                                        </span>
                                        <div>
                                            {getClassificationBadge(
                                                selectedProduct.classification_code,
                                                selectedProduct.classification,
                                            ) || (
                                                <span className="text-xs text-muted-foreground">
                                                    None
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="space-y-0.5">
                                        <span className="block text-[11px] font-medium text-muted-foreground uppercase">
                                            Created At
                                        </span>
                                        <span className="text-xs font-semibold text-foreground">
                                            {new Date(
                                                selectedProduct.created_at,
                                            ).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>

                                {/* EDITABLE PRICING FORM */}
                                <form
                                    onSubmit={handlePriceSubmit}
                                    className="space-y-4 rounded-2xl border border-border/90 bg-card p-5 shadow-xs"
                                >
                                    <div className="flex flex-col justify-between gap-2 border-b border-border/60 pb-3 sm:flex-row sm:items-center">
                                        <div>
                                            <h4 className="flex items-center gap-2 text-sm font-bold tracking-tight text-foreground uppercase">
                                                <DollarSign className="h-4 w-4 text-emerald-600" />{' '}
                                                Product Pricing & Classification Controls
                                            </h4>
                                            <p className="text-xs text-muted-foreground">
                                                Edit classification badges, selling prices, margins,
                                                commissions, discounts, and events.
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {priceSuccessMsg && (
                                                <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                    <CheckCircle2 className="h-3.5 w-3.5" />{' '}
                                                    Saved!
                                                </span>
                                            )}
                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={priceSaving}
                                                className="h-8 gap-1.5 bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700"
                                            >
                                                <Save className="h-3.5 w-3.5" />
                                                {priceSaving
                                                    ? 'Saving...'
                                                    : 'Save Pricing & Badge'}
                                            </Button>
                                        </div>
                                    </div>

                                    {/* Product Classification Selector */}
                                    <div className="space-y-2 rounded-xl border border-border/80 bg-muted/20 p-3.5">
                                        <div className="flex items-center justify-between">
                                            <Label className="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                                <Sparkles className="h-3.5 w-3.5 text-amber-500" />
                                                Product Classification Badge
                                            </Label>
                                            {priceForm.classification_code && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            classification_code:
                                                                '',
                                                        })
                                                    }
                                                    className="text-[11px] font-medium text-muted-foreground underline hover:text-foreground"
                                                >
                                                    Clear / None
                                                </button>
                                            )}
                                        </div>
                                        <div className="flex flex-wrap gap-1.5">
                                            {classifications.map((item) => {
                                                const isSelected =
                                                    priceForm.classification_code ===
                                                    item.code;
                                                return (
                                                    <button
                                                        key={item.code}
                                                        type="button"
                                                        onClick={() =>
                                                            setPriceForm({
                                                                ...priceForm,
                                                                classification_code:
                                                                    isSelected
                                                                        ? ''
                                                                        : item.code,
                                                            })
                                                        }
                                                        className={`inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition-all ${
                                                            isSelected
                                                                ? 'border-primary bg-primary text-primary-foreground shadow-xs'
                                                                : 'border-border bg-background text-muted-foreground hover:border-border/80 hover:bg-muted/40 hover:text-foreground'
                                                        }`}
                                                    >
                                                        <Sparkles
                                                            className={`h-3 w-3 ${isSelected ? 'text-primary-foreground' : 'text-amber-500'}`}
                                                        />
                                                        {item.en ||
                                                            item.fr ||
                                                            item.ar ||
                                                            item.code}
                                                    </button>
                                                );
                                            })}
                                            {classifications.length === 0 && (
                                                <span className="text-xs text-muted-foreground italic">
                                                    No classifications found. Run `php artisan db:seed --class=ProductClassificationSeeder`
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Core Prices Grid */}
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        {/* 1. Price Store */}
                                        <div className="space-y-1.5 rounded-xl border border-border bg-muted/20 p-3">
                                            <Label className="text-xs font-semibold text-muted-foreground">
                                                Store Wholesale Cost (DA)
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                value={priceForm.price_store}
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        price_store:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-9 font-semibold text-foreground"
                                            />
                                            <span className="block text-[10px] text-muted-foreground">
                                                Amount paid to store owner
                                            </span>
                                        </div>

                                        {/* 2. Price Selling */}
                                        <div className="space-y-1.5 rounded-xl border border-primary/30 bg-primary/5 p-3">
                                            <Label className="text-xs font-semibold text-primary">
                                                Selling Price (DA) *
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                value={priceForm.price_selling}
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        price_selling:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-9 font-bold text-primary"
                                            />
                                            <span className="block text-[10px] text-muted-foreground">
                                                Standard customer price
                                            </span>
                                        </div>

                                        {/* 3. Original / MSRP */}
                                        <div className="space-y-1.5 rounded-xl border border-border bg-muted/20 p-3">
                                            <Label className="text-xs font-semibold text-muted-foreground">
                                                Original MSRP (DA)
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                value={priceForm.price_original}
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        price_original:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-9 text-muted-foreground"
                                            />
                                            <span className="block text-[10px] text-muted-foreground">
                                                Crossed-out reference
                                            </span>
                                        </div>
                                    </div>

                                    {/* Discounts & Commissions */}
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        {/* Discount Price */}
                                        <div className="space-y-1.5 rounded-xl border border-rose-200/60 bg-rose-500/5 p-3 dark:border-rose-900/40">
                                            <Label className="text-xs font-semibold text-rose-600 dark:text-rose-400">
                                                Discount Price (DA)
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="e.g. 2900"
                                                value={priceForm.discount_price}
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        discount_price:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-8 text-xs font-medium text-rose-600 dark:text-rose-400"
                                            />
                                        </div>

                                        {/* Discount Percentage */}
                                        <div className="space-y-1.5 rounded-xl border border-rose-200/60 bg-rose-500/5 p-3 dark:border-rose-900/40">
                                            <Label className="text-xs font-semibold text-rose-600 dark:text-rose-400">
                                                Discount %
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.1"
                                                min="0"
                                                max="100"
                                                placeholder="e.g. 15"
                                                value={
                                                    priceForm.discount_percentage
                                                }
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        discount_percentage:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-8 text-xs font-medium text-rose-600 dark:text-rose-400"
                                            />
                                        </div>

                                        {/* Creator Commission */}
                                        <div className="space-y-1.5 rounded-xl border border-violet-200/60 bg-violet-500/5 p-3 dark:border-violet-900/40">
                                            <div className="flex items-center justify-between">
                                                <Label className="text-xs font-semibold text-violet-700 dark:text-violet-300">
                                                    Creator Share
                                                </Label>
                                                <select
                                                    value={
                                                        priceForm.creator_earning_type
                                                    }
                                                    onChange={(e) =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            creator_earning_type:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="rounded border border-violet-200 bg-transparent px-1 py-0.5 text-[10px] font-bold text-violet-700 dark:border-violet-800 dark:text-violet-300"
                                                >
                                                    <option value="fixed">
                                                        DA
                                                    </option>
                                                    <option value="percentage">
                                                        %
                                                    </option>
                                                </select>
                                            </div>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="Value"
                                                value={
                                                    priceForm.creator_earning_value
                                                }
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        creator_earning_value:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-8 text-xs font-medium text-violet-700 dark:text-violet-300"
                                            />
                                        </div>

                                        {/* Platform Take */}
                                        <div className="space-y-1.5 rounded-xl border border-emerald-200/60 bg-emerald-500/5 p-3 dark:border-emerald-900/40">
                                            <Label className="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                                Platform Margin (DA)
                                            </Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="Custom DA"
                                                value={
                                                    priceForm.platform_earning
                                                }
                                                onChange={(e) =>
                                                    setPriceForm({
                                                        ...priceForm,
                                                        platform_earning:
                                                            e.target.value,
                                                    })
                                                }
                                                className="h-8 text-xs font-medium text-emerald-700 dark:text-emerald-300"
                                            />
                                        </div>
                                    </div>

                                    {/* Event-Related Pricing Section */}
                                    <div className="space-y-3 rounded-xl border border-amber-300/80 bg-amber-500/10 p-3.5 dark:border-amber-800/80">
                                        <div className="flex items-center gap-1.5 text-xs font-bold text-amber-800 dark:text-amber-300">
                                            <Calendar className="h-4 w-4" />
                                            <span>
                                                Flash Campaign / Event Pricing
                                            </span>
                                        </div>
                                        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                                            <div className="space-y-1">
                                                <Label className="text-[11px] font-medium text-muted-foreground">
                                                    Event Name
                                                </Label>
                                                <Input
                                                    placeholder="e.g. Ramadan Special"
                                                    value={priceForm.event_name}
                                                    onChange={(e) =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            event_name:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="h-8 text-xs"
                                                />
                                            </div>
                                            <div className="space-y-1">
                                                <Label className="text-[11px] font-medium text-muted-foreground">
                                                    Event Price (DA)
                                                </Label>
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    value={
                                                        priceForm.event_price
                                                    }
                                                    onChange={(e) =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            event_price:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="h-8 text-xs font-bold text-amber-700 dark:text-amber-300"
                                                />
                                            </div>
                                            <div className="space-y-1">
                                                <Label className="text-[11px] font-medium text-muted-foreground">
                                                    Start Date (Min)
                                                </Label>
                                                <Input
                                                    type="datetime-local"
                                                    value={
                                                        priceForm.event_start_at
                                                    }
                                                    onChange={(e) =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            event_start_at:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="h-8 text-xs"
                                                />
                                            </div>
                                            <div className="space-y-1">
                                                <Label className="text-[11px] font-medium text-muted-foreground">
                                                    End Date (Max)
                                                </Label>
                                                <Input
                                                    type="datetime-local"
                                                    value={
                                                        priceForm.event_end_at
                                                    }
                                                    onChange={(e) =>
                                                        setPriceForm({
                                                            ...priceForm,
                                                            event_end_at:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="h-8 text-xs"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </form>

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
                                {(selectedProduct.product_status ===
                                    'rejected' ||
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
                                                {
                                                    selectedProduct.product_status
                                                }{' '}
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

                                {/* Inline Change Status Form */}
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
                                                Select a target status, provide
                                                a note if required, and click
                                                apply.
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
                                                    setInlineReason(
                                                        e.target.value,
                                                    )
                                                }
                                                className="text-xs"
                                            />
                                        </div>
                                    )}

                                    {(targetStatus !==
                                        selectedProduct.product_status ||
                                        ((targetStatus === 'rejected' ||
                                            targetStatus === 'archived') &&
                                            inlineReason.trim().length >
                                                0)) && (
                                        <div className="flex justify-end pt-1">
                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={
                                                    actionLoading ||
                                                    ((targetStatus ===
                                                        'rejected' ||
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
                            </div>

                            {/* Sheet Footer Actions */}
                            <SheetFooter className="border-t border-border bg-card p-4">
                                <div className="flex w-full flex-wrap items-center justify-between gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            handleSelectProduct(null)
                                        }
                                    >
                                        Close
                                    </Button>

                                    <div className="flex flex-wrap items-center gap-2">
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
                                                className="gap-1.5"
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
                                                    openRejectModal(
                                                        selectedProduct,
                                                    )
                                                }
                                                className="gap-1.5"
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
                                                    handleApprove(
                                                        selectedProduct,
                                                    )
                                                }
                                                className="gap-1.5 bg-emerald-600 text-white hover:bg-emerald-700"
                                            >
                                                <CheckCircle2 className="h-4 w-4" />{' '}
                                                Approve & Publish
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </SheetFooter>
                        </div>
                    )}
                </SheetContent>
            </Sheet>

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
