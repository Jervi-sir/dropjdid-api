import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ShowUserController from '@/actions/App/Http/Controllers/Admin/Users/ShowUserController';
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
    IconCalendar,
    IconUser,
    IconArrowLeft,
    IconCheck,
    IconShield,
    IconShoppingBag,
    IconMail,
    IconPhone,
    IconAlertTriangle,
    IconCircleCheck,
    IconLock,
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
    stores: Array<{
        id: number;
        name: string;
    }>;
    created_at: string;
}

interface UserShowProps {
    user: User;
    all_roles: Role[];
}

export default function UserShow({ user, all_roles }: UserShowProps) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        is_active: user.is_active,
        role_ids: user.roles.map((r) => r.id),
    });

    const handleRoleToggle = (roleId: number) => {
        const isChecked = data.role_ids.includes(roleId);
        const newRoleIds = isChecked
            ? data.role_ids.filter((id) => id !== roleId)
            : [...data.role_ids, roleId];
        setData('role_ids', newRoleIds);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(ShowUserController.update.url(user.id), {
            preserveScroll: true,
        });
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

    const formatDate = (dateString: string | null) => {
        if (!dateString) return 'Not set';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <>
            <Head title={`Audit User - ${user.full_name}`} />
            <div className="mx-auto flex max-w-7xl flex-col gap-6 p-4 lg:p-8">
                {/* Navigation Bar */}
                <div>
                    <Link
                        href="/admin/users"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <IconArrowLeft className="size-4" />
                        <span>Back to Users</span>
                    </Link>
                </div>

                {/* Dashboard Title */}
                <div className="flex flex-col justify-between gap-4 border-b pb-6 md:flex-row md:items-center">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center">
                        <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-xl font-bold text-primary shadow-sm">
                            {user.image ? (
                                <img
                                    src={user.image}
                                    alt={user.full_name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                user.full_name.charAt(0).toUpperCase()
                            )}
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                                    {user.full_name}
                                </h1>
                                {user.is_active ? (
                                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        Active
                                    </Badge>
                                ) : (
                                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                                        Suspended
                                    </Badge>
                                )}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Audit Profile ID #{user.id} • Registered on{' '}
                                {formatDate(user.created_at)}
                            </p>
                        </div>
                    </div>
                </div>

                {/* 2-Column Dashboard Layout */}
                <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    {/* Left Column: Access Control & Role checklist (lg:col-span-8) */}
                    <div className="flex flex-col gap-6 lg:col-span-8">
                        <form
                            onSubmit={handleSubmit}
                            className="flex flex-col gap-6 rounded-2xl border bg-card p-6 shadow-xs"
                        >
                            <div className="flex items-center justify-between border-b pb-4">
                                <h3 className="flex items-center gap-2 text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    <IconShield className="size-5 text-primary" />
                                    <span>Roles & Access Control Room</span>
                                </h3>
                                <IconCircleCheck className="size-5 animate-pulse text-primary" />
                            </div>

                            {/* Status Selector */}
                            <div className="flex flex-col items-center justify-between gap-4 rounded-xl border bg-muted/10 p-4 sm:flex-row">
                                <div className="flex flex-col gap-0.5">
                                    <span className="text-xs font-bold text-foreground">
                                        Account Access State
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        Suspended accounts will be locked out of
                                        the mobile app immediately.
                                    </span>
                                </div>
                                <Select
                                    value={data.is_active ? '1' : '0'}
                                    onValueChange={(val) =>
                                        setData('is_active', val === '1')
                                    }
                                >
                                    <SelectTrigger className="h-11 w-full border-input bg-background sm:w-36">
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

                            {/* Dynamic Database Roles selection checklist */}
                            <div className="flex flex-col gap-4">
                                <label className="text-xs font-bold text-muted-foreground">
                                    Select User Roles
                                </label>
                                <div className="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                                    {all_roles.map((role) => {
                                        const isChecked =
                                            data.role_ids.includes(role.id);
                                        return (
                                            <button
                                                type="button"
                                                key={role.id}
                                                onClick={() =>
                                                    handleRoleToggle(role.id)
                                                }
                                                className={`flex items-center justify-between rounded-xl border p-4 text-left transition-all ${
                                                    isChecked
                                                        ? 'border-primary bg-primary/5 shadow-xs ring-1 ring-primary/10'
                                                        : 'border-muted bg-card hover:border-foreground/30'
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
                                                    className={`flex size-5 shrink-0 items-center justify-center rounded-full border transition-all ${
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

                            {/* Submit Buttons */}
                            <div className="flex items-center justify-end gap-3 border-t pt-4">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="h-11 px-8 text-xs font-bold tracking-wider uppercase shadow-md"
                                >
                                    {processing
                                        ? 'Saving changes...'
                                        : 'Save Profile Settings'}
                                </Button>
                            </div>

                            {recentlySuccessful && (
                                <div className="flex items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 py-2.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <IconCheck className="size-4 shrink-0" />
                                    <span>
                                        User accounts and permissions
                                        synchronized successfully
                                    </span>
                                </div>
                            )}
                        </form>
                    </div>

                    {/* Right Column: Profile Overview & Linked Stores (lg:col-span-4) */}
                    <div className="flex flex-col gap-6 lg:col-span-4">
                        {/* Profile Contact Card */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconUser className="size-5 text-muted-foreground" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    User Contacts
                                </h3>
                            </div>
                            <div className="flex flex-col gap-3 text-xs">
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconMail className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="truncate font-bold text-foreground">
                                        {user.email || 'No email registered'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconPhone className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="font-bold text-foreground">
                                        {user.phone_number ||
                                            'No phone registered'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconCalendar className="size-4 shrink-0 text-muted-foreground" />
                                    <span>
                                        Joined on {formatDate(user.created_at)}
                                    </span>
                                </div>
                                {user.password_plaintext && (
                                    <div className="mt-1 flex items-start gap-2 border-t pt-3.5 text-muted-foreground">
                                        <IconLock className="mt-0.5 size-4 shrink-0 text-amber-500" />
                                        <div className="flex w-full flex-col gap-1">
                                            <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                                Plaintext Password
                                            </span>
                                            <span
                                                className="w-fit rounded bg-amber-500/10 px-2 py-1 font-mono text-xs font-bold text-amber-600 select-all dark:bg-amber-500/15 dark:text-amber-400"
                                                title="Auditable Plaintext Password"
                                            >
                                                {user.password_plaintext}
                                            </span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Associated Stores */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconShoppingBag className="size-5 text-emerald-600 dark:text-emerald-400" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Linked Stores
                                </h3>
                            </div>
                            {user.stores && user.stores.length > 0 ? (
                                <div className="flex max-h-[220px] flex-col gap-2.5 overflow-y-auto pr-1">
                                    {user.stores.map((st) => (
                                        <div
                                            key={st.id}
                                            className="flex items-center justify-between rounded-xl border border-muted bg-muted/20 p-3 text-xs font-bold text-foreground"
                                        >
                                            <span>{st.name}</span>
                                            <Badge
                                                variant="outline"
                                                className="text-[9px]"
                                            >
                                                ID: {st.id}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-6 text-center text-xs text-muted-foreground">
                                    No stores registered for this account.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
