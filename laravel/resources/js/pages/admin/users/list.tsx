import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowUserController from '@/actions/App/Http/Controllers/Admin/Users/ShowUserController';
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
import { Checkbox } from '@/components/ui/checkbox';
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
    IconFolder,
    IconChevronLeft,
    IconChevronRight,
    IconCheck,
    IconLoader2,
    IconMail,
    IconPhone,
    IconLock,
    IconShield,
    IconShoppingBag,
} from '@tabler/icons-react';

interface Role {
    id: number;
    code: string;
    en: string;
}

interface User {
    id: number;
    full_name: string;
    username: string;
    email: string;
    phone_number: string;
    password_plaintext: string | null;
    image: string | null;
    is_active: boolean;
    roles: Role[];
    drops_count: number;
    stores_count: number;
    orders_count: number;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedUsers {
    data: User[];
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
    role: string;
    per_page: number;
}

interface UserListProps {
    users: PaginatedUsers;
    filters: FilterProps;
    roles: Role[];
    stats: {
        total: number;
        active: number;
        inactive: number;
        creators: number;
        stores: number;
    };
}

export default function UserList({
    users,
    filters,
    roles,
    stats,
}: UserListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [roleVal, setRoleVal] = React.useState(filters.role || 'all');
    const [perPage, setPerPage] = React.useState(
        filters.per_page?.toString() || '10',
    );

    // Sheet Preview States
    const [selectedUserId, setSelectedUserId] = React.useState<number | null>(
        null,
    );
    const [sheetOpen, setSheetOpen] = React.useState(false);
    const [loadingUser, setLoadingUser] = React.useState(false);
    const [previewUser, setPreviewUser] = React.useState<any>(null);
    const [allRoles, setAllRoles] = React.useState<Role[]>([]);

    // Sheet Status Management Form States
    const [formIsActive, setFormIsActive] = React.useState<boolean>(true);
    const [selectedRoleIds, setSelectedRoleIds] = React.useState<number[]>([]);
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    const [submitSuccess, setSubmitSuccess] = React.useState(false);

    React.useEffect(() => {
        if (selectedUserId && sheetOpen) {
            setLoadingUser(true);
            setPreviewUser(null);
            setSubmitSuccess(false);

            fetch(ShowUserController.show.url(selectedUserId), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    setPreviewUser(data.user);
                    setAllRoles(data.all_roles || []);
                    setFormIsActive(data.user.is_active);
                    setSelectedRoleIds(data.user.roles.map((r: any) => r.id));
                })
                .catch((err) => console.error(err))
                .finally(() => setLoadingUser(false));
        }
    }, [selectedUserId, sheetOpen]);

    const handleRoleToggle = (roleId: number) => {
        setSelectedRoleIds((prev) =>
            prev.includes(roleId)
                ? prev.filter((id) => id !== roleId)
                : [...prev, roleId],
        );
    };

    const handleSheetSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setSubmitSuccess(false);

        router.put(
            ShowUserController.update.url(selectedUserId!),
            {
                is_active: formIsActive,
                role_ids: selectedRoleIds,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setSubmitSuccess(true);
                    // Refetch to show updated details
                    fetch(ShowUserController.show.url(selectedUserId!), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((res) => res.json())
                        .then((data) => {
                            setPreviewUser(data.user);
                        });
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const getRoleBadge = (roleCode: string) => {
        switch (roleCode.toLowerCase()) {
            case 'admin':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Admin
                    </Badge>
                );
            case 'creator':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Creator
                    </Badge>
                );
            case 'store':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Store Owner
                    </Badge>
                );
            case 'user':
                return (
                    <Badge className="border border-slate-500/20 bg-slate-100 text-slate-700 hover:bg-slate-200/80 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Standard User
                    </Badge>
                );
            default:
                return <Badge variant="outline">{roleCode}</Badge>;
        }
    };

