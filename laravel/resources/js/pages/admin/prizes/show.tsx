import * as React from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    IconArrowLeft,
    IconCalendar,
    IconEdit,
    IconTrophy,
    IconUser,
    IconMail,
    IconChevronLeft,
    IconChevronRight,
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
    status_raw: number;
    created_at: string;
    creator: Creator | null;
}

interface User {
    id: number;
    full_name: string;
    username: string;
    email: string;
    image: string | null;
}

interface Joining {
    id: number;
    status: string;
    created_at: string;
    user: User | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedJoinings {
    data: Joining[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface ShowPrizeProps {
    prize: Prize;
    joinings: PaginatedJoinings;
}

export default function ShowPrize({ prize, joinings }: ShowPrizeProps) {
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

    const getStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'active':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Active
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge className="border border-slate-500/20 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Draft
                    </Badge>
                );
            case 'ended':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Ended
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Cancelled
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getJoiningStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'winner':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400 font-bold animate-pulse">
                        🏆 Winner
                    </Badge>
                );
            case 'joined':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Joined
                    </Badge>
                );
            case 'lost':
                return (
                    <Badge className="border border-slate-300 bg-slate-100 text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        Lost
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const isActive = prize.status.toLowerCase() === 'active';
    const hasParticipants = joinings.total > 0;

    return (
        <>
            <Head title={`Prize: ${prize.title}`} />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div className="flex flex-col gap-2">
                        <Link
                            href="/admin/prizes"
                            className="inline-flex w-fit items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground"
                        >
                            <IconArrowLeft className="size-3.5" />
                            <span>Back to prizes</span>
                        </Link>
                        <div className="flex items-center gap-3">
                            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                                Prize details
                            </h1>
                            {getStatusBadge(prize.status)}
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                        >
                            <Link href={`/admin/prizes/${prize.id}/edit`}>
                                <IconEdit className="size-4 mr-1.5" />
                                <span>Edit Prize</span>
                            </Link>
                        </Button>
                        {isActive && hasParticipants && (
                            <Button
                                size="sm"
                                className="bg-indigo-600 text-white hover:bg-indigo-500 font-bold shadow-md"
                                asChild
                            >
                                <Link href={`/admin/prizes/${prize.id}/pick-winner`}>
                                    <IconTrophy className="size-4 mr-1.5" />
                                    <span>Draw Winner Room</span>
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                {/* Prize Banner & Meta Grid */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Banner card */}
                    <div className="overflow-hidden rounded-xl border bg-card shadow-xs lg:col-span-2">
                        <div className="h-64 bg-muted flex items-center justify-center border-b">
                            {prize.image ? (
                                <img
                                    src={prize.image}
                                    alt={prize.title}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex flex-col items-center gap-2">
                                    <IconTrophy className="size-12 text-muted-foreground/40" />
                                    <span className="text-sm font-semibold text-muted-foreground">No banner image uploaded</span>
                                </div>
                            )}
                        </div>
                        <div className="p-6">
                            <h2 className="text-xl font-bold text-foreground mb-3">{prize.title}</h2>
                            <p className="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">
                                {prize.description || 'No description provided.'}
                            </p>
                        </div>
                    </div>

                    {/* Metadata Card */}
                    <div className="flex flex-col gap-4 rounded-xl border bg-card p-6 shadow-xs">
                        <h3 className="text-sm font-bold tracking-wider text-muted-foreground uppercase mb-2">Campaign Meta</h3>
                        
                        <div className="flex flex-col gap-4 text-sm">
                            <div className="flex flex-col gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">Starts At</span>
                                <div className="flex items-center gap-2 font-medium text-foreground">
                                    <IconCalendar className="size-4 text-muted-foreground" />
                                    <span>{formatDate(prize.starts_at)}</span>
                                </div>
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">Ends At</span>
                                <div className="flex items-center gap-2 font-medium text-foreground">
                                    <IconCalendar className="size-4 text-muted-foreground" />
                                    <span>{formatDate(prize.ends_at)}</span>
                                </div>
                            </div>

                            <hr className="border-muted/50 my-1" />

                            <div className="flex flex-col gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">Created By</span>
                                <div className="flex items-center gap-2 font-medium text-foreground">
                                    <IconUser className="size-4 text-muted-foreground" />
                                    <div className="flex flex-col">
                                        <span>{prize.creator?.full_name || 'System'}</span>
                                        <span className="text-xs text-muted-foreground">
                                            {prize.creator ? `@${prize.creator.username}` : ''}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <span className="text-xs font-semibold text-muted-foreground">Total Enrolled Participants</span>
                                <div className="text-2xl font-black text-foreground">
                                    {joinings.total}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Participants List */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    <div className="border-b bg-muted/20 px-6 py-4">
                        <h3 className="font-bold text-foreground">Enrolled Participants</h3>
                        <p className="text-xs text-muted-foreground mt-0.5">List of users who entered this promotional campaign.</p>
                    </div>

                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="border-b bg-muted/15">
                                <TableRow>
                                    <TableHead className="w-[60px] py-4 pl-6">Avatar</TableHead>
                                    <TableHead className="py-4">User Details</TableHead>
                                    <TableHead className="py-4">Contact</TableHead>
                                    <TableHead className="py-4">Entry Date</TableHead>
                                    <TableHead className="py-4 pr-6 text-right">Result status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {joinings.data.length > 0 ? (
                                    joinings.data.map((joining) => (
                                        <TableRow
                                            key={joining.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted/10 text-xs font-bold text-primary shadow-inner">
                                                    {joining.user?.image ? (
                                                        <img
                                                            src={joining.user.image}
                                                            alt={joining.user.full_name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        joining.user?.full_name?.charAt(0).toUpperCase()
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-foreground">
                                                        {joining.user?.full_name || 'Deleted User'}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {joining.user ? `@${joining.user.username}` : ''}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                                    <IconMail className="size-3.5" />
                                                    <span>{joining.user?.email || 'N/A'}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                {formatDate(joining.created_at)}
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                {getJoiningStatusBadge(joining.status)}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconUser className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No participants yet
                                                    </p>
                                                    <p className="text-xs">
                                                        Users haven't enrolled or registered for this campaign.
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
                    {joinings.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {joinings.from} to {joinings.to} of {joinings.total} entries
                            </span>

                            <div className="flex items-center gap-1.5">
                                {joinings.links.map((link, idx) => {
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
