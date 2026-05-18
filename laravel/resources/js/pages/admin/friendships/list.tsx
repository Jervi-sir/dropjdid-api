import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ListFriendshipsController from '@/actions/App/Http/Controllers/Admin/Friendships/ListFriendshipsController';
import ActionFriendshipController from '@/actions/App/Http/Controllers/Admin/Friendships/ActionFriendshipController';
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
import {
    IconSearch,
    IconUsers,
    IconArrowRight,
    IconLoader2,
    IconRefresh,
    IconChevronLeft,
    IconChevronRight,
    IconHeart,
    IconMessageCircle,
    IconUser,
    IconCircleCheck,
    IconAlertTriangle,
    IconBan,
    IconTrash,
} from '@tabler/icons-react';

interface UserProfile {
    id: number;
    full_name: string;
    username: string;
    email: string;
    image: string | null;
}

interface Friendship {
    id: number;
    sender: UserProfile | null;
    receiver: UserProfile | null;
    status: string;
    status_raw: number;
    accepted_at: string | null;
    rejected_at: string | null;
    blocked_at: string | null;
    created_at: string;
}

interface ConversationStats {
    id: number;
    type: string;
    type_raw: number;
    messages_count: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedFriendships {
    data: Friendship[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface ListFriendshipsProps {
    friendships: PaginatedFriendships;
    kpis: {
        total_count: number;
        accepted_count: number;
        pending_count: number;
        blocked_count: number;
    };
    filters: {
        search: string;
        status: string;
        per_page: number;
    };
}

export default function ListFriendships({
    friendships,
    kpis,
    filters,
}: ListFriendshipsProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [statusFilter, setStatusFilter] = React.useState(
        filters.status || 'all',
    );

    // Sheet Preview States
    const [previewOpen, setPreviewOpen] = React.useState(false);
    const [previewFriendship, setPreviewFriendship] =
        React.useState<Friendship | null>(null);
    const [conversationStats, setConversationStats] =
        React.useState<ConversationStats | null>(null);
    const [loadingDetails, setLoadingDetails] = React.useState(false);

    // Form hook for saving friendship status
    const { data, setData, put, processing, errors, clearErrors } = useForm({
        status: 0,
    });

    // Fetch individual friendship details (including conversation stats) for preview
    const handleOpenPreview = async (friendship: Friendship) => {
        setPreviewOpen(true);
        setLoadingDetails(true);
        setPreviewFriendship(friendship);
        setConversationStats(null);
        clearErrors();

        setData({
            status: friendship.status_raw,
        });

        try {
            const response = await fetch(
                ActionFriendshipController.show.url(friendship.id),
                {
                    headers: { Accept: 'application/json' },
                },
            );
            if (response.ok) {
                const result = await response.json();
                setPreviewFriendship(result.friendship);
                setConversationStats(result.conversation);
            }
        } catch (e) {
            console.error(e);
        } finally {
            setLoadingDetails(false);
        }
    };

    const handleUpdateStatus = (e: React.FormEvent) => {
        e.preventDefault();
        if (!previewFriendship) return;

        put(ActionFriendshipController.update.url(previewFriendship.id), {
            preserveScroll: true,
            onSuccess: () => {
                setPreviewOpen(false);
            },
        });
    };

    const handleDeleteFriendship = (friendshipId: number) => {
        if (
            confirm(
                'Are you sure you want to terminate this friendship relationship completely?',
            )
        ) {
            router.delete(
                ActionFriendshipController.destroy.url(friendshipId),
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const applyFilters = () => {
        router.get(
            '/admin/friendships',
            {
                search: searchTerm || undefined,
                status: statusFilter !== 'all' ? statusFilter : undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    React.useEffect(() => {
        const timer = setTimeout(() => {
            applyFilters();
        }, 450);
        return () => clearTimeout(timer);
    }, [searchTerm, statusFilter]);

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'accepted':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Accepted
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Rejected
                    </Badge>
                );
            case 'blacked':
            case 'blocked':
                return (
                    <Badge className="border border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-200">
                        Blocked
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    return (
        <>
            <Head title="Friendships Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Page Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Friendships
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Audit peer-to-peer user relationships, verify active
                            direct message channels, and manage connection
                            states.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={applyFilters}
                        className="gap-1 self-start md:self-auto"
                    >
                        <IconRefresh className="size-4" />
                        <span>Sync Directory</span>
                    </Button>
                </div>

                {/* KPI Grid */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <IconUsers className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Total Connections
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.total_count}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <IconHeart className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Active Friends
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.accepted_count} Pairs
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <IconLoader2 className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Pending Requests
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.pending_count} Sent
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <IconBan className="size-6 stroke-[1.8]" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Blocked Pairs
                            </span>
                            <span className="mt-0.5 text-xl font-black text-foreground">
                                {kpis.blocked_count} Blocked
                            </span>
                        </div>
                    </div>
                </div>

                {/* Filter bar and Table */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    <div className="flex flex-col items-center gap-4 border-b bg-muted/15 p-4 lg:flex-row">
                        <div className="relative w-full shrink-0 lg:max-w-xs">
                            <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search sender or receiver..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-10 w-full bg-background pl-9"
                            />
                        </div>

                        <div className="flex w-full flex-wrap items-center gap-3 lg:justify-end">
                            <div className="flex shrink-0 items-center gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">
                                    Status:
                                </span>
                                <Select
                                    value={statusFilter}
                                    onValueChange={setStatusFilter}
                                >
                                    <SelectTrigger className="h-9 w-[140px] bg-background">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="1">
                                            Accepted
                                        </SelectItem>
                                        <SelectItem value="2">
                                            Rejected
                                        </SelectItem>
                                        <SelectItem value="3">
                                            Blocked
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
                                        Sender User
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Direction
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Receiver User
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Status
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Initiated Date
                                    </TableHead>
                                    <TableHead className="w-[200px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {friendships.data.length > 0 ? (
                                    friendships.data.map((friendship) => (
                                        <TableRow
                                            key={friendship.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-xs font-bold text-primary">
                                                        {friendship.sender
                                                            ?.image ? (
                                                            <img
                                                                src={
                                                                    friendship
                                                                        .sender
                                                                        .image
                                                                }
                                                                alt={
                                                                    friendship
                                                                        .sender
                                                                        .username
                                                                }
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <IconUser className="size-4 stroke-[1.8]" />
                                                        )}
                                                    </div>
                                                    <div className="flex flex-col gap-0.5">
                                                        <span className="text-xs font-extrabold text-foreground">
                                                            {friendship.sender
                                                                ?.full_name ||
                                                                'N/A'}
                                                        </span>
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            @
                                                            {friendship.sender
                                                                ?.username ||
                                                                'N/A'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell className="py-4 text-center">
                                                <span className="text-xs font-bold text-muted-foreground/60">
                                                    ➔
                                                </span>
                                            </TableCell>

                                            <TableCell className="py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-xs font-bold text-primary">
                                                        {friendship.receiver
                                                            ?.image ? (
                                                            <img
                                                                src={
                                                                    friendship
                                                                        .receiver
                                                                        .image
                                                                }
                                                                alt={
                                                                    friendship
                                                                        .receiver
                                                                        .username
                                                                }
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <IconUser className="size-4 stroke-[1.8]" />
                                                        )}
                                                    </div>
                                                    <div className="flex flex-col gap-0.5">
                                                        <span className="text-xs font-extrabold text-foreground">
                                                            {friendship.receiver
                                                                ?.full_name ||
                                                                'N/A'}
                                                        </span>
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            @
                                                            {friendship.receiver
                                                                ?.username ||
                                                                'N/A'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell className="py-4 text-center">
                                                {getStatusBadge(
                                                    friendship.status,
                                                )}
                                            </TableCell>

                                            <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                {friendship.created_at
                                                    ? new Date(
                                                          friendship.created_at,
                                                      ).toLocaleString()
                                                    : 'N/A'}
                                            </TableCell>

                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5 font-bold"
                                                        onClick={() =>
                                                            handleOpenPreview(
                                                                friendship,
                                                            )
                                                        }
                                                    >
                                                        Manage
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="xs"
                                                        className="h-7 px-2 text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10"
                                                        onClick={() =>
                                                            handleDeleteFriendship(
                                                                friendship.id,
                                                            )
                                                        }
                                                    >
                                                        <IconTrash className="size-3.5" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-16 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconUsers className="size-12 stroke-[1.5] text-muted-foreground/50" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No friendships found
                                                    </p>
                                                    <p className="text-xs">
                                                        Adjust filter settings
                                                        or try a different
                                                        search phrase.
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
                    {friendships.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {friendships.from} to {friendships.to}{' '}
                                of {friendships.total} friendships
                            </span>

                            <div className="flex items-center gap-1.5">
                                {friendships.links.map((link, idx) => {
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

            {/* Slide-over Friendship Manager Sheet */}
            <Sheet open={previewOpen} onOpenChange={setPreviewOpen}>
                <SheetContent className="flex h-full flex-col bg-card sm:max-w-md">
                    <SheetHeader className="border-b pb-4">
                        <SheetTitle className="text-lg font-black text-foreground">
                            Friendship Auditor
                        </SheetTitle>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Review connections, verify direct message status,
                            and change relationship states.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingDetails ? (
                        <div className="flex flex-grow flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Auditing connections...
                            </span>
                        </div>
                    ) : previewFriendship ? (
                        <form
                            onSubmit={handleUpdateStatus}
                            className="flex flex-grow flex-col justify-between"
                        >
                            <div className="flex flex-col gap-6 overflow-y-auto p-4">
                                {/* Sender & Receiver Card */}
                                <div className="flex flex-col gap-4 rounded-2xl border bg-muted/20 p-4">
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-sm font-bold text-primary">
                                            {previewFriendship.sender?.image ? (
                                                <img
                                                    src={
                                                        previewFriendship.sender
                                                            .image
                                                    }
                                                    alt={
                                                        previewFriendship.sender
                                                            .username
                                                    }
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <IconUser className="size-5 stroke-[1.8]" />
                                            )}
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold tracking-wider text-primary uppercase">
                                                Sender (Initiator)
                                            </span>
                                            <span className="text-xs font-extrabold text-foreground">
                                                {previewFriendship.sender
                                                    ?.full_name || 'N/A'}
                                            </span>
                                            <span className="text-[10px] font-semibold text-muted-foreground">
                                                @
                                                {previewFriendship.sender
                                                    ?.username || 'N/A'}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="my-1 flex justify-center border-t border-dashed py-1">
                                        <span className="font-mono text-xs font-bold text-muted-foreground/60">
                                            ➔ CONNECTED ➔
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <div className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-sm font-bold text-primary">
                                            {previewFriendship.receiver
                                                ?.image ? (
                                                <img
                                                    src={
                                                        previewFriendship
                                                            .receiver.image
                                                    }
                                                    alt={
                                                        previewFriendship
                                                            .receiver.username
                                                    }
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <IconUser className="size-5 stroke-[1.8]" />
                                            )}
                                        </div>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold tracking-wider text-primary uppercase">
                                                Receiver
                                            </span>
                                            <span className="text-xs font-extrabold text-foreground">
                                                {previewFriendship.receiver
                                                    ?.full_name || 'N/A'}
                                            </span>
                                            <span className="text-[10px] font-semibold text-muted-foreground">
                                                @
                                                {previewFriendship.receiver
                                                    ?.username || 'N/A'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Conversation Stats Panel */}
                                <div className="flex flex-col gap-3 rounded-xl border bg-background/50 p-4">
                                    <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                        Direct Message Audit
                                    </span>

                                    {conversationStats ? (
                                        <div className="flex flex-col gap-2">
                                            <div className="flex items-center justify-between border-b pb-2">
                                                <span className="text-xs font-semibold text-muted-foreground">
                                                    Conversation Channel:
                                                </span>
                                                <div className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                    <IconCircleCheck className="size-3.5" />
                                                    <span>Active Channel</span>
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <span className="text-xs font-semibold text-muted-foreground">
                                                    Total Exchanged Messages:
                                                </span>
                                                <div className="inline-flex items-center gap-1 text-xs font-black text-primary">
                                                    <IconMessageCircle className="size-4 shrink-0" />
                                                    <span>
                                                        {
                                                            conversationStats.messages_count
                                                        }{' '}
                                                        Messages
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="flex items-center gap-2 rounded-lg border border-border bg-slate-500/5 p-2">
                                            <IconAlertTriangle className="size-5 shrink-0 text-amber-500" />
                                            <div className="flex flex-col">
                                                <span className="text-xs font-extrabold text-foreground">
                                                    No Private Conversation
                                                </span>
                                                <span className="text-[10px] text-muted-foreground">
                                                    These two users have not
                                                    exchanged direct messages.
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Moderator Dropdown */}
                                <div className="flex flex-col gap-2">
                                    <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        Relationship Status
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
                                                Pending Request
                                            </SelectItem>
                                            <SelectItem value="1">
                                                Accepted (Friends)
                                            </SelectItem>
                                            <SelectItem value="2">
                                                Rejected
                                            </SelectItem>
                                            <SelectItem value="3">
                                                Blocked
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && (
                                        <span className="text-xs font-semibold text-rose-500">
                                            {errors.status}
                                        </span>
                                    )}
                                </div>
                            </div>

                            <SheetFooter className="flex flex-col gap-2 border-t bg-muted/10 p-4">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full font-bold shadow-xs"
                                >
                                    {processing
                                        ? 'Saving...'
                                        : 'Sync Relationship'}
                                </Button>

                                <Button
                                    type="button"
                                    variant="outline"
                                    className="w-full border-rose-500/20 font-bold text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                                    onClick={() => {
                                        setPreviewOpen(false);
                                        handleDeleteFriendship(
                                            previewFriendship.id,
                                        );
                                    }}
                                >
                                    <IconTrash className="mr-1.5 size-4" />
                                    <span>Delete Relationship Relation</span>
                                </Button>
                            </SheetFooter>
                        </form>
                    ) : null}
                </SheetContent>
            </Sheet>
        </>
    );
}
