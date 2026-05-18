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
      <div className="flex flex-col gap-6 p-4 lg:p-8 max-w-7xl mx-auto">

        {/* Navigation Bar */}
        <div>
          <Link
            href="/admin/users"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <IconArrowLeft className="size-4" />
            <span>Back to Users</span>
          </Link>
        </div>

        {/* Dashboard Title */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
          <div className="flex flex-col md:flex-row md:items-center gap-4">
            <div className="size-16 rounded-full overflow-hidden border shrink-0 bg-primary/10 text-primary flex items-center justify-center font-bold text-xl shadow-sm">
              {user.image ? (
                <img src={user.image} alt={user.full_name} className="w-full h-full object-cover" />
              ) : (
                user.full_name.charAt(0).toUpperCase()
              )}
            </div>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-3xl font-extrabold tracking-tight text-foreground">{user.full_name}</h1>
                {user.is_active ? (
                  <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                    Active
                  </Badge>
                ) : (
                  <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
                    Suspended
                  </Badge>
                )}
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                Audit Profile ID #{user.id} • Registered on {formatDate(user.created_at)}
              </p>
            </div>
          </div>
        </div>

        {/* 2-Column Dashboard Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

          {/* Left Column: Access Control & Role checklist (lg:col-span-8) */}
          <div className="lg:col-span-8 flex flex-col gap-6">

            <form onSubmit={handleSubmit} className="bg-card border rounded-2xl p-6 shadow-xs flex flex-col gap-6">
              <div className="flex items-center justify-between border-b pb-4">
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                  <IconShield className="size-5 text-primary" />
                  <span>Roles & Access Control Room</span>
                </h3>
                <IconCircleCheck className="size-5 text-primary animate-pulse" />
              </div>

              {/* Status Selector */}
              <div className="flex flex-col sm:flex-row items-center justify-between p-4 rounded-xl border bg-muted/10 gap-4">
                <div className="flex flex-col gap-0.5">
                  <span className="text-xs font-bold text-foreground">Account Access State</span>
                  <span className="text-xs text-muted-foreground">Suspended accounts will be locked out of the mobile app immediately.</span>
                </div>
                <Select
                  value={data.is_active ? '1' : '0'}
                  onValueChange={(val) => setData('is_active', val === '1')}
                >
                  <SelectTrigger className="w-full sm:w-36 h-11 bg-background border-input">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="1">Active</SelectItem>
                    <SelectItem value="0">Suspended</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Dynamic Database Roles selection checklist */}
              <div className="flex flex-col gap-4">
                <label className="text-xs font-bold text-muted-foreground">Select User Roles</label>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                  {all_roles.map((role) => {
                    const isChecked = data.role_ids.includes(role.id);
                    return (
                      <button
                        type="button"
                        key={role.id}
                        onClick={() => handleRoleToggle(role.id)}
                        className={`flex items-center justify-between p-4 rounded-xl border text-left transition-all ${
                          isChecked
                            ? 'border-primary bg-primary/5 ring-1 ring-primary/10 shadow-xs'
                            : 'border-muted hover:border-foreground/30 bg-card'
                        }`}
                      >
                        <div className="flex flex-col gap-0.5">
                          <span className="text-xs font-bold capitalize text-foreground">{role.en}</span>
                          <span className="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">
                            Code: {role.code}
                          </span>
                        </div>
                        <div
                          className={`size-5 rounded-full border flex items-center justify-center transition-all shrink-0 ${
                            isChecked ? 'bg-primary border-primary text-primary-foreground' : 'border-muted-foreground/35 bg-background'
                          }`}
                        >
                          {isChecked && <IconCheck className="size-3.5 stroke-[2.5]" />}
                        </div>
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Submit Buttons */}
              <div className="flex items-center justify-end gap-3 pt-4 border-t">
                <Button
                  type="submit"
                  disabled={processing}
                  className="h-11 px-8 text-xs font-bold uppercase tracking-wider shadow-md"
                >
                  {processing ? 'Saving changes...' : 'Save Profile Settings'}
                </Button>
              </div>

              {recentlySuccessful && (
                <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-2.5 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                  <IconCheck className="size-4 shrink-0" />
                  <span>User accounts and permissions synchronized successfully</span>
                </div>
              )}
            </form>
          </div>

          {/* Right Column: Profile Overview & Linked Stores (lg:col-span-4) */}
          <div className="lg:col-span-4 flex flex-col gap-6">

            {/* Profile Contact Card */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconUser className="size-5 text-muted-foreground" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">User Contacts</h3>
              </div>
              <div className="flex flex-col gap-3 text-xs">
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconMail className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-bold text-foreground truncate">{user.email || 'No email registered'}</span>
                </div>
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconPhone className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-bold text-foreground">{user.phone_number || 'No phone registered'}</span>
                </div>
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconCalendar className="size-4 text-muted-foreground shrink-0" />
                  <span>Joined on {formatDate(user.created_at)}</span>
                </div>
              </div>
            </div>

            {/* Associated Stores */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconShoppingBag className="size-5 text-emerald-600 dark:text-emerald-400" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Linked Stores</h3>
              </div>
              {user.stores && user.stores.length > 0 ? (
                <div className="flex flex-col gap-2.5 max-h-[220px] overflow-y-auto pr-1">
                  {user.stores.map((st) => (
                    <div key={st.id} className="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-muted text-xs font-bold text-foreground">
                      <span>{st.name}</span>
                      <Badge variant="outline" className="text-[9px]">ID: {st.id}</Badge>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-6 text-xs text-muted-foreground">
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
