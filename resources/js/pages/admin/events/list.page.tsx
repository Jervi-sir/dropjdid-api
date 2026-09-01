import { Head, router } from '@inertiajs/react';
import {
    Calendar,
    CalendarCheck,
    CalendarClock,
    CheckCircle2,
    Clock,
    ExternalLink,
    Filter,
    Image as ImageIcon,
    MapPin,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Trash2,
    Users,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
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
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface EventMeta {
    location?: string;
    city?: string;
    badge?: string;
    cta_text?: string;
    organizer?: string;
    capacity?: number | string;
    highlights?: string[];
    [key: string]: any;
}

interface EventItem {
    id: number;
    user_id: number | null;
    title: string;
    description: string | null;
    image_url: string | null;
    url: string | null;
    status: 'draft' | 'active' | 'inactive' | 'completed' | string;
    sort_order: number;
    starts_at: string | null;
    ends_at: string | null;
    meta: EventMeta | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        full_name?: string | null;
        username?: string | null;
        email?: string;
    } | null;
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
    events: PaginatedData<EventItem>;
    filters: {
        status: string;
        search: string;
    };
    counts: {
        all: number;
        active: number;
        upcoming: number;
        past: number;
        draft: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Events',
        href: '/admin/events',
    },
];

export default function AdminEventsListPage({
    events,
    filters,
    counts,
}: Props) {
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [currentStatus, setCurrentStatus] = useState(filters.status || 'all');

    // Dialog state
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editingEvent, setEditingEvent] = useState<EventItem | null>(null);
    const [deletingEvent, setDeletingEvent] = useState<EventItem | null>(null);

    // Form state for Create / Edit
    const [formState, setFormState] = useState({
        title: '',
        description: '',
        image_url: '',
        url: '',
        status: 'active',
        sort_order: 0,
        starts_at: '',
        ends_at: '',
        location: '',
        city: '',
        badge: '',
        cta_text: 'Get Tickets',
        organizer: '',
        capacity: '',
    });

    const [isSubmitting, setIsSubmitting] = useState(false);

    const openCreateDialog = () => {
        setEditingEvent(null);
        setFormState({
            title: '',
            description: '',
            image_url: '',
            url: '',
            status: 'active',
            sort_order: 0,
            starts_at: new Date().toISOString().slice(0, 16),
            ends_at: new Date(Date.now() + 14 * 86400000)
                .toISOString()
                .slice(0, 16),
            location: '',
            city: '',
            badge: '',
            cta_text: 'Get Tickets',
            organizer: 'DropJdid Official',
            capacity: '',
        });
        setIsCreateOpen(true);
    };

    const openEditDialog = (event: EventItem) => {
        setEditingEvent(event);
        setFormState({
            title: event.title || '',
            description: event.description || '',
            image_url: event.image_url || '',
            url: event.url || '',
            status: event.status || 'active',
            sort_order: event.sort_order ?? 0,
            starts_at: event.starts_at
                ? new Date(event.starts_at).toISOString().slice(0, 16)
                : '',
            ends_at: event.ends_at
                ? new Date(event.ends_at).toISOString().slice(0, 16)
                : '',
            location: event.meta?.location || '',
            city: event.meta?.city || '',
            badge: event.meta?.badge || '',
            cta_text: event.meta?.cta_text || '',
            organizer: event.meta?.organizer || '',
            capacity: event.meta?.capacity ? String(event.meta.capacity) : '',
        });
        setIsCreateOpen(true);
    };

    const handleFilterChange = (newStatus: string) => {
        setCurrentStatus(newStatus);
        router.get(
            '/admin/events',
            { status: newStatus, search: searchQuery },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/admin/events',
            { status: currentStatus, search: searchQuery },
            { preserveState: true, replace: true },
        );
    };

    const handleClearSearch = () => {
        setSearchQuery('');
        router.get(
            '/admin/events',
            { status: currentStatus, search: '' },
            { preserveState: true, replace: true },
        );
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const payload = {
            title: formState.title,
            description: formState.description || null,
            image_url: formState.image_url || null,
            url: formState.url || null,
            status: formState.status,
            sort_order: Number(formState.sort_order) || 0,
            starts_at: formState.starts_at
                ? new Date(formState.starts_at).toISOString()
                : null,
            ends_at: formState.ends_at
                ? new Date(formState.ends_at).toISOString()
                : null,
            meta: {
                location: formState.location || null,
                city: formState.city || null,
                badge: formState.badge || null,
                cta_text: formState.cta_text || null,
                organizer: formState.organizer || null,
                capacity: formState.capacity
                    ? Number(formState.capacity)
                    : null,
            },
        };

        if (editingEvent) {
            router.put(`/admin/events/${editingEvent.id}`, payload, {
                onSuccess: () => {
                    setIsCreateOpen(false);
                    setEditingEvent(null);
                },
                onFinish: () => setIsSubmitting(false),
            });
        } else {
            router.post('/admin/events', payload, {
                onSuccess: () => {
                    setIsCreateOpen(false);
                },
                onFinish: () => setIsSubmitting(false),
            });
        }
    };

    const handleDelete = () => {
        if (!deletingEvent) return;
        setIsSubmitting(true);
        router.delete(`/admin/events/${deletingEvent.id}`, {
            onSuccess: () => setDeletingEvent(null),
            onFinish: () => setIsSubmitting(false),
        });
    };

    const handleToggleStatus = (event: EventItem) => {
        router.post(
            `/admin/events/${event.id}/toggle-status`,
            {},
            { preserveScroll: true },
        );
    };

    const formatDateRange = (
        startsAt: string | null,
        endsAt: string | null,
    ) => {
        if (!startsAt && !endsAt) return 'No period specified';
        const start = startsAt
            ? new Date(startsAt).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                  year: 'numeric',
              })
            : 'Anytime';
        const end = endsAt
            ? new Date(endsAt).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                  year: 'numeric',
              })
            : 'Ongoing';
        return `${start} — ${end}`;
    };

    const getStatusBadge = (event: EventItem) => {
        const now = new Date();
        const start = event.starts_at ? new Date(event.starts_at) : null;
        const end = event.ends_at ? new Date(event.ends_at) : null;

        if (event.status === 'draft') {
            return (
                <Badge
                    variant="secondary"
                    className="border-amber-200 bg-amber-100 text-amber-800"
                >
                    Draft
                </Badge>
            );
        }
        if (event.status === 'inactive') {
            return (
                <Badge
                    variant="outline"
                    className="bg-slate-100 text-slate-700"
                >
                    Inactive
                </Badge>
            );
        }
        if (end && end < now) {
            return (
                <Badge
                    variant="secondary"
                    className="bg-zinc-100 text-zinc-600"
                >
                    Ended
                </Badge>
            );
        }
        if (start && start > now) {
            return (
                <Badge
                    variant="secondary"
                    className="border-blue-200 bg-blue-100 text-blue-700"
                >
                    Upcoming
                </Badge>
            );
        }
        return (
            <Badge className="bg-emerald-600 text-white hover:bg-emerald-700">
                Active Now
            </Badge>
        );
    };

    return (
        <>
            <Head title="Events Management - Admin" />

            <div className="flex flex-1 flex-col gap-6 p-6">
                {/* Header with Title and Create Button */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground">
                            <Sparkles className="h-6 w-6 text-pink-600" />
                            Events & Campaigns
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage special drops, launch parties, masterclasses,
                            and featured seasonal events.
                        </p>
                    </div>
                    <Button
                        onClick={openCreateDialog}
                        className="gap-2 bg-pink-600 text-white shadow-sm hover:bg-pink-700"
                    >
                        <Plus className="h-4 w-4" />
                        Create Event
                    </Button>
                </div>

                {/* Metrics Stat Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card
                        className={`cursor-pointer transition-all hover:border-pink-300 ${currentStatus === 'all' ? 'bg-pink-50/20 ring-2 ring-pink-500' : ''}`}
                        onClick={() => handleFilterChange('all')}
                    >
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Events
                            </CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {counts.all}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                All events in database
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        className={`cursor-pointer transition-all hover:border-emerald-300 ${currentStatus === 'active' ? 'bg-emerald-50/20 ring-2 ring-emerald-500' : ''}`}
                        onClick={() => handleFilterChange('active')}
                    >
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                Active Now
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {counts.active}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Live and visible on app
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        className={`cursor-pointer transition-all hover:border-blue-300 ${currentStatus === 'upcoming' ? 'bg-blue-50/20 ring-2 ring-blue-500' : ''}`}
                        onClick={() => handleFilterChange('upcoming')}
                    >
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                Upcoming
                            </CardTitle>
                            <CalendarClock className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {counts.upcoming}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Scheduled for future date
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        className={`cursor-pointer transition-all hover:border-amber-300 ${currentStatus === 'draft' ? 'bg-amber-50/20 ring-2 ring-amber-500' : ''}`}
                        onClick={() => handleFilterChange('draft')}
                    >
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                Drafts
                            </CardTitle>
                            <Clock className="h-4 w-4 text-amber-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600">
                                {counts.draft}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Unpublished events
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters and Search Bar */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            {/* Filter Pills */}
                            <div className="flex flex-wrap gap-2">
                                {[
                                    {
                                        id: 'all',
                                        label: 'All',
                                        count: counts.all,
                                    },
                                    {
                                        id: 'active',
                                        label: 'Active',
                                        count: counts.active,
                                    },
                                    {
                                        id: 'upcoming',
                                        label: 'Upcoming',
                                        count: counts.upcoming,
                                    },
                                    {
                                        id: 'past',
                                        label: 'Past',
                                        count: counts.past,
                                    },
                                    {
                                        id: 'draft',
                                        label: 'Drafts',
                                        count: counts.draft,
                                    },
                                ].map((tab) => (
                                    <Button
                                        key={tab.id}
                                        variant={
                                            currentStatus === tab.id
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        onClick={() =>
                                            handleFilterChange(tab.id)
                                        }
                                        className={
                                            currentStatus === tab.id
                                                ? 'bg-pink-600 text-white hover:bg-pink-700'
                                                : ''
                                        }
                                    >
                                        {tab.label} ({tab.count})
                                    </Button>
                                ))}
                            </div>

                            {/* Search Bar */}
                            <form
                                onSubmit={handleSearchSubmit}
                                className="flex items-center gap-2"
                            >
                                <div className="relative w-full md:w-72">
                                    <Search className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search title, city, organizer..."
                                        className="pr-8 pl-9"
                                        value={searchQuery}
                                        onChange={(e) =>
                                            setSearchQuery(e.target.value)
                                        }
                                    />
                                    {searchQuery && (
                                        <button
                                            type="button"
                                            onClick={handleClearSearch}
                                            className="absolute top-2.5 right-2.5 text-muted-foreground hover:text-foreground"
                                        >
                                            <X className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                                <Button
                                    type="submit"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Search
                                </Button>
                            </form>
                        </div>
                    </CardContent>
                </Card>

                {/* Events Grid */}
                {events.data.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center p-12 text-center">
                        <div className="rounded-full bg-pink-100 p-4 text-pink-600 dark:bg-pink-900/30">
                            <Calendar className="h-8 w-8" />
                        </div>
                        <h3 className="mt-4 text-lg font-semibold">
                            No events found
                        </h3>
                        <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                            {searchQuery
                                ? `No events matched your search query "${searchQuery}".`
                                : 'There are currently no events under this filter. Create one to get started!'}
                        </p>
                        <Button
                            onClick={openCreateDialog}
                            className="mt-4 bg-pink-600 text-white hover:bg-pink-700"
                        >
                            <Plus className="mr-2 h-4 w-4" /> Create New Event
                        </Button>
                    </Card>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {events.data.map((event) => (
                            <Card
                                key={event.id}
                                className="group flex flex-col overflow-hidden border shadow-sm transition-all hover:shadow-md"
                            >
                                {/* Cover Image Container */}
                                <div className="relative aspect-[16/9] w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    {event.image_url ? (
                                        <img
                                            src={event.image_url}
                                            alt={event.title}
                                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            onError={(e) => {
                                                (
                                                    e.target as HTMLElement
                                                ).style.display = 'none';
                                            }}
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center bg-gradient-to-r from-pink-500 to-purple-600 text-white">
                                            <ImageIcon className="h-10 w-10 opacity-60" />
                                        </div>
                                    )}

                                    {/* Top Status & Badge overlay */}
                                    <div className="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                                        {getStatusBadge(event)}
                                        {event.meta?.badge && (
                                            <Badge
                                                variant="outline"
                                                className="border-transparent bg-black/60 text-white backdrop-blur-md"
                                            >
                                                {event.meta.badge}
                                            </Badge>
                                        )}
                                    </div>

                                    {/* Quick action menu */}
                                    <div className="absolute top-2.5 right-2.5">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    size="icon"
                                                    variant="secondary"
                                                    className="h-8 w-8 rounded-full bg-white/90 shadow-sm backdrop-blur-sm dark:bg-slate-900/90"
                                                >
                                                    <MoreVertical className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem
                                                    onClick={() =>
                                                        openEditDialog(event)
                                                    }
                                                    className="cursor-pointer gap-2"
                                                >
                                                    <Pencil className="h-4 w-4 text-blue-600" />
                                                    Edit Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onClick={() =>
                                                        handleToggleStatus(
                                                            event,
                                                        )
                                                    }
                                                    className="cursor-pointer gap-2"
                                                >
                                                    {event.status ===
                                                    'active' ? (
                                                        <>
                                                            <XCircle className="h-4 w-4 text-amber-600" />
                                                            Deactivate
                                                        </>
                                                    ) : (
                                                        <>
                                                            <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                                                            Activate
                                                        </>
                                                    )}
                                                </DropdownMenuItem>
                                                {event.url && (
                                                    <DropdownMenuItem
                                                        onClick={() =>
                                                            window.open(
                                                                event.url!,
                                                                '_blank',
                                                            )
                                                        }
                                                        className="cursor-pointer gap-2"
                                                    >
                                                        <ExternalLink className="h-4 w-4 text-slate-600" />
                                                        Visit URL
                                                    </DropdownMenuItem>
                                                )}
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    onClick={() =>
                                                        setDeletingEvent(event)
                                                    }
                                                    className="cursor-pointer gap-2 text-red-600"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                    Delete Event
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>

                                {/* Content Details */}
                                <CardContent className="flex flex-1 flex-col justify-between gap-3 p-4">
                                    <div className="space-y-1.5">
                                        <h3 className="line-clamp-1 text-base leading-snug font-semibold">
                                            {event.title}
                                        </h3>
                                        <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                                            {event.description ||
                                                'No description provided.'}
                                        </p>
                                    </div>

                                    {/* Meta specs */}
                                    <div className="space-y-2 border-t pt-2 text-xs text-muted-foreground">
                                        <div className="flex items-center gap-1.5">
                                            <Calendar className="h-3.5 w-3.5 shrink-0 text-pink-600" />
                                            <span className="truncate">
                                                {formatDateRange(
                                                    event.starts_at,
                                                    event.ends_at,
                                                )}
                                            </span>
                                        </div>

                                        {(event.meta?.location ||
                                            event.meta?.city) && (
                                            <div className="flex items-center gap-1.5">
                                                <MapPin className="h-3.5 w-3.5 shrink-0 text-blue-600" />
                                                <span className="truncate">
                                                    {event.meta.location ||
                                                        event.meta.city}
                                                </span>
                                            </div>
                                        )}

                                        {event.meta?.organizer && (
                                            <div className="flex items-center gap-1.5">
                                                <Users className="h-3.5 w-3.5 shrink-0 text-emerald-600" />
                                                <span className="truncate">
                                                    By {event.meta.organizer}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Card Footer Actions */}
                                    <div className="mt-auto flex items-center justify-between gap-2 border-t pt-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                openEditDialog(event)
                                            }
                                            className="flex-1 gap-1.5 text-xs"
                                        >
                                            <Pencil className="h-3.5 w-3.5" />{' '}
                                            Edit
                                        </Button>

                                        {event.url ? (
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                onClick={() =>
                                                    window.open(
                                                        event.url!,
                                                        '_blank',
                                                    )
                                                }
                                                className="gap-1 px-2.5 text-xs"
                                            >
                                                <ExternalLink className="h-3.5 w-3.5" />{' '}
                                                Link
                                            </Button>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {events.links && events.links.length > 3 && (
                    <div className="flex items-center justify-between border-t pt-4">
                        <div className="text-sm text-muted-foreground">
                            Showing{' '}
                            <span className="font-medium">
                                {events.from || 0}
                            </span>{' '}
                            to{' '}
                            <span className="font-medium">
                                {events.to || 0}
                            </span>{' '}
                            of{' '}
                            <span className="font-medium">{events.total}</span>{' '}
                            events
                        </div>
                        <div className="flex items-center gap-1">
                            {events.links.map((link, idx) => (
                                <Button
                                    key={idx}
                                    variant={
                                        link.active ? 'default' : 'outline'
                                    }
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() =>
                                        link.url &&
                                        router.get(
                                            link.url,
                                            {},
                                            { preserveState: true },
                                        )
                                    }
                                    className={
                                        link.active
                                            ? 'bg-pink-600 text-white hover:bg-pink-700'
                                            : ''
                                    }
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Create / Edit Event Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>
                            {editingEvent ? 'Edit Event' : 'Create New Event'}
                        </DialogTitle>
                        <DialogDescription>
                            Configure the event banner, active period,
                            description, and metadata tags.
                        </DialogDescription>
                    </DialogHeader>

                    <form
                        onSubmit={handleFormSubmit}
                        className="space-y-4 pt-2"
                    >
                        {/* Title & Status */}
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="title">
                                    Event Title (text1) *
                                </Label>
                                <Input
                                    id="title"
                                    placeholder="e.g. Summer Streetwear Pop-Up 2026"
                                    value={formState.title}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            title: e.target.value,
                                        })
                                    }
                                    required
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    value={formState.status}
                                    onValueChange={(val) =>
                                        setFormState({
                                            ...formState,
                                            status: val,
                                        })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            Active
                                        </SelectItem>
                                        <SelectItem value="draft">
                                            Draft
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            Inactive
                                        </SelectItem>
                                        <SelectItem value="completed">
                                            Completed
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Description (text2) */}
                        <div className="space-y-1.5">
                            <Label htmlFor="description">
                                Description / Subtitle (text2)
                            </Label>
                            <textarea
                                id="description"
                                rows={2}
                                className="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                placeholder="Short catchy description or venue details..."
                                value={formState.description}
                                onChange={(e) =>
                                    setFormState({
                                        ...formState,
                                        description: e.target.value,
                                    })
                                }
                            />
                        </div>

                        {/* Image URL with live preview */}
                        <div className="space-y-1.5">
                            <Label htmlFor="image_url">Cover Image URL</Label>
                            <div className="flex gap-2">
                                <Input
                                    id="image_url"
                                    placeholder="https://images.unsplash.com/..."
                                    value={formState.image_url}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            image_url: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            {formState.image_url ? (
                                <div className="relative mt-2 h-32 w-full overflow-hidden rounded-lg border bg-slate-100">
                                    <img
                                        src={formState.image_url}
                                        alt="Preview"
                                        className="h-full w-full object-cover"
                                        onError={(e) => {
                                            (
                                                e.target as HTMLElement
                                            ).style.display = 'none';
                                        }}
                                    />
                                </div>
                            ) : null}
                        </div>

                        {/* Event Link / URL & Sort Order */}
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="space-y-1.5 md:col-span-2">
                                <Label htmlFor="url">Action / Ticket URL</Label>
                                <Input
                                    id="url"
                                    placeholder="https://dropjdid.com/events/..."
                                    value={formState.url}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            url: e.target.value,
                                        })
                                    }
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="sort_order">
                                    Sort Priority
                                </Label>
                                <Input
                                    id="sort_order"
                                    type="number"
                                    value={formState.sort_order}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            sort_order: Number(e.target.value),
                                        })
                                    }
                                />
                            </div>
                        </div>

                        {/* Period: Starts At & Ends At */}
                        <div className="grid grid-cols-1 gap-4 rounded-lg border bg-slate-50 p-3.5 md:grid-cols-2 dark:bg-slate-900/50">
                            <div className="space-y-1.5">
                                <Label
                                    htmlFor="starts_at"
                                    className="flex items-center gap-1.5 text-xs font-semibold"
                                >
                                    <Calendar className="h-3.5 w-3.5 text-pink-600" />{' '}
                                    Start Date & Time
                                </Label>
                                <Input
                                    id="starts_at"
                                    type="datetime-local"
                                    value={formState.starts_at}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            starts_at: e.target.value,
                                        })
                                    }
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label
                                    htmlFor="ends_at"
                                    className="flex items-center gap-1.5 text-xs font-semibold"
                                >
                                    <Clock className="h-3.5 w-3.5 text-pink-600" />{' '}
                                    End Date & Time
                                </Label>
                                <Input
                                    id="ends_at"
                                    type="datetime-local"
                                    value={formState.ends_at}
                                    onChange={(e) =>
                                        setFormState({
                                            ...formState,
                                            ends_at: e.target.value,
                                        })
                                    }
                                />
                            </div>
                        </div>

                        {/* Metadata fields */}
                        <div className="space-y-3 pt-2">
                            <Label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Metadata & Venue Information
                            </Label>
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                                <div className="space-y-1">
                                    <Label htmlFor="city" className="text-xs">
                                        City
                                    </Label>
                                    <Input
                                        id="city"
                                        placeholder="e.g. Algiers, Oran"
                                        value={formState.city}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                city: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label
                                        htmlFor="location"
                                        className="text-xs"
                                    >
                                        Venue Location
                                    </Label>
                                    <Input
                                        id="location"
                                        placeholder="e.g. Safex Exhibition"
                                        value={formState.location}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                location: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label
                                        htmlFor="organizer"
                                        className="text-xs"
                                    >
                                        Organizer
                                    </Label>
                                    <Input
                                        id="organizer"
                                        placeholder="e.g. DropJdid Official"
                                        value={formState.organizer}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                organizer: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label htmlFor="badge" className="text-xs">
                                        Badge Tag
                                    </Label>
                                    <Input
                                        id="badge"
                                        placeholder="e.g. Exclusive, Masterclass"
                                        value={formState.badge}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                badge: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label
                                        htmlFor="cta_text"
                                        className="text-xs"
                                    >
                                        Button CTA Text
                                    </Label>
                                    <Input
                                        id="cta_text"
                                        placeholder="e.g. Get Tickets, RSVP"
                                        value={formState.cta_text}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                cta_text: e.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label
                                        htmlFor="capacity"
                                        className="text-xs"
                                    >
                                        Max Capacity
                                    </Label>
                                    <Input
                                        id="capacity"
                                        type="number"
                                        placeholder="e.g. 1500"
                                        value={formState.capacity}
                                        onChange={(e) =>
                                            setFormState({
                                                ...formState,
                                                capacity: e.target.value,
                                            })
                                        }
                                    />
                                </div>
                            </div>
                        </div>

                        <DialogFooter className="border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsCreateOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={isSubmitting}
                                className="bg-pink-600 text-white hover:bg-pink-700"
                            >
                                {isSubmitting
                                    ? 'Saving...'
                                    : editingEvent
                                      ? 'Update Event'
                                      : 'Create Event'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog
                open={!!deletingEvent}
                onOpenChange={(open) => !open && setDeletingEvent(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Event</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete the event &quot;
                            {deletingEvent?.title}&quot;? This action cannot be
                            undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeletingEvent(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Deleting...' : 'Delete'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
