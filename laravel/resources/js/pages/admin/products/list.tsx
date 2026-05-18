import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowProductController from '@/actions/App/Http/Controllers/Admin/Products/ShowProductController';
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
    IconUser,
    IconHeart,
    IconBookmark,
    IconRefresh,
    IconPlus,
    IconFolder,
    IconChevronLeft,
    IconChevronRight,
    IconCheck,
    IconAlertTriangle,
    IconLoader2,
    IconShoppingBag,
    IconTag,
    IconCircleCheck,
} from '@tabler/icons-react';
import { History } from 'lucide-react';

interface Product {
    id: number;
    name: string;
    description: string;
    original_price: number;
    show_price: number;
    store_price: number;
    status: string;
    store: {
        id: number;
        name: string;
        username: string;
    } | null;
    category: {
        id: number;
        en: string;
    } | null;
    quality: {
        id: number;
        en: string;
    } | null;
    image: string | null;
    liked_count: number;
    saved_count: number;
    order_items_count: number;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProducts {
    data: Product[];
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

interface ProductListProps {
    products: PaginatedProducts;
    filters: FilterProps;
    statuses: { [key: number]: string };
    stats: {
        total: number;
        draft: number;
        published: number;
        archived: number;
        rejected: number;
    };
}

export default function ProductList({
    products,
    filters,
    statuses,
    stats,
}: ProductListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    // Sheet Preview States
    const [selectedProductId, setSelectedProductId] = React.useState<
        number | null
    >(null);
    const [sheetOpen, setSheetOpen] = React.useState(false);
    const [loadingProduct, setLoadingProduct] = React.useState(false);
    const [previewProduct, setPreviewProduct] = React.useState<any>(null);

    // Sheet Status Management Form States
    const [formStatus, setFormStatus] = React.useState<string>('');
    const [reasonEn, setReasonEn] = React.useState<string>('');
    const [reasonFr, setReasonFr] = React.useState<string>('');
    const [reasonAr, setReasonAr] = React.useState<string>('');
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    const [submitSuccess, setSubmitSuccess] = React.useState(false);

    React.useEffect(() => {
        if (selectedProductId && sheetOpen) {
            setLoadingProduct(true);
            setPreviewProduct(null);
            setSubmitSuccess(false);

            fetch(ShowProductController.show.url(selectedProductId), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    setPreviewProduct(data.product);
                    setFormStatus(data.product.status);
                    setReasonEn('');
                    setReasonFr('');
                    setReasonAr('');
                })
                .catch((err) => console.error(err))
                .finally(() => setLoadingProduct(false));
        }
    }, [selectedProductId, sheetOpen]);

    const handleSheetSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setSubmitSuccess(false);

        router.put(
            ShowProductController.update.url(selectedProductId!),
            {
                status: formStatus,
                rejection_reason_en: reasonEn,
                rejection_reason_fr: reasonFr,
                rejection_reason_ar: reasonAr,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setSubmitSuccess(true);
                    // Refetch to show updated details
                    fetch(ShowProductController.show.url(selectedProductId!), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((res) => res.json())
                        .then((data) => {
                            setPreviewProduct(data.product);
                        });

                    if (formStatus !== 'rejected') {
                        setReasonEn('');
                        setReasonFr('');
                        setReasonAr('');
                    }
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const getStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'published':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Published
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge className="border border-slate-500/20 bg-slate-100 text-slate-700 hover:bg-slate-200/80 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Draft
                    </Badge>
                );
            case 'archived':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Archived
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Rejected
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const applyFilters = (
        search = searchTerm,
        status = statusVal,
        limit = perPage,
    ) => {
        router.get(
            '/admin/products',
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

    const handleStatusChange = (value: string) => {
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
        router.get(
            '/admin/products',
            {},
            { preserveState: true, replace: true },
        );
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
            <Head title="Products Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Products
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review store products, manage quality labels, and
                            moderate publishing states.
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

                {/* Stats Grid */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Total Products
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.total}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                            Published
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.published}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            Drafts
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.draft}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-indigo-500 uppercase">
                            Archived
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.archived}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-rose-500 uppercase">
                            Rejected
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.rejected}
                        </span>
                    </div>
                </div>

                {/* Filters and Table Container */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    {/* Controls Bar */}
                    <div className="flex flex-col items-center justify-between gap-4 border-b bg-muted/20 p-4 sm:flex-row">
                        <div className="flex w-full flex-1 flex-col items-center gap-2 sm:flex-row">
                            <div className="relative w-full sm:max-w-xs">
                                <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search products..."
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
                                    onValueChange={handleStatusChange}
                                >
                                    <SelectTrigger className="h-10 border-input bg-background">
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        {Object.entries(statuses).map(
                                            ([key, value]) => (
                                                <SelectItem
                                                    key={key}
                                                    value={value}
                                                >
                                                    <span className="capitalize">
                                                        {value}
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

                    {/* Table Element */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="border-b bg-muted/15">
                                <TableRow>
                                    <TableHead className="w-[80px] py-4 pl-6">
                                        Image
                                    </TableHead>
                                    <TableHead className="min-w-[180px] py-4">
                                        Product Info
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Seller Store
                                    </TableHead>
                                    <TableHead className="py-4 text-right">
                                        Pricing Stack
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Category
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Quality
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Created
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Status
                                    </TableHead>
                                    <TableHead className="w-[160px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {products.data.length > 0 ? (
                                    products.data.map((prod) => (
                                        <TableRow
                                            key={prod.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="size-11 shrink-0 overflow-hidden rounded-lg border bg-muted/10">
                                                    <img
                                                        src={prod.image || ''}
                                                        alt={prod.name}
                                                        className="h-full w-full object-cover"
                                                        onError={(e) => {
                                                            (
                                                                e.target as HTMLImageElement
                                                            ).src =
                                                                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                                                        }}
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex max-w-[240px] flex-col">
                                                    <span className="truncate text-sm font-bold text-foreground">
                                                        {prod.name}
                                                    </span>
                                                    <span className="mt-0.5 line-clamp-1 text-xs text-muted-foreground">
                                                        {prod.description}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-xs font-semibold text-foreground">
                                                        {prod.store
                                                            ? prod.store.name
                                                            : 'Unknown Store'}
                                                    </span>
                                                    <span className="text-[10px] text-muted-foreground">
                                                        {prod.store?.username
                                                            ? `@${prod.store.username}`
                                                            : ''}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-right">
                                                <div className="flex flex-col items-end justify-end gap-0.5">
                                                    <div className="flex items-center gap-1">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Orig:
                                                        </span>
                                                        <span className="text-xs font-bold text-foreground">
                                                            $
                                                            {prod.original_price.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-1">
                                                        <span className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                            Show:
                                                        </span>
                                                        <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                            $
                                                            {prod.show_price.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {prod.category ? (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-muted bg-muted/20 px-2 py-0.5 text-[10px] capitalize"
                                                    >
                                                        {prod.category.en}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        None
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {prod.quality ? (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-indigo-500/10 bg-indigo-500/5 px-2 py-0.5 text-[10px] text-indigo-600 capitalize dark:text-indigo-400"
                                                    >
                                                        {prod.quality.en}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        None
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5 text-muted-foreground" />
                                                    <span>
                                                        {formatDate(
                                                            prod.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {getStatusBadge(prod.status)}
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5"
                                                        onClick={() => {
                                                            setSelectedProductId(
                                                                prod.id,
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
                                                            href={ShowProductController.show.url(
                                                                prod.id,
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
                                            colSpan={9}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconFolder className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No products found
                                                    </p>
                                                    <p className="text-xs">
                                                        Try adjusting your
                                                        filters or search query.
                                                    </p>
                                                </div>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    {/* Pagination Controls */}
                    {products.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {products.from} to {products.to} of{' '}
                                {products.total} products
                            </span>

                            <div className="flex items-center gap-1.5">
                                {products.links.map((link, idx) => {
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

            {/* Dynamic Product Management Sheet */}
            <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                <SheetContent
                    side="right"
                    className="flex w-full flex-col overflow-y-auto border-l bg-card p-0 shadow-xl sm:max-w-xl"
                >
                    <SheetHeader className="border-b border-muted/50 bg-muted/10 p-6">
                        <div className="flex items-center justify-between gap-3">
                            <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                                Manage Product Preview #{selectedProductId}
                            </SheetTitle>
                            {previewProduct &&
                                getStatusBadge(previewProduct.status)}
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Review details, images, seller info, and perform
                            quick status adjustments.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingProduct ? (
                        <div className="flex flex-grow flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Loading product information...
                            </span>
                        </div>
                    ) : previewProduct ? (
                        <div className="flex flex-col gap-4 p-4">
                            {/* Product Overview stats */}
                            <div className="flex flex-col gap-3 rounded-xl border bg-muted/10 p-4 text-sm">
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Product Overview
                                </div>
                                <div className="flex items-start gap-4">
                                    <div className="size-20 shrink-0 overflow-hidden rounded-lg border bg-muted/10 shadow-sm">
                                        <img
                                            src={
                                                previewProduct.images?.[0]
                                                    ?.image || ''
                                            }
                                            alt={previewProduct.name}
                                            className="h-full w-full object-cover"
                                            onError={(e) => {
                                                (
                                                    e.target as HTMLImageElement
                                                ).src =
                                                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                                            }}
                                        />
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <span className="text-sm font-bold text-foreground">
                                            {previewProduct.name}
                                        </span>
                                        <span className="line-clamp-2 text-xs text-muted-foreground">
                                            {previewProduct.description}
                                        </span>
                                    </div>
                                </div>

                                <hr className="my-1 border-muted/50" />

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Seller Store
                                        </span>
                                        <span className="text-xs font-bold text-foreground">
                                            {previewProduct.store
                                                ? previewProduct.store.name
                                                : 'Unknown Store'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Category
                                        </span>
                                        <span className="text-xs font-bold text-foreground">
                                            {previewProduct.category
                                                ? previewProduct.category.en
                                                : 'None'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Original Price
                                        </span>
                                        <span className="text-xs font-medium text-foreground">
                                            $
                                            {previewProduct.original_price?.toFixed(
                                                2,
                                            )}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Show Price
                                        </span>
                                        <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            $
                                            {previewProduct.show_price?.toFixed(
                                                2,
                                            )}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Status Update Form inside sheet */}
                            <form
                                onSubmit={handleSheetSubmit}
                                className="flex flex-col gap-4 rounded-xl border bg-card p-4"
                            >
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Status Updater
                                </div>
                                <div className="flex flex-col gap-1.5">
                                    <label className="text-xs font-semibold text-muted-foreground">
                                        Select New Status
                                    </label>
                                    <Select
                                        value={formStatus}
                                        onValueChange={setFormStatus}
                                    >
                                        <SelectTrigger className="h-10 w-full border-input bg-background">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(statuses).map(
                                                ([key, label]) => (
                                                    <SelectItem
                                                        key={key}
                                                        value={label}
                                                    >
                                                        <span className="capitalize">
                                                            {label}
                                                        </span>
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Localized Rejection Fields (Collapsible inside sheet) */}
                                <div
                                    className={`flex flex-col gap-3.5 overflow-hidden transition-all duration-300 ease-in-out ${
                                        formStatus === 'rejected'
                                            ? 'mt-2 max-h-[350px] opacity-100'
                                            : 'pointer-events-none max-h-0 opacity-0'
                                    }`}
                                >
                                    <div className="flex flex-col gap-3 border-t pt-3">
                                        <div className="flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                            <IconAlertTriangle className="size-4" />
                                            <span>
                                                Rejection Reasons Required
                                            </span>
                                        </div>

                                        <div className="flex flex-col gap-1">
                                            <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Reason (English)
                                            </label>
                                            <Input
                                                placeholder="Rejection reason in English"
                                                value={reasonEn}
                                                onChange={(e) =>
                                                    setReasonEn(e.target.value)
                                                }
                                                className="h-9 bg-background"
                                            />
                                        </div>
                                        <div className="flex flex-col gap-1">
                                            <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Reason (French)
                                            </label>
                                            <Input
                                                placeholder="Raison du rejet en français"
                                                value={reasonFr}
                                                onChange={(e) =>
                                                    setReasonFr(e.target.value)
                                                }
                                                className="h-9 bg-background"
                                            />
                                        </div>
                                        <div className="flex flex-col gap-1">
                                            <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Reason (Arabic)
                                            </label>
                                            <Input
                                                dir="rtl"
                                                placeholder="سبب الرفض باللغة العربية"
                                                value={reasonAr}
                                                onChange={(e) =>
                                                    setReasonAr(e.target.value)
                                                }
                                                className="h-9 bg-background text-right"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="mt-1 h-10 w-full"
                                >
                                    {isSubmitting
                                        ? 'Saving changes...'
                                        : 'Save Status'}
                                </Button>

                                {submitSuccess && (
                                    <div className="flex items-center justify-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <IconCheck className="size-4" />
                                        <span>Status updated successfully</span>
                                    </div>
                                )}
                            </form>

                            {/* Product Gallery Section */}
                            {previewProduct.images &&
                                previewProduct.images.length > 1 && (
                                    <div className="flex flex-col gap-2">
                                        <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                            Product Gallery (
                                            {previewProduct.images.length}{' '}
                                            images)
                                        </div>
                                        <div className="grid grid-cols-4 gap-2">
                                            {previewProduct.images.map(
                                                (img: any) => (
                                                    <div
                                                        key={img.id}
                                                        className="aspect-square overflow-hidden rounded-lg border bg-muted/10 transition-colors hover:border-primary"
                                                    >
                                                        <img
                                                            src={img.image}
                                                            alt="Product detail"
                                                            className="h-full w-full object-cover"
                                                        />
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}

                            {/* Historical Audit Timeline (Rejections history) */}
                            {previewProduct.rejection_reasons &&
                                previewProduct.rejection_reasons.length > 0 && (
                                    <div className="flex flex-col gap-2.5">
                                        <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                            Historical Audit Timeline
                                        </div>
                                        <div className="flex max-h-[180px] flex-col gap-3 overflow-y-auto rounded-lg border bg-muted/5 p-3 pr-1">
                                            {previewProduct.rejection_reasons.map(
                                                (audit: any, index: number) => (
                                                    <div
                                                        key={audit.id || index}
                                                        className="flex items-start gap-2 border-b pb-2 text-xs last:border-0 last:pb-0"
                                                    >
                                                        <div className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-rose-500/10 text-rose-500">
                                                            <History className="size-3.5" />
                                                        </div>
                                                        <div className="flex flex-1 flex-col gap-1">
                                                            <span className="font-bold text-foreground">
                                                                Audit #
                                                                {audit.id ||
                                                                    index + 1}
                                                            </span>
                                                            <div className="flex flex-col gap-0.5 text-muted-foreground">
                                                                {audit.en && (
                                                                    <div>
                                                                        <strong className="text-[10px] text-foreground uppercase">
                                                                            EN:
                                                                        </strong>{' '}
                                                                        {
                                                                            audit.en
                                                                        }
                                                                    </div>
                                                                )}
                                                                {audit.fr && (
                                                                    <div>
                                                                        <strong className="text-[10px] text-foreground uppercase">
                                                                            FR:
                                                                        </strong>{' '}
                                                                        {
                                                                            audit.fr
                                                                        }
                                                                    </div>
                                                                )}
                                                                {audit.ar && (
                                                                    <div
                                                                        className="text-right"
                                                                        dir="rtl"
                                                                    >
                                                                        <strong className="text-[10px] text-foreground uppercase">
                                                                            AR:
                                                                        </strong>{' '}
                                                                        {
                                                                            audit.ar
                                                                        }
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}

                            {/* Show Full Details Button */}
                            <Link
                                href={ShowProductController.show.url(
                                    previewProduct.id,
                                )}
                            >
                                <Button
                                    type="button"
                                    className="mt-2 h-10 w-full"
                                    variant="outline"
                                >
                                    Show full details
                                </Button>
                            </Link>
                        </div>
                    ) : null}
                </SheetContent>
            </Sheet>
        </>
    );
}
