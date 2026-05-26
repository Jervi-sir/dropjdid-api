import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import BecomeSgmController from '@/actions/App/Http/Controllers/Admin/UserSupportRequest/BecomeSgmController';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
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
    IconRefresh,
    IconChevronLeft,
    IconChevronRight,
    IconCheck,
    IconLoader2,
    IconMail,
    IconPhone,
    IconShield,
    IconBuildingStore,
    IconAlertCircle,
    IconX,
    IconUserCheck,
} from '@tabler/icons-react';

interface Role {
    id: number;
    code: string;
    en: string;
}

interface UserStore {
    id: number;
    name: string;
}

interface User {
    id: number;
    full_name: string;
    username: string;
    email: string;
    phone_number: string;
    image: string | null;
    is_active: boolean;
    roles: Role[];
    stores?: UserStore[];
    created_at?: string;
}

interface SgmRequest {
    id: number;
    user_id: number;
    contact: string;
    type: number;
    status: number;
    status_label: string;
    note: string | null;
    reviewed_at: string | null;
    created_at: string;
    user: User | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRequests {
    data: SgmRequest[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface ListToApproveProps {
    requests: PaginatedRequests;
    filters: {
        search: string;
        status: string;
        per_page: number;
    };
    stats: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
}

export default function ListToApprove({
    requests,
    filters,
    stats,
}: ListToApproveProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusFilter, setStatusFilter] = React.useState(
        filters.status || 'all',
    );
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    // Sheet states
    const [selectedRequestId, setSelectedRequestId] = React.useState<
        number | null
    >(null);
    const [sheetOpen, setSheetOpen] = React.useState(false);
    const [loadingDetails, setLoadingDetails] = React.useState(false);
    const [previewRequest, setPreviewRequest] =
        React.useState<SgmRequest | null>(null);

    // Rejection state
    const [showRejectionForm, setShowRejectionForm] = React.useState(false);
    const [rejectionNote, setRejectionNote] = React.useState('');
    const [rejectionError, setRejectionError] = React.useState('');

    // Processing states
    const [isApproving, setIsApproving] = React.useState(false);
    const [isRejecting, setIsRejecting] = React.useState(false);

    // Fetch full request details when opened
    React.useEffect(() => {
        if (selectedRequestId && sheetOpen) {
            setLoadingDetails(true);
            setPreviewRequest(null);
            setShowRejectionForm(false);
            setRejectionNote('');
            setRejectionError('');

            fetch(BecomeSgmController.show.url(selectedRequestId), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    setPreviewRequest(data.request);
                })
                .catch((err) => console.error(err))
                .finally(() => setLoadingDetails(false));
        }
    }, [selectedRequestId, sheetOpen]);

    const applyFilters = (
        search = searchTerm,
        status = statusFilter,
        limit = perPage,
    ) => {
        router.get(
            BecomeSgmController.index.url(),
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
                applyFilters(searchTerm, statusFilter, perPage);
            }
        }, 450);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    const handleStatusFilterChange = (value: string) => {
        setStatusFilter(value);
        applyFilters(searchTerm, value, perPage);
    };

    const handlePerPageChange = (value: string) => {
        setPerPage(value);
        applyFilters(searchTerm, statusFilter, value);
    };

    const handleClearFilters = () => {
        setSearchTerm('');
        setStatusFilter('all');
        setPerPage('10');
        router.get(
            BecomeSgmController.index.url(),
            {},
            { preserveState: true, replace: true },
        );
    };

