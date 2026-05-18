import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowDropController from '@/actions/App/Http/Controllers/Admin/Drops/ShowDropController';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/components/ui/sheet';
import {
    IconCheck,
    IconAlertTriangle,
    IconLoader2,
    IconBan,
} from '@tabler/icons-react';
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
    IconLayoutGrid,
    IconFolder,
    IconChevronLeft,
    IconChevronRight,
} from '@tabler/icons-react';

interface Drop {
    id: number;
    title: string;
    description: string;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
    creator: {
        id: number;
        username: string;
    } | null;
    products_count: number;
    liked_drops_count: number;
    saved_drops_count: number;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedDrops {
    data: Drop[];
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

interface DropListProps {
    drops: PaginatedDrops;
    filters: FilterProps;
    statuses: { [key: number]: string };
    stats: {
        total: number;
        draft: number;
        published: number;
        ended: number;
        cancelled: number;
        rejected: number;
    };
}

export default function DropList({
    drops,
    filters,
    statuses,
    stats,
}: DropListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    // Sheet Preview States
    const [selectedDropId, setSelectedDropId] = React.useState<number | null>(
        null,
    );
    const [sheetOpen, setSheetOpen] = React.useState(false);
    const [loadingDrop, setLoadingDrop] = React.useState(false);
    const [previewDrop, setPreviewDrop] = React.useState<any>(null);
    const [previewProducts, setPreviewProducts] = React.useState<any[]>([]);

    // Sheet Status Management Form States
    const [formStatus, setFormStatus] = React.useState<string>('');
    const [reasonEn, setReasonEn] = React.useState<string>('');
    const [reasonFr, setReasonFr] = React.useState<string>('');
    const [reasonAr, setReasonAr] = React.useState<string>('');
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    const [submitSuccess, setSubmitSuccess] = React.useState(false);

    React.useEffect(() => {
        if (selectedDropId && sheetOpen) {
            setLoadingDrop(true);
            setPreviewDrop(null);
            setPreviewProducts([]);
            setSubmitSuccess(false);

            fetch(ShowDropController.show.url(selectedDropId), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    setPreviewDrop(data.drop);
                    setPreviewProducts(data.products || []);
                    setFormStatus(data.drop.status);
                    setReasonEn('');
                    setReasonFr('');
                    setReasonAr('');
                })
                .catch((err) => console.error(err))
                .finally(() => setLoadingDrop(false));
        }
    }, [selectedDropId, sheetOpen]);

    const handleSheetSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setSubmitSuccess(false);

        router.put(
            ShowDropController.update.url(selectedDropId!),
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
                    // Refetch the preview drop to show updated history
                    fetch(ShowDropController.show.url(selectedDropId!), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((res) => res.json())
                        .then((data) => {
                            setPreviewDrop(data.drop);
                            setPreviewProducts(data.products || []);
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

    // Perform search and apply filters
    const applyFilters = (
        search = searchTerm,
        status = statusVal,
        limit = perPage,
    ) => {
        router.get(
            '/admin/drops',
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

    // Debounced search effect
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
        router.get('/admin/drops', {}, { preserveState: true, replace: true });
    };

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Drops', href: '/admin/drops' },
    ];

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
            case 'ended':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Ended
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100/80 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Cancelled
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
            <Head title="Drops Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Drops
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Monitor, moderate, and manage user drops,
                            collections, and statuses.
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
                        <Button
                            size="sm"
                            className="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <IconPlus className="size-4" />
                            <span>Create Drop</span>
                        </Button>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Total Drops
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
                            Ended
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.ended}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-amber-500 uppercase">
                            Cancelled
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.cancelled}
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

                {/* Filter and Table Container */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    {/* Controls Bar */}
                    <div className="flex flex-col items-center justify-between gap-4 border-b bg-muted/20 p-4 sm:flex-row">
                        <div className="flex w-full flex-1 flex-col items-center gap-2 sm:flex-row">
                            <div className="relative w-full sm:max-w-xs">
                                <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search drops or creators..."
                                    className="h-9 w-full bg-background pl-8"
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                />
                            </div>

                            <Select
                                value={statusVal}
                                onValueChange={handleStatusChange}
                            >
                                <SelectTrigger className="h-9 w-full bg-background sm:w-44">
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Statuses
                                    </SelectItem>
                                    {Object.entries(statuses).map(
                                        ([key, value]) => (
                                            <SelectItem key={key} value={value}>
                                                <span className="capitalize">
                                                    {value}
                                                </span>
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>

                            {(searchTerm ||
                                statusVal !== 'all' ||
                                perPage !== '10') && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={handleClearFilters}
                                    className="h-9 px-3 py-1 text-xs font-semibold hover:bg-muted"
                                >
                                    Clear Filters
                                </Button>
                            )}
                        </div>

                        <div className="flex w-full items-center justify-end gap-2 sm:w-auto">
                            <span className="text-xs whitespace-nowrap text-muted-foreground">
                                Show
                            </span>
                            <Select
                                value={perPage}
                                onValueChange={handlePerPageChange}
                            >
                                <SelectTrigger className="h-9 w-18 bg-background">
                                    <SelectValue placeholder="10" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="10">10</SelectItem>
                                    <SelectItem value="25">25</SelectItem>
                                    <SelectItem value="50">50</SelectItem>
                                </SelectContent>
                            </Select>
                            <span className="text-xs whitespace-nowrap text-muted-foreground">
                                per page
                            </span>
                        </div>
                    </div>

                    {/* Table Element */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="bg-muted/40">
                                <TableRow>
                                    <TableHead className="w-12 py-3.5 pl-6 text-xs font-semibold">
                                        ID
                                    </TableHead>
                                    <TableHead className="py-3.5 text-xs font-semibold">
                                        Drop Details
                                    </TableHead>
                                    <TableHead className="py-3.5 text-xs font-semibold">
                                        Creator
                                    </TableHead>
                                    <TableHead className="py-3.5 text-xs font-semibold">
                                        Status
                                    </TableHead>
                                    <TableHead className="py-3.5 text-center text-xs font-semibold">
                                        Products
                                    </TableHead>
                                    <TableHead className="py-3.5 text-center text-xs font-semibold">
                                        Likes / Saves
                                    </TableHead>
                                    <TableHead className="py-3.5 text-xs font-semibold">
                                        Starts At
                                    </TableHead>
                                    <TableHead className="py-3.5 text-xs font-semibold">
                                        Ends At
                                    </TableHead>
                                    <TableHead className="py-3.5 pr-6 text-right text-xs font-semibold">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {drops.data.length > 0 ? (
                                    drops.data.map((drop) => (
                                        <TableRow
                                            key={drop.id}
                                            className="group transition-colors hover:bg-muted/30"
                                        >
                                            <TableCell className="py-4 pl-6 font-mono text-xs font-semibold text-muted-foreground">
                                                #{drop.id}
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-semibold text-foreground transition-colors group-hover:text-primary">
                                                        {drop.title ||
                                                            'Untitled'}
                                                    </span>
                                                    <span className="mt-0.5 line-clamp-1 max-w-[260px] text-xs text-muted-foreground">
                                                        {drop.description ||
                                                            'No description provided.'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {drop.creator ? (
                                                    <div className="flex items-center gap-1.5 text-sm text-foreground">
                                                        <IconUser className="size-4 text-muted-foreground" />
                                                        <span className="font-medium">
                                                            @
                                                            {
                                                                drop.creator
                                                                    .username
                                                            }
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">
                                                        Anonymous
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {getStatusBadge(drop.status)}
                                            </TableCell>
                                            <TableCell className="py-4 text-center">
                                                <Badge
                                                    variant="secondary"
                                                    className="rounded-md px-2 py-0.5 text-xs font-semibold"
                                                >
                                                    {drop.products_count}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex items-center justify-center gap-3 text-xs text-muted-foreground">
                                                    <span className="flex items-center gap-1">
                                                        <IconHeart className="size-3 fill-rose-500/20 text-rose-500" />
                                                        {drop.liked_drops_count}
                                                    </span>
                                                    <span className="flex items-center gap-1">
                                                        <IconBookmark className="size-3 fill-blue-500/20 text-blue-500" />
                                                        {drop.saved_drops_count}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5 text-muted-foreground" />
                                                    <span>
                                                        {formatDate(
                                                            drop.starts_at,
                                                        )}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5 text-muted-foreground" />
                                                    <span>
                                                        {formatDate(
                                                            drop.ends_at,
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
                                                            setSelectedDropId(
                                                                drop.id,
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
                                                            href={ShowDropController.show.url(
                                                                drop.id,
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
                                                        No drops found
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
                    {drops.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {drops.from} to {drops.to} of{' '}
                                {drops.total} drops
                            </span>

                            <div className="flex items-center gap-1.5">
                                {drops.links.map((link, idx) => {
                                    const isPrev =
                                        link.label.includes('Previous');
                                    const isNext = link.label.includes('Next');

                                    // Style non-page labels or keep as text
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

            {/* Dynamic Drop Management Sheet */}
            <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                <SheetContent
                    side="right"
                    className="flex w-full flex-col overflow-y-auto border-l bg-card p-0 shadow-xl sm:max-w-xl"
                >
                    <SheetHeader className="border-b border-muted/50 bg-muted/10 p-6">
                        <div className="flex items-center justify-between gap-3">
                            <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                                Manage Drop Preview #{selectedDropId}
                            </SheetTitle>
                            {previewDrop && getStatusBadge(previewDrop.status)}
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Review stats, products list, and perform quick
                            status adjustments.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingDrop ? (
                        <div className="flex flex-grow flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Loading drop information...
                            </span>
                        </div>
                    ) : previewDrop ? (
                        <div className="flex flex-col gap-2 p-3">
                            {/* Drop Overview stats */}
                            <div className="flex flex-col gap-3 rounded-xl border bg-muted/10 p-4 text-sm">
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Overview
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Title
                                        </span>
                                        <span className="text-xs font-bold text-foreground">
                                            {previewDrop.title}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Creator
                                        </span>
                                        <span className="text-xs font-bold text-foreground">
                                            {previewDrop.creator
                                                ? `@${previewDrop.creator.username}`
                                                : 'Anonymous'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Starts At
                                        </span>
                                        <span className="text-xs font-medium text-foreground">
                                            {formatDate(previewDrop.starts_at)}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Ends At
                                        </span>
                                        <span className="text-xs font-medium text-foreground">
                                            {formatDate(previewDrop.ends_at)}
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

                            {/* Linked Products Preview in Sheet */}
                            <div className="flex flex-grow flex-col gap-3">
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Associated Products (
                                    {previewProducts.length})
                                </div>

                                {previewProducts.length > 0 ? (
                                    <div className="flex max-h-[300px] flex-col gap-2.5 overflow-y-auto pr-1">
                                        {previewProducts.map((prod) => (
                                            <div
                                                key={prod.id}
                                                className="group flex items-center justify-between rounded-lg border bg-muted/5 p-2.5 transition-colors hover:bg-muted/15"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div className="size-10 shrink-0 overflow-hidden rounded-md border bg-muted/10">
                                                        <img
                                                            src={
                                                                prod.image || ''
                                                            }
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
                                                    <div className="flex flex-col">
                                                        <span className="line-clamp-1 text-xs font-semibold text-foreground transition-colors group-hover:text-primary">
                                                            {prod.name}
                                                        </span>
                                                        <span className="text-[10px] text-muted-foreground capitalize">
                                                            {prod.status}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="flex shrink-0 flex-col items-end">
                                                    <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                        $
                                                        {prod.drop_price.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                    <span className="text-[10px] text-muted-foreground line-through">
                                                        $
                                                        {prod.original_price.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="rounded-lg border border-dashed py-6 text-center text-xs text-muted-foreground">
                                        No products linked to this drop yet.
                                    </div>
                                )}
                            </div>

                            <Link
                                href={
                                    ShowDropController.show({
                                        drop: previewDrop.id,
                                    }).url
                                }
                            >
                                <Button
                                    type="submit"
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
