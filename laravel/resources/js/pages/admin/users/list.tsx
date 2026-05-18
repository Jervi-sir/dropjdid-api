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

export default function UserList({ users, filters, roles, stats }: UserListProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [roleVal, setRoleVal] = React.useState(filters.role || 'all');
  const [perPage, setPerPage] = React.useState(filters.per_page?.toString() || '10');

  // Sheet Preview States
  const [selectedUserId, setSelectedUserId] = React.useState<number | null>(null);
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
          'Accept': 'application/json',
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
      prev.includes(roleId) ? prev.filter((id) => id !== roleId) : [...prev, roleId]
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
              'Accept': 'application/json',
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
      }
    );
  };

  const getRoleBadge = (roleCode: string) => {
    switch (roleCode.toLowerCase()) {
      case 'admin':
        return (
          <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
            Admin
          </Badge>
        );
      case 'creator':
        return (
          <Badge className="bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 border-indigo-500/20 border dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30">
            Creator
          </Badge>
        );
      case 'store':
        return (
          <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
            Store Owner
          </Badge>
        );
      case 'user':
        return (
          <Badge className="bg-slate-100 text-slate-700 hover:bg-slate-200/80 border-slate-500/20 border dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
            Standard User
          </Badge>
        );
      default:
        return <Badge variant="outline">{roleCode}</Badge>;
    }
  };

  const applyFilters = (search = searchTerm, role = roleVal, limit = perPage) => {
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
      }
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
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Users</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Audit profiles, manage dynamic database roles, and moderate user accessibility.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" onClick={handleClearFilters}>
              <IconRefresh className="size-4" />
              <span>Refresh</span>
            </Button>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Users</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.total}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Active</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.active}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-rose-500">Suspended</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.inactive}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-indigo-500">Creators</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.creators}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-teal-500">Store Owners</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.stores}</span>
          </div>
        </div>

        {/* Filters and Table Container */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">

          {/* Controls Bar */}
          <div className="p-4 border-b flex flex-col sm:flex-row items-center gap-4 justify-between bg-muted/20">
            <div className="flex flex-1 flex-col sm:flex-row items-center gap-2 w-full">
              <div className="relative w-full sm:max-w-xs">
                <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search name, phone or email..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-9 h-10 w-full bg-background"
                />
              </div>

              <div className="w-full sm:w-44">
                <Select value={roleVal} onValueChange={handleRoleFilterChange}>
                  <SelectTrigger className="h-10 bg-background border-input">
                    <SelectValue placeholder="All Roles" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Roles</SelectItem>
                    {roles.map((role) => (
                      <SelectItem key={role.id} value={role.code}>
                        <span className="capitalize">{role.en}</span>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="flex items-center gap-2 shrink-0">
              <span className="text-xs text-muted-foreground font-semibold">Limit</span>
              <Select value={perPage} onValueChange={handlePerPageChange}>
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
              <TableHeader className="bg-muted/15 border-b">
                <TableRow>
                  <TableHead className="w-[60px] pl-6 py-4">Avatar</TableHead>
                  <TableHead className="min-w-[180px] py-4">User Details</TableHead>
                  <TableHead className="py-4">Contact Info</TableHead>
                  <TableHead className="py-4">Assoc. Counts</TableHead>
                  <TableHead className="py-4">Assigned Roles</TableHead>
                  <TableHead className="py-4">Access Status</TableHead>
                  <TableHead className="py-4">Joined</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[160px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {users.data.length > 0 ? (
                  users.data.map((user) => (
                    <TableRow key={user.id} className="hover:bg-muted/5 group/row transition-colors">
                      <TableCell className="pl-6 py-4">
                        <div className="size-10 rounded-full overflow-hidden border shrink-0 bg-muted/10 flex items-center justify-center font-bold text-xs text-primary shadow-inner">
                          {user.image ? (
                            <img src={user.image} alt={user.full_name} className="w-full h-full object-cover" />
                          ) : (
                            user.full_name?.charAt(0).toUpperCase()
                          )}
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-col">
                          <span className="font-bold text-foreground text-sm">{user.full_name}</span>
                          <span className="text-xs text-muted-foreground mt-0.5">@{user.username}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-xs font-semibold">
                        <div className="flex flex-col gap-1 justify-center">
                          <div className="flex items-center gap-1.5 text-muted-foreground">
                            <IconMail className="size-3.5" />
                            <span>{user.email || 'No email'}</span>
                          </div>
                          <div className="flex items-center gap-1.5 text-muted-foreground">
                            <IconPhone className="size-3.5" />
                            <span>{user.phone_number || 'No phone'}</span>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex items-center gap-3.5 text-xs font-bold text-foreground">
                          <div className="flex flex-col">
                            <span className="text-[10px] text-muted-foreground font-semibold">Stores</span>
                            <span>{user.stores_count}</span>
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[10px] text-muted-foreground font-semibold">Drops</span>
                            <span>{user.drops_count}</span>
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[10px] text-muted-foreground font-semibold">Orders</span>
                            <span>{user.orders_count}</span>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-wrap gap-1 max-w-[200px]">
                          {user.roles.length > 0 ? (
                            user.roles.map((r) => (
                              <React.Fragment key={r.id}>
                                {getRoleBadge(r.code)}
                              </React.Fragment>
                            ))
                          ) : (
                            <span className="text-xs text-muted-foreground">None</span>
                          )}
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {user.is_active ? (
                          <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                            Active
                          </Badge>
                        ) : (
                          <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
                            Suspended
                          </Badge>
                        )}
                      </TableCell>
                      <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconCalendar className="size-3.5" />
                          <span>{formatDate(user.created_at)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-1.5">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5"
                            onClick={() => {
                              setSelectedUserId(user.id);
                              setSheetOpen(true);
                            }}
                          >
                            Manage
                          </Button>
                          <Button variant="ghost" size="xs" className="h-7 px-2 text-muted-foreground hover:text-foreground" asChild>
                            <Link href={ShowUserController.show.url(user.id)}>
                              Details
                            </Link>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={8} className="py-12 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconUser className="size-10 text-muted-foreground/55 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No users found</p>
                          <p className="text-xs">Try adjusting your filters or search query.</p>
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
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {users.from} to {users.to} of {users.total} users
              </span>

              <div className="flex items-center gap-1.5">
                {users.links.map((link, idx) => {
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
                      className={`
                        inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none 
                        ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'}
                        ${link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm' : 'border border-border bg-card text-foreground'}
                      `}
                    >
                      {isPrev && <IconChevronLeft className="size-3.5 -ml-0.5" />}
                      <span>{label}</span>
                      {isNext && <IconChevronRight className="size-3.5 -mr-0.5" />}
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
        <SheetContent side="right" className="w-full sm:max-w-xl overflow-y-auto bg-card border-l shadow-xl flex flex-col p-0">
          <SheetHeader className="p-6 border-b border-muted/50 bg-muted/10">
            <div className="flex items-center justify-between gap-3">
              <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                Manage User Roles #{selectedUserId}
              </SheetTitle>
              {previewUser && (
                previewUser.is_active ? (
                  <Badge className="bg-emerald-50 text-emerald-700 border-emerald-500/20 border">Active</Badge>
                ) : (
                  <Badge className="bg-rose-50 text-rose-700 border-rose-500/20 border">Suspended</Badge>
                )
              )}
            </div>
            <SheetDescription className="text-xs text-muted-foreground">
              Review stats, profiles, and toggle active status & assign/revoke user roles.
            </SheetDescription>
          </SheetHeader>

          {loadingUser ? (
            <div className="flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Loading profile information...</span>
            </div>
          ) : previewUser ? (
            <div className="flex flex-col p-4 gap-5">

              {/* User Profile Overview */}
              <div className="flex flex-col gap-3.5 text-sm border bg-muted/10 p-4 rounded-xl">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  User Overview
                </div>
                <div className="flex items-center gap-4">
                  <div className="size-16 rounded-full overflow-hidden border shrink-0 bg-muted/15 flex items-center justify-center font-bold text-lg text-primary shadow-sm">
                    {previewUser.image ? (
                      <img src={previewUser.image} alt={previewUser.full_name} className="w-full h-full object-cover" />
                    ) : (
                      previewUser.full_name?.charAt(0).toUpperCase()
                    )}
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="font-bold text-foreground text-sm">{previewUser.full_name}</span>
                    <span className="text-xs text-muted-foreground">@{previewUser.username}</span>
                  </div>
                </div>

                <hr className="border-muted/50 my-1" />

                <div className="grid grid-cols-2 gap-4 text-xs font-semibold">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Email Address</span>
                    <span className="text-foreground">{previewUser.email || 'None'}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Phone Number</span>
                    <span className="text-foreground">{previewUser.phone_number || 'None'}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Registration Date</span>
                    <span className="text-foreground">{formatDate(previewUser.created_at)}</span>
                  </div>
                </div>
              </div>

              {/* Connected Stores Section */}
              {previewUser.stores && previewUser.stores.length > 0 && (
                <div className="flex flex-col gap-2.5 border p-4 rounded-xl bg-card">
                  <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                    <IconShoppingBag className="size-4 text-emerald-600 dark:text-emerald-400" />
                    <span>Connected Stores ({previewUser.stores.length})</span>
                  </div>
                  <div className="flex flex-col gap-2 max-h-[140px] overflow-y-auto pr-1">
                    {previewUser.stores.map((st: any) => (
                      <div key={st.id} className="flex items-center justify-between p-2 rounded-lg bg-muted/20 border border-muted text-xs font-bold text-foreground">
                        <span>{st.name}</span>
                        <Badge variant="outline" className="text-[9px]">ID: {st.id}</Badge>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Roles Updater Form */}
              <form onSubmit={handleSheetSubmit} className="flex flex-col gap-5 border p-4 rounded-xl bg-card shadow-xs">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                  <IconShield className="size-4 text-primary" />
                  <span>Roles & Access Control</span>
                </div>

                {/* Accessibility Toggle */}
                <div className="flex items-center justify-between p-3 rounded-lg border bg-muted/5">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-xs font-bold text-foreground">Account Access State</span>
                    <span className="text-[10px] text-muted-foreground">Toggle suspension status</span>
                  </div>
                  <Select
                    value={formIsActive ? '1' : '0'}
                    onValueChange={(val) => setFormIsActive(val === '1')}
                  >
                    <SelectTrigger className="w-28 h-9 bg-background">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="1">Active</SelectItem>
                      <SelectItem value="0">Suspended</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Multi-role selection checklist */}
                <div className="flex flex-col gap-2 border-t pt-3">
                  <label className="text-xs font-bold text-muted-foreground">Select User Roles</label>
                  <div className="flex flex-col gap-2.5 mt-1.5">
                    {allRoles.map((role) => {
                      const isChecked = selectedRoleIds.includes(role.id);
                      return (
                        <button
                          type="button"
                          key={role.id}
                          onClick={() => handleRoleToggle(role.id)}
                          className={`flex items-center justify-between p-3 rounded-xl border text-left transition-all ${isChecked
                            ? 'border-primary bg-primary/5 ring-1 ring-primary/10'
                            : 'border-muted hover:border-foreground/30 bg-muted/5'
                            }`}
                        >
                          <div className="flex flex-col gap-0.5">
                            <span className="text-xs font-bold capitalize text-foreground">{role.en}</span>
                            <span className="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">
                              Code: {role.code}
                            </span>
                          </div>
                          <div
                            className={`size-5 rounded-full border flex items-center justify-center transition-all ${isChecked ? 'bg-primary border-primary text-primary-foreground' : 'border-muted-foreground/35 bg-background'
                              }`}
                          >
                            {isChecked && <IconCheck className="size-3.5 stroke-[2.5]" />}
                          </div>
                        </button>
                      );
                    })}
                  </div>
                </div>

                <Button type="submit" disabled={isSubmitting} className="w-full h-10 mt-1">
                  {isSubmitting ? 'Syncing accounts...' : 'Commit Changes'}
                </Button>

                {submitSuccess && (
                  <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-1.5 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                    <IconCheck className="size-4" />
                    <span>User settings and roles synced successfully</span>
                  </div>
                )}
              </form>

              {/* Show Full Details Button */}
              <Link href={ShowUserController.show.url(previewUser.id)}>
                <Button type="button" className="w-full h-10 mt-1" variant="outline">
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