    const handleApprove = () => {
        if (!selectedRequestId) return;
        setIsApproving(true);

        router.post(
            BecomeSgmController.approve.url(selectedRequestId),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setSheetOpen(false);
                },
                onFinish: () => {
                    setIsApproving(false);
                },
            },
        );
    };

    const handleRejectSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedRequestId) return;
        if (!rejectionNote.trim()) {
            setRejectionError('Rejection note is required.');
            return;
        }

        setIsRejecting(true);
        setRejectionError('');

        router.post(
            BecomeSgmController.reject.url(selectedRequestId),
            {
                note: rejectionNote,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setSheetOpen(false);
                },
                onError: (errors) => {
                    if (errors.note) {
                        setRejectionError(errors.note);
                    } else {
                        setRejectionError(
                            'An error occurred during rejection.',
                        );
                    }
                },
                onFinish: () => {
                    setIsRejecting(false);
                },
            },
        );
    };

    const getStatusBadge = (statusVal: number) => {
        switch (statusVal) {
            case 0: // pending
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100/80 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            case 1: // approved
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Approved
                    </Badge>
                );
            case 2: // rejected
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Rejected
                    </Badge>
                );
            default:
                return <Badge variant="outline">Unknown</Badge>;
        }
    };

    const formatDate = (dateString: string | null) => {
        if (!dateString) return 'N/A';
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
            <Head title="SGM Applications Moderation" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            SGM Requests
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Moderate users applying to become SGMs. Review
                            profiles and approve or reject submissions.
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

                {/* Stats Cards */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Total Requests
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.total}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-amber-500 uppercase">
                            Pending
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.pending}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-emerald-500 uppercase">
                            Approved
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.approved}
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
                                    placeholder="Search name, phone, email..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="h-10 w-full bg-background pl-9"
                                />
                            </div>

                            <div className="w-full sm:w-44">
                                <Select
                                    value={statusFilter}
                                    onValueChange={handleStatusFilterChange}
                                >
                                    <SelectTrigger className="h-10 border-input bg-background">
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="1">
                                            Approved
                                        </SelectItem>
                                        <SelectItem value="2">
                                            Rejected
                                        </SelectItem>
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

                    {/* Table */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="border-b bg-muted/15">
                                <TableRow>
                                    <TableHead className="w-[60px] py-4 pl-6">
                                        Avatar
                                    </TableHead>
                                    <TableHead className="min-w-[180px] py-4">
                                        User Details
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Contact Info
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Provided Phone
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Status
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Created At
                                    </TableHead>
                                    <TableHead className="w-[160px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {requests.data.length > 0 ? (
                                    requests.data.map((req) => (
                                        <TableRow
                                            key={req.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted/10 text-xs font-bold text-primary shadow-inner">
                                                    {req.user?.image ? (
                                                        <img
                                                            src={req.user.image}
                                                            alt={
                                                                req.user
                                                                    .full_name
                                                            }
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        req.user?.full_name
                                                            ?.charAt(0)
                                                            .toUpperCase() ||
                                                        'U'
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-foreground">
                                                        {req.user?.full_name ||
                                                            'Deleted User'}
                                                    </span>
                                                    <span className="mt-0.5 text-xs text-muted-foreground">
                                                        {req.user
                                                            ? `@${req.user.username}`
                                                            : ''}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold">
                                                <div className="flex flex-col justify-center gap-1">
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <IconMail className="size-3.5" />
                                                        <span>
                                                            {req.user?.email ||
                                                                'No email'}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <IconPhone className="size-3.5" />
                                                        <span>
                                                            {req.user
                                                                ?.phone_number ||
                                                                'No phone'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-sm font-medium text-foreground">
                                                {req.contact}
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {getStatusBadge(req.status)}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5" />
                                                    <span>
                                                        {formatDate(
                                                            req.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                <Button
                                                    variant="outline"
                                                    size="xs"
                                                    className="h-7 px-2.5 font-bold"
                                                    onClick={() => {
                                                        setSelectedRequestId(
                                                            req.id,
                                                        );
                                                        setSheetOpen(true);
                                                    }}
                                                >
                                                    Review
                                                </Button>
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
                                                <IconUserCheck className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No requests found
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

                    {/* Pagination */}
                    {requests.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {requests.from} to {requests.to} of{' '}
                                {requests.total} requests
                            </span>

                            <div className="flex items-center gap-1.5">
                                {requests.links.map((link, idx) => {
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

            {/* Moderation Sheet */}
            <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                <SheetContent
                    side="right"
                    className="flex w-full flex-col overflow-y-auto border-l bg-card p-0 shadow-xl sm:max-w-xl"
                >
                    <SheetHeader className="border-b border-muted/50 bg-muted/10 p-6">
                        <div className="flex items-center justify-between gap-3">
                            <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                                Review SGM Request #{selectedRequestId}
                            </SheetTitle>
                            {previewRequest &&
                                getStatusBadge(previewRequest.status)}
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Examine user details, check history, and either
                            approve as an SGM or submit rejection reasons.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingDetails ? (
                        <div className="flex flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Loading request data...
                            </span>
                        </div>
                    ) : previewRequest ? (
                        <div className="flex flex-grow flex-col justify-between gap-6 p-6">
                            <div className="flex flex-col gap-6 overflow-y-auto pr-1">
                                {/* User Overview */}
                                <div className="flex flex-col gap-3.5 rounded-xl border bg-muted/10 p-4 text-sm">
                                    <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        User Details
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <div className="text-md flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted/15 font-bold text-primary shadow-sm">
                                            {previewRequest.user?.image ? (
                                                <img
                                                    src={
                                                        previewRequest.user
                                                            .image
                                                    }
                                                    alt={
                                                        previewRequest.user
                                                            .full_name
                                                    }
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                previewRequest.user?.full_name
                                                    ?.charAt(0)
                                                    .toUpperCase() || 'U'
                                            )}
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-sm font-bold text-foreground">
                                                {previewRequest.user
                                                    ?.full_name ||
                                                    'Deleted User'}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {previewRequest.user
                                                    ? `@${previewRequest.user.username}`
                                                    : ''}
                                            </span>
                                        </div>
                                    </div>

                                    <hr className="my-1 border-muted/50" />

                                    <div className="grid grid-cols-2 gap-4 text-xs font-semibold">
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Email Address
                                            </span>
                                            <span className="text-foreground">
                                                {previewRequest.user?.email ||
                                                    'None'}
                                            </span>
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Phone Number
                                            </span>
                                            <span className="text-foreground">
                                                {previewRequest.user
                                                    ?.phone_number || 'None'}
                                            </span>
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Registration Date
                                            </span>
                                            <span className="text-foreground">
                                                {formatDate(
                                                    previewRequest.user
                                                        ?.created_at || null,
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Current Roles
                                            </span>
                                            <div className="mt-0.5 flex flex-wrap gap-1">
                                                {previewRequest.user?.roles &&
                                                previewRequest.user.roles
                                                    .length > 0 ? (
                                                    previewRequest.user.roles.map(
                                                        (r) => (
                                                            <Badge
                                                                key={r.id}
                                                                variant="outline"
                                                                className="px-1 py-0 text-[9px] capitalize"
                                                            >
                                                                {r.en}
                                                            </Badge>
                                                        ),
                                                    )
                                                ) : (
                                                    <span className="text-[10px] text-foreground">
                                                        None
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Request Moderation Info */}
                                <div className="flex flex-col gap-3 rounded-xl border bg-card p-4 text-sm">
                                    <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        Request Overview
                                    </div>
                                    <div className="grid grid-cols-2 gap-4 text-xs font-semibold">
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Submitted Contact Phone
                                            </span>
                                            <span className="text-sm font-bold text-foreground">
                                                {previewRequest.contact}
                                            </span>
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Submitted Date
                                            </span>
                                            <span className="text-foreground">
                                                {formatDate(
                                                    previewRequest.created_at,
                                                )}
                                            </span>
                                        </div>
                                        {previewRequest.reviewed_at && (
                                            <div className="col-span-2 flex flex-col gap-0.5">
                                                <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                    Reviewed Date
                                                </span>
                                                <span className="text-foreground">
                                                    {formatDate(
                                                        previewRequest.reviewed_at,
                                                    )}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Rejected Notes / General Note display */}
                                    {previewRequest.note && (
                                        <div className="mt-2 rounded-lg border border-rose-500/20 bg-rose-500/10 p-3 text-xs">
                                            <span className="mb-1 block font-bold text-rose-700 dark:text-rose-400">
                                                Rejection Note
                                            </span>
                                            <p className="whitespace-pre-wrap text-muted-foreground">
                                                {previewRequest.note}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {/* Connected Stores */}
                                {previewRequest.user?.stores &&
                                    previewRequest.user.stores.length > 0 && (
                                        <div className="flex flex-col gap-2.5 rounded-xl border bg-card p-4">
                                            <div className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                                <IconBuildingStore className="size-4 text-emerald-600 dark:text-emerald-400" />
                                                <span>
                                                    Connected Stores (
                                                    {
                                                        previewRequest.user
                                                            .stores.length
                                                    }
                                                    )
                                                </span>
                                            </div>
                                            <div className="flex max-h-[140px] flex-col gap-2 overflow-y-auto pr-1">
                                                {previewRequest.user.stores.map(
                                                    (st) => (
                                                        <div
                                                            key={st.id}
                                                            className="flex items-center justify-between rounded-lg border border-muted bg-muted/20 p-2 text-xs font-bold text-foreground"
                                                        >
                                                            <span>
                                                                {st.name}
                                                            </span>
                                                            <Badge
                                                                variant="outline"
                                                                className="text-[9px]"
                                                            >
                                                                ID: {st.id}
                                                            </Badge>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    )}

                                {/* Moderation Actions or Forms */}
                                {previewRequest.status === 0 &&
                                    !showRejectionForm && (
                                        <div className="mt-4 flex flex-col gap-3">
                                            <div className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                                <IconShield className="size-4 text-primary" />
                                                <span>Moderation Actions</span>
                                            </div>
                                            <div className="grid grid-cols-2 gap-3">
                                                <Button
                                                    onClick={handleApprove}
                                                    disabled={isApproving}
                                                    className="bg-emerald-600 font-bold text-white hover:bg-emerald-700"
                                                >
                                                    {isApproving ? (
                                                        <>
                                                            <IconLoader2 className="mr-1.5 size-4 animate-spin" />
                                                            Approving...
                                                        </>
                                                    ) : (
                                                        <>
                                                            <IconCheck className="mr-1.5 size-4" />
                                                            Approve Request
                                                        </>
                                                    )}
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    onClick={() =>
                                                        setShowRejectionForm(
                                                            true,
                                                        )
                                                    }
                                                    className="font-bold"
                                                >
                                                    <IconX className="mr-1.5 size-4" />
                                                    Reject Request
                                                </Button>
                                            </div>
                                        </div>
                                    )}

                                {/* Rejection Note Form */}
                                {showRejectionForm && (
                                    <form
                                        onSubmit={handleRejectSubmit}
                                        className="mt-4 flex flex-col gap-4 rounded-xl border bg-muted/5 p-4"
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold tracking-wider text-rose-600 uppercase">
                                                Submit Rejection Note
                                            </span>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="xs"
                                                className="h-6 w-6 p-0"
                                                onClick={() => {
                                                    setShowRejectionForm(false);
                                                    setRejectionNote('');
                                                    setRejectionError('');
                                                }}
                                            >
                                                <IconX className="size-4 text-muted-foreground" />
                                            </Button>
                                        </div>
                                        <div className="flex flex-col gap-1.5">
                                            <label className="text-xs font-semibold text-muted-foreground">
                                                Provide a reason for rejecting
                                                this SGM application. The user
                                                will see this note.
                                            </label>
                                            <textarea
                                                value={rejectionNote}
                                                onChange={(e) =>
                                                    setRejectionNote(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Write rejection details here..."
                                                className="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                                rows={3}
                                                disabled={isRejecting}
                                            />
                                            {rejectionError && (
                                                <span className="flex items-center gap-1 text-xs font-bold text-rose-500">
                                                    <IconAlertCircle className="size-3.5 shrink-0" />
                                                    {rejectionError}
                                                </span>
                                            )}
                                        </div>
                                        <div className="mt-1 flex justify-end gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                disabled={isRejecting}
                                                onClick={() => {
                                                    setShowRejectionForm(false);
                                                    setRejectionNote('');
                                                    setRejectionError('');
                                                }}
                                            >
                                                Cancel
                                            </Button>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                size="sm"
                                                disabled={isRejecting}
                                            >
                                                {isRejecting ? (
                                                    <>
                                                        <IconLoader2 className="mr-1.5 size-4 animate-spin" />
                                                        Rejecting...
                                                    </>
                                                ) : (
                                                    'Submit Rejection'
                                                )}
                                            </Button>
                                        </div>
                                    </form>
                                )}
                            </div>

                            <SheetFooter className="border-t pt-4">
                                <Button
                                    variant="outline"
                                    type="button"
                                    onClick={() => setSheetOpen(false)}
                                    className="w-full font-semibold"
                                >
                                    Close Moderator Panel
                                </Button>
                            </SheetFooter>
                        </div>
                    ) : null}
                </SheetContent>
            </Sheet>
        </>
    );
}
