import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
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
    IconRefresh,
    IconPlus,
    IconChevronLeft,
    IconChevronRight,
    IconTrophy,
    IconEdit,
    IconEye,
} from '@tabler/icons-react';

interface Creator {
    id: number;
    username: string;
    full_name: string;
}

interface Prize {
    id: number;
    title: string;
    image: string | null;
    description: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    joinings_count: number;
    creator: Creator | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPrizes {
    data: Prize[];
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

interface PrizeListProps {
    prizes: PaginatedPrizes;
    filters: FilterProps;
    statuses: { [key: number]: string };
    stats: {
        total: number;
        draft: number;
        active: number;
        ended: number;
        cancelled: number;
    };
}

export default function PrizeList({
    prizes,
    filters,
    statuses,
    stats,
}: PrizeListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    const applyFilters = (
        search = searchTerm,
        status = statusVal,
        limit = perPage,
    ) => {
        router.get(
            '/admin/prizes',
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
        router.get('/admin/prizes', {}, { preserveState: true, replace: true });
    };

    const getStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'active':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Active
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
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Cancelled
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
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <>
            <Head title="Prizes Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Prizes Management
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Create, schedule, edit, and draw lucky winners for promotional prizes.
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
                            className="gap-1.5 font-bold shadow-md bg-indigo-600 text-white hover:bg-indigo-500"
                            asChild
                        >
                            <Link href="/admin/prizes/create">
                                <IconPlus className="size-4" />
                                <span>Create Prize</span>
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Total Prizes
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.total}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs">
                        <span className="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                            Active
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.active}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs">
                        <span className="text-xs font-semibold tracking-wider text-indigo-500 uppercase">
                            Ended
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.ended}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs">
                        <span className="text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            Drafts
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.draft}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs">
                        <span className="text-xs font-semibold tracking-wider text-rose-500 uppercase">
                            Cancelled
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.cancelled}
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
                                    placeholder="Search prizes..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
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
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        {Object.entries(statuses).map(([key, value]) => (
                                            <SelectItem key={key} value={value}>
                                                <span className="capitalize">{value}</span>
                                            </SelectItem>
                                        ))}
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
                                        Prize details
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Active Dates
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Participants
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Created By
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Status
                                    </TableHead>
                                    <TableHead className="w-[200px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {prizes.data.length > 0 ? (
                                    prizes.data.map((prize) => (
                                        <TableRow
                                            key={prize.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="size-12 shrink-0 overflow-hidden rounded-lg border bg-muted/10 shadow-sm flex items-center justify-center">
                                                    {prize.image ? (
                                                        <img
                                                            src={prize.image}
                                                            alt={prize.title}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <IconTrophy className="size-5 text-muted-foreground/60" />
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex max-w-[240px] flex-col">
                                                    <span className="truncate text-sm font-bold text-foreground">
                                                        {prize.title}
                                                    </span>
                                                    <span className="mt-0.5 line-clamp-1 text-xs text-muted-foreground">
                                                        {prize.description || 'No description provided.'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                <div className="flex flex-col gap-0.5">
                                                    <div className="flex items-center gap-1.5">
                                                        <span className="text-[10px] text-muted-foreground uppercase">Starts:</span>
                                                        <span>{formatDate(prize.starts_at)}</span>
                                                    </div>
                                                    <div className="flex items-center gap-1.5">
                                                        <span className="text-[10px] text-muted-foreground uppercase">Ends:</span>
                                                        <span>{formatDate(prize.ends_at)}</span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-center">
                                                <Badge
                                                    variant="outline"
                                                    className="bg-muted/10 px-2.5 py-0.5 text-xs font-bold"
                                                >
                                                    {prize.joinings_count} joined
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-xs font-bold text-foreground">
                                                        {prize.creator?.full_name || 'System'}
                                                    </span>
                                                    <span className="text-[10px] text-muted-foreground">
                                                        {prize.creator ? `@${prize.creator.username}` : ''}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {getStatusBadge(prize.status)}
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5"
                                                        asChild
                                                    >
                                                        <Link href={`/admin/prizes/${prize.id}`}>
                                                            <IconEye className="size-3.5 mr-1" />
                                                            <span>Details</span>
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2"
                                                        asChild
                                                    >
                                                        <Link href={`/admin/prizes/${prize.id}/edit`}>
                                                            <IconEdit className="size-3.5" />
                                                        </Link>
                                                    </Button>
                                                    {prize.status.toLowerCase() === 'active' && (
                                                        <Button
                                                            variant="default"
                                                            size="xs"
                                                            className="h-7 px-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/prizes/${prize.id}/pick-winner`}>
                                                                <IconTrophy className="size-3.5 mr-1" />
                                                                <span>Draw</span>
                                                            </Link>
                                                        </Button>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconTrophy className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No prizes found
                                                    </p>
                                                    <p className="text-xs">
                                                        Create a new prize or adjust your filter options.
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
                    {prizes.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {prizes.from} to {prizes.to} of {prizes.total} prizes
                            </span>

                            <div className="flex items-center gap-1.5">
                                {prizes.links.map((link, idx) => {
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
                                            className={`inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'} ${link.active ? 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90' : 'border border-border bg-card text-foreground'} `}
                                        >
                                            {isPrev && <IconChevronLeft className="-ml-0.5 size-3.5" />}
                                            <span>{label}</span>
                                            {isNext && <IconChevronRight className="-mr-0.5 size-3.5" />}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