    const applyFilters = (
        search = searchTerm,
        role = roleVal,
        limit = perPage,
    ) => {
        router.get(
            '/admin/users',
            {
                search: search || undefined,
                role: role === 'all' ? undefined : role,
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
                applyFilters(searchTerm, roleVal, perPage);
            }
        }, 450);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    const handleRoleFilterChange = (value: string) => {
        setRoleVal(value);
        applyFilters(searchTerm, value, perPage);
    };

    const handlePerPageChange = (value: string) => {
        setPerPage(value);
        applyFilters(searchTerm, roleVal, value);
    };

    const handleClearFilters = () => {
        setSearchTerm('');
        setRoleVal('all');
        setPerPage('10');
        router.get('/admin/users', {}, { preserveState: true, replace: true });
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
            <Head title="Users Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Users
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Audit profiles, manage dynamic database roles, and
                            moderate user accessibility.
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
                            Total Users
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.total}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                            Active
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.active}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-rose-500 uppercase">
                            Suspended
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.inactive}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-indigo-500 uppercase">
                            Creators
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.creators}
                        </span>
                    </div>
                    <div className="flex flex-col justify-between rounded-xl border bg-card p-4 shadow-xs transition-colors hover:bg-card/85">
                        <span className="text-xs font-semibold tracking-wider text-teal-500 uppercase">
                            Store Owners
                        </span>
                        <span className="mt-2 text-2xl font-bold text-foreground">
                            {stats.stores}
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
                                    placeholder="Search name, phone or email..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="h-10 w-full bg-background pl-9"
                                />
                            </div>

                            <div className="w-full sm:w-44">
                                <Select
                                    value={roleVal}
                                    onValueChange={handleRoleFilterChange}
                                >
                                    <SelectTrigger className="h-10 border-input bg-background">
                                        <SelectValue placeholder="All Roles" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Roles
                                        </SelectItem>
                                        {roles.map((role) => (
                                            <SelectItem
                                                key={role.id}
                                                value={role.code}
                                            >
                                                <span className="capitalize">
                                                    {role.en}
                                                </span>
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
                                        Assoc. Counts
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Assigned Roles
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Access Status
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Joined
                                    </TableHead>
                                    <TableHead className="w-[160px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.length > 0 ? (
                                    users.data.map((user) => (
                                        <TableRow
                                            key={user.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6">
                                                <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted/10 text-xs font-bold text-primary shadow-inner">
                                                    {user.image ? (
                                                        <img
                                                            src={user.image}
                                                            alt={user.full_name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        user.full_name
                                                            ?.charAt(0)
                                                            .toUpperCase()
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-foreground">
                                                        {user.full_name}
                                                    </span>
                                                    <span className="mt-0.5 text-xs text-muted-foreground">
                                                        @{user.username}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold">
                                                <div className="flex flex-col justify-center gap-1">
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <IconMail className="size-3.5" />
                                                        <span>
                                                            {user.email ||
                                                                'No email'}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <IconPhone className="size-3.5" />
                                                        <span>
                                                            {user.phone_number ||
                                                                'No phone'}
                                                        </span>
                                                    </div>
                                                    {user.password_plaintext && (
                                                        <div
                                                            className="mt-0.5 flex w-fit items-center gap-1.5 rounded bg-amber-500/10 px-1.5 py-0.5 font-mono text-[10px] font-bold text-amber-600 select-all dark:bg-amber-500/15 dark:text-amber-400"
                                                            title="Auditable Plaintext Password"
                                                        >
                                                            <IconLock className="size-3.5 shrink-0" />
                                                            <span>
                                                                {
                                                                    user.password_plaintext
                                                                }
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex items-center gap-3.5 text-xs font-bold text-foreground">
                                                    <div className="flex flex-col">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Stores
                                                        </span>
                                                        <span>
                                                            {user.stores_count}
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-col">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Drops
                                                        </span>
                                                        <span>
                                                            {user.drops_count}
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-col">
                                                        <span className="text-[10px] font-semibold text-muted-foreground">
                                                            Orders
                                                        </span>
                                                        <span>
                                                            {user.orders_count}
                                                        </span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                <div className="flex max-w-[200px] flex-wrap gap-1">
                                                    {user.roles.length > 0 ? (
                                                        user.roles.map((r) => (
                                                            <React.Fragment
                                                                key={r.id}
                                                            >
                                                                {getRoleBadge(
                                                                    r.code,
                                                                )}
                                                            </React.Fragment>
                                                        ))
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">
                                                            None
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="py-4">
                                                {user.is_active ? (
                                                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                        Active
                                                    </Badge>
                                                ) : (
                                                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                                                        Suspended
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <IconCalendar className="size-3.5" />
                                                    <span>
                                                        {formatDate(
                                                            user.created_at,
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
                                                            setSelectedUserId(
                                                                user.id,
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
                                                            href={ShowUserController.show.url(
                                                                user.id,
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
                                            colSpan={8}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconUser className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No users found
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
                    {users.total > 0 && (
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {users.from} to {users.to} of{' '}
                                {users.total} users
                            </span>

                            <div className="flex items-center gap-1.5">
                                {users.links.map((link, idx) => {
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

            {/* Dynamic User Management Sheet */}
            <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                <SheetContent
                    side="right"
                    className="flex w-full flex-col overflow-y-auto border-l bg-card p-0 shadow-xl sm:max-w-xl"
                >
                    <SheetHeader className="border-b border-muted/50 bg-muted/10 p-6">
                        <div className="flex items-center justify-between gap-3">
                            <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                                Manage User Roles #{selectedUserId}
                            </SheetTitle>
                            {previewUser &&
                                (previewUser.is_active ? (
                                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700">
                                        Active
                                    </Badge>
                                ) : (
                                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700">
                                        Suspended
                                    </Badge>
                                ))}
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Review stats, profiles, and toggle active status &
                            assign/revoke user roles.
                        </SheetDescription>
                    </SheetHeader>

                    {loadingUser ? (
                        <div className="flex flex-col items-center justify-center gap-3 py-16">
                            <IconLoader2 className="size-8 animate-spin text-primary" />
                            <span className="text-xs font-semibold text-muted-foreground">
                                Loading profile information...
                            </span>
                        </div>
                    ) : previewUser ? (
                        <div className="flex flex-col gap-5 p-4">
                            {/* User Profile Overview */}
                            <div className="flex flex-col gap-3.5 rounded-xl border bg-muted/10 p-4 text-sm">
                                <div className="mb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    User Overview
                                </div>
                                <div className="flex items-center gap-4">
                                    <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted/15 text-lg font-bold text-primary shadow-sm">
                                        {previewUser.image ? (
                                            <img
                                                src={previewUser.image}
                                                alt={previewUser.full_name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            previewUser.full_name
                                                ?.charAt(0)
                                                .toUpperCase()
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-sm font-bold text-foreground">
                                            {previewUser.full_name}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            @{previewUser.username}
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
                                            {previewUser.email || 'None'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Phone Number
                                        </span>
                                        <span className="text-foreground">
                                            {previewUser.phone_number || 'None'}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Registration Date
                                        </span>
                                        <span className="text-foreground">
                                            {formatDate(previewUser.created_at)}
                                        </span>
                                    </div>
                                    {previewUser.password_plaintext && (
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                Plaintext Password
                                            </span>
                                            <span className="w-fit rounded bg-amber-500/10 px-1.5 py-0.5 font-mono font-bold text-amber-600 select-all dark:bg-amber-500/15 dark:text-amber-400">
                                                {previewUser.password_plaintext}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Connected Stores Section */}
                            {previewUser.stores &&
                                previewUser.stores.length > 0 && (
                                    <div className="flex flex-col gap-2.5 rounded-xl border bg-card p-4">
                                        <div className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                            <IconShoppingBag className="size-4 text-emerald-600 dark:text-emerald-400" />
                                            <span>
                                                Connected Stores (
                                                {previewUser.stores.length})
                                            </span>
                                        </div>
                                        <div className="flex max-h-[140px] flex-col gap-2 overflow-y-auto pr-1">
                                            {previewUser.stores.map(
                                                (st: any) => (
                                                    <div
                                                        key={st.id}
                                                        className="flex items-center justify-between rounded-lg border border-muted bg-muted/20 p-2 text-xs font-bold text-foreground"
                                                    >
                                                        <span>{st.name}</span>
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

                            {/* Roles Updater Form */}
                            <form
                                onSubmit={handleSheetSubmit}
                                className="flex flex-col gap-5 rounded-xl border bg-card p-4 shadow-xs"
                            >
                                <div className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    <IconShield className="size-4 text-primary" />
                                    <span>Roles & Access Control</span>
                                </div>

                                {/* Accessibility Toggle */}
                                <div className="flex items-center justify-between rounded-lg border bg-muted/5 p-3">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs font-bold text-foreground">
                                            Account Access State
                                        </span>
                                        <span className="text-[10px] text-muted-foreground">
                                            Toggle suspension status
                                        </span>
                                    </div>
                                    <Select
                                        value={formIsActive ? '1' : '0'}
                                        onValueChange={(val) =>
                                            setFormIsActive(val === '1')
                                        }
                                    >
                                        <SelectTrigger className="h-9 w-28 bg-background">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">
                                                Active
                                            </SelectItem>
                                            <SelectItem value="0">
                                                Suspended
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Multi-role selection checklist */}
                                <div className="flex flex-col gap-2 border-t pt-3">
                                    <label className="text-xs font-bold text-muted-foreground">
                                        Select User Roles
                                    </label>
                                    <div className="mt-1.5 flex flex-col gap-2.5">
                                        {allRoles.map((role) => {
                                            const isChecked =
                                                selectedRoleIds.includes(
                                                    role.id,
                                                );
                                            return (
                                                <button
                                                    type="button"
                                                    key={role.id}
                                                    onClick={() =>
                                                        handleRoleToggle(
                                                            role.id,
                                                        )
                                                    }
                                                    className={`flex items-center justify-between rounded-xl border p-3 text-left transition-all ${
                                                        isChecked
                                                            ? 'border-primary bg-primary/5 ring-1 ring-primary/10'
                                                            : 'border-muted bg-muted/5 hover:border-foreground/30'
                                                    }`}
                                                >
                                                    <div className="flex flex-col gap-0.5">
                                                        <span className="text-xs font-bold text-foreground capitalize">
                                                            {role.en}
                                                        </span>
                                                        <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                                            Code: {role.code}
                                                        </span>
                                                    </div>
                                                    <div
                                                        className={`flex size-5 items-center justify-center rounded-full border transition-all ${
                                                            isChecked
                                                                ? 'border-primary bg-primary text-primary-foreground'
                                                                : 'border-muted-foreground/35 bg-background'
                                                        }`}
                                                    >
                                                        {isChecked && (
                                                            <IconCheck className="size-3.5 stroke-[2.5]" />
                                                        )}
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="mt-1 h-10 w-full"
                                >
                                    {isSubmitting
                                        ? 'Syncing accounts...'
                                        : 'Commit Changes'}
                                </Button>

                                {submitSuccess && (
                                    <div className="flex items-center justify-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <IconCheck className="size-4" />
                                        <span>
                                            User settings and roles synced
                                            successfully
                                        </span>
                                    </div>
                                )}
                            </form>

                            {/* Show Full Details Button */}
                            <Link
                                href={ShowUserController.show.url(
                                    previewUser.id,
                                )}
                            >
                                <Button
                                    type="button"
                                    className="mt-1 h-10 w-full"
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
