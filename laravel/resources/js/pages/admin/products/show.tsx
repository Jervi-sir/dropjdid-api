import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ShowProductController from '@/actions/App/Http/Controllers/Admin/Products/ShowProductController';
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
    IconRefresh,
    IconArrowLeft,
    IconCheck,
    IconAlertTriangle,
    IconBan,
    IconShoppingBag,
    IconCircleCheck,
    IconHeart,
    IconBookmark,
    IconRocket,
    IconLoader2,
    IconChevronLeft,
    IconChevronRight,
    IconInbox,
    IconMessageCircle,
} from '@tabler/icons-react';

interface Product {
    id: number;
    name: string;
    description: string;
    original_price: number;
    show_price: number;
    store_price: number;
    status: string;
    store: {
        id: number;
        name: string;
        username: string;
    } | null;
    category: {
        id: number;
        en: string;
        fr: string;
        ar: string;
    } | null;
    quality: {
        id: number;
        en: string;
    } | null;
    gender: {
        id: number;
        en: string;
    } | null;
    payment_method: {
        id: number;
        en: string;
    } | null;
    images: Array<{
        id: number;
        image: string;
        sort_order: number;
    }>;
    rejection_reasons: Array<{
        id: number;
        en: string;
        fr: string;
        ar: string;
    }>;
    created_at: string;
}

interface ProductShowProps {
    product: Product;
    statuses: { [key: number]: string };
}

export default function ProductShow({ product, statuses }: ProductShowProps) {
    const [activeImage, setActiveImage] = React.useState<string>(
        product.images?.[0]?.image ||
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
    );

    // Performance analytics states
    const [stats, setStats] = React.useState<any>(null);
    const [loadingStats, setLoadingStats] = React.useState(true);
    const [likedPage, setLikedPage] = React.useState(1);
    const [savedPage, setSavedPage] = React.useState(1);
    const [dropsPage, setDropsPage] = React.useState(1);

    const fetchStats = async () => {
        setLoadingStats(true);
        try {
            const res = await fetch(
                `/admin/products/${product.id}/stats?liked_page=${likedPage}&saved_page=${savedPage}&drops_page=${dropsPage}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );
            if (res.ok) {
                const result = await res.json();
                setStats(result);
            }
        } catch (e) {
            console.error('Error fetching stats:', e);
        } finally {
            setLoadingStats(false);
        }
    };

    React.useEffect(() => {
        fetchStats();
    }, [likedPage, savedPage, dropsPage]);

    const { data, setData, put, processing, errors, recentlySuccessful } =
        useForm({
            status: product.status,
            rejection_reason_en: '',
            rejection_reason_fr: '',
            rejection_reason_ar: '',
        });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(ShowProductController.update.url(product.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (data.status !== 'rejected') {
                    setData({
                        status: data.status,
                        rejection_reason_en: '',
                        rejection_reason_fr: '',
                        rejection_reason_ar: '',
                    });
                }
            },
        });
    };

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
            case 'archived':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Archived
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
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <>
            <Head title={`Audit Product - ${product.name}`} />
            <div className="mx-auto flex max-w-7xl flex-col gap-6 p-4 lg:p-8">
                {/* Navigation Bar */}
                <div>
                    <Link
                        href="/admin/products"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <IconArrowLeft className="size-4" />
                        <span>Back to Products</span>
                    </Link>
                </div>

                {/* Dashboard Title */}
                <div className="flex flex-col justify-between gap-4 border-b pb-6 md:flex-row md:items-center">
                    <div className="flex flex-col gap-3.5 md:flex-row md:items-center">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl border border-primary/20 bg-primary/10 text-primary shadow-sm">
                            <IconShoppingBag className="size-6 stroke-[1.5]" />
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                                    {product.name}
                                </h1>
                                {getStatusBadge(product.status)}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Audit Product ID #{product.id} • Registered on{' '}
                                {formatDate(product.created_at)}
                            </p>
                        </div>
                    </div>
                </div>

                {/* 3-Column Dashboard Layout */}
                <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    {/* Column 1: Image Gallery & Description (lg:col-span-5) */}
                    <div className="flex flex-col gap-6 lg:col-span-5">
                        {/* Gallery Card */}
                        <div className="flex flex-col gap-4 overflow-hidden rounded-2xl border bg-card p-4 shadow-xs">
                            <div className="relative aspect-square overflow-hidden rounded-xl border bg-muted/15 shadow-inner">
                                <img
                                    src={activeImage}
                                    alt={product.name}
                                    className="h-full w-full object-cover"
                                />
                            </div>
                            {product.images && product.images.length > 0 && (
                                <div className="grid grid-cols-5 gap-2.5">
                                    {product.images.map((img) => (
                                        <button
                                            key={img.id}
                                            onClick={() =>
                                                setActiveImage(img.image)
                                            }
                                            className={`aspect-square overflow-hidden rounded-lg border transition-all ${
                                                activeImage === img.image
                                                    ? 'scale-[1.03] border-primary ring-2 ring-primary/25'
                                                    : 'border-muted hover:border-foreground/40'
                                            }`}
                                        >
                                            <img
                                                src={img.image}
                                                alt="product thumbnail"
                                                className="h-full w-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Description and Features */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-6 shadow-xs">
                            <h3 className="text-sm font-bold tracking-wider text-muted-foreground uppercase">
                                Product Description
                            </h3>
                            <p className="text-sm leading-relaxed whitespace-pre-wrap text-foreground">
                                {product.description}
                            </p>

                            <hr className="border-muted/65" />

                            <div className="grid grid-cols-2 gap-4 text-xs">
                                <div className="flex flex-col gap-1 border-r pr-2">
                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                        Gender Target
                                    </span>
                                    <span className="font-bold text-foreground capitalize">
                                        {product.gender?.en || 'Unisex'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                        Payment Type
                                    </span>
                                    <span className="font-bold text-foreground capitalize">
                                        {product.payment_method?.en ||
                                            'Standard'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Column 2: Status Moderator Form (lg:col-span-4) */}
                    <div className="flex flex-col gap-6 lg:col-span-4">
                        <form
                            onSubmit={handleSubmit}
                            className="flex flex-col gap-5 rounded-2xl border bg-card p-6 shadow-xs"
                        >
                            <div className="flex items-center justify-between border-b pb-4">
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Moderator Hub
                                </h3>
                                <IconCircleCheck className="size-5 text-primary" />
                            </div>

                            {/* Status Selector */}
                            <div className="flex flex-col gap-2">
                                <label className="text-xs font-semibold text-muted-foreground">
                                    Modify Status
                                </label>
                                <Select
                                    value={data.status}
                                    onValueChange={(val) =>
                                        setData('status', val)
                                    }
                                >
                                    <SelectTrigger className="h-11 w-full border-input bg-background">
                                        <SelectValue placeholder="Select Status" />
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
                                {errors.status && (
                                    <p className="mt-1 text-xs font-semibold text-rose-500">
                                        {errors.status}
                                    </p>
                                )}
                            </div>

                            {/* Dynamic Localized Rejection Fields (English, French, Arabic RTL supported) */}
                            <div
                                className={`flex flex-col gap-4 overflow-hidden transition-all duration-300 ease-in-out ${
                                    data.status === 'rejected'
                                        ? 'mt-2 max-h-[450px] opacity-100'
                                        : 'pointer-events-none max-h-0 opacity-0'
                                }`}
                            >
                                <div className="flex flex-col gap-4 border-t pt-4">
                                    <div className="flex items-center gap-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                                        <IconAlertTriangle className="size-4 shrink-0" />
                                        <span>
                                            Rejection translations required
                                        </span>
                                    </div>

                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Reason (English)
                                        </label>
                                        <Input
                                            placeholder="Type rejection reason in English..."
                                            value={data.rejection_reason_en}
                                            onChange={(e) =>
                                                setData(
                                                    'rejection_reason_en',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-10 bg-background"
                                        />
                                        {errors.rejection_reason_en && (
                                            <p className="text-[10px] font-bold text-rose-500">
                                                {errors.rejection_reason_en}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Reason (French)
                                        </label>
                                        <Input
                                            placeholder="Saisir la raison en français..."
                                            value={data.rejection_reason_fr}
                                            onChange={(e) =>
                                                setData(
                                                    'rejection_reason_fr',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-10 bg-background"
                                        />
                                        {errors.rejection_reason_fr && (
                                            <p className="text-[10px] font-bold text-rose-500">
                                                {errors.rejection_reason_fr}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-[10px] font-bold text-muted-foreground uppercase">
                                            Reason (Arabic)
                                        </label>
                                        <Input
                                            dir="rtl"
                                            placeholder="أدخل سبب الرفض باللغة العربية..."
                                            value={data.rejection_reason_ar}
                                            onChange={(e) =>
                                                setData(
                                                    'rejection_reason_ar',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-10 bg-background text-right"
                                        />
                                        {errors.rejection_reason_ar && (
                                            <p className="text-[10px] font-bold text-rose-500">
                                                {errors.rejection_reason_ar}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Submit and Notifications */}
                            <Button
                                type="submit"
                                disabled={processing}
                                className="mt-2 h-11 w-full text-xs font-bold tracking-wider uppercase"
                            >
                                {processing
                                    ? 'Applying decisions...'
                                    : 'Commit Audit Decisions'}
                            </Button>

                            {recentlySuccessful && (
                                <div className="flex items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <IconCheck className="size-4 shrink-0" />
                                    <span>Audit updated successfully</span>
                                </div>
                            )}
                        </form>
                    </div>

                    {/* Column 3: Store owner & rejection history (lg:col-span-3) */}
                    <div className="flex flex-col gap-6 lg:col-span-3">
                        {/* Store Card */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconShoppingBag className="size-5 text-muted-foreground" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Seller Profile
                                </h3>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="flex size-11 shrink-0 items-center justify-center rounded-full bg-indigo-500/10 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    {product.store?.name
                                        ?.charAt(0)
                                        .toUpperCase() || 'S'}
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-sm leading-tight font-bold text-foreground">
                                        {product.store?.name || 'Unknown Store'}
                                    </span>
                                    <span className="mt-0.5 text-xs text-muted-foreground">
                                        {product.store?.username
                                            ? `@${product.store.username}`
                                            : ''}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Rejection History Log */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconBan className="size-5 text-rose-500" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Audit Log
                                </h3>
                            </div>

                            {product.rejection_reasons &&
                            product.rejection_reasons.length > 0 ? (
                                <div className="flex max-h-[300px] flex-col gap-4 overflow-y-auto pr-1">
                                    {product.rejection_reasons.map(
                                        (reason, index) => (
                                            <div
                                                key={reason.id || index}
                                                className="flex items-start gap-2.5 border-b pb-3 text-xs last:border-0 last:pb-0"
                                            >
                                                <div className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-rose-500/15 font-bold text-rose-500">
                                                    {reason.id || index + 1}
                                                </div>
                                                <div className="flex flex-1 flex-col gap-1 leading-relaxed">
                                                    <span className="font-bold text-foreground">
                                                        Audit Comment
                                                    </span>
                                                    <div className="mt-1 flex flex-col gap-1 text-muted-foreground">
                                                        {reason.en && (
                                                            <div>
                                                                <strong className="text-[9px] font-bold tracking-wider text-foreground uppercase">
                                                                    EN:
                                                                </strong>{' '}
                                                                {reason.en}
                                                            </div>
                                                        )}
                                                        {reason.fr && (
                                                            <div>
                                                                <strong className="text-[9px] font-bold tracking-wider text-foreground uppercase">
                                                                    FR:
                                                                </strong>{' '}
                                                                {reason.fr}
                                                            </div>
                                                        )}
                                                        {reason.ar && (
                                                            <div
                                                                className="mt-0.5 text-right"
                                                                dir="rtl"
                                                            >
                                                                <strong className="text-[9px] font-bold tracking-wider text-foreground uppercase">
                                                                    AR:
                                                                </strong>{' '}
                                                                {reason.ar}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ),
                                    )}
                                </div>
                            ) : (
                                <div className="py-6 text-center text-xs text-muted-foreground">
                                    No rejection history found. This product is
                                    clean.
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Product Performance Stats Panel */}
                <div className="mt-12 flex flex-col gap-6 border-t pt-10">
                    <div>
                        <h2 className="text-2xl font-black tracking-tight text-foreground">
                            Interaction & Distribution Stats
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Analyze product popularity, active shopper
                            wishlists, and orders distributed across publishing
                            drops.
                        </p>
                    </div>

                    {/* KPI Counters Grid */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {/* Likes KPI */}
                        <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                <IconHeart className="size-6 stroke-[1.8]" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Total Likes
                                </span>
                                <span className="mt-0.5 text-xl font-black text-foreground">
                                    {loadingStats ? (
                                        <IconLoader2 className="size-4 animate-spin text-muted-foreground" />
                                    ) : (
                                        (stats?.kpis?.liked_count ?? 0)
                                    )}{' '}
                                    Likes
                                </span>
                            </div>
                        </div>

                        {/* Saves KPI */}
                        <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <IconBookmark className="size-6 stroke-[1.8]" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Times Saved
                                </span>
                                <span className="mt-0.5 text-xl font-black text-foreground">
                                    {loadingStats ? (
                                        <IconLoader2 className="size-4 animate-spin text-muted-foreground" />
                                    ) : (
                                        (stats?.kpis?.saved_count ?? 0)
                                    )}{' '}
                                    Saves
                                </span>
                            </div>
                        </div>

                        {/* Orders KPI */}
                        <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <IconShoppingBag className="size-6 stroke-[1.8]" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Total Orders
                                </span>
                                <span className="mt-0.5 text-xl font-black text-foreground">
                                    {loadingStats ? (
                                        <IconLoader2 className="size-4 animate-spin text-muted-foreground" />
                                    ) : (
                                        (stats?.kpis?.orders_count ?? 0)
                                    )}{' '}
                                    Orders
                                </span>
                            </div>
                        </div>

                        {/* Drops KPI */}
                        <div className="flex items-center gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <IconRocket className="size-6 stroke-[1.8]" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Drops Carrying Item
                                </span>
                                <span className="mt-0.5 text-xl font-black text-foreground">
                                    {loadingStats ? (
                                        <IconLoader2 className="size-4 animate-spin text-muted-foreground" />
                                    ) : (
                                        (stats?.kpis?.drops_count ?? 0)
                                    )}{' '}
                                    Drops
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Interactive Three Tables View */}
                    <div className="mt-4 grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                        {/* Table 1: Who Liked This Product (lg:col-span-6) */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs lg:col-span-6">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconHeart className="size-5 stroke-[1.8] text-rose-500" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Who Liked this Product
                                </h3>
                            </div>

                            <div className="min-h-[220px] overflow-x-auto">
                                {loadingStats ? (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16">
                                        <IconLoader2 className="size-6 animate-spin text-primary" />
                                        <span className="text-xs text-muted-foreground">
                                            Loading likes...
                                        </span>
                                    </div>
                                ) : stats?.liked_users?.data?.length > 0 ? (
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="border-b bg-muted/5 text-[10px] font-bold text-muted-foreground uppercase">
                                                <th className="py-2.5 pl-3">
                                                    Shopper
                                                </th>
                                                <th className="py-2.5">
                                                    Email
                                                </th>
                                                <th className="py-2.5 pr-3 text-right">
                                                    Liked Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.liked_users.data.map(
                                                (item: any) => (
                                                    <tr
                                                        key={item.id}
                                                        className="border-b transition-colors last:border-0 hover:bg-muted/5"
                                                    >
                                                        <td className="py-3 pl-3">
                                                            <div className="flex items-center gap-2.5">
                                                                <div className="flex size-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-rose-500/10 text-[10px] font-bold text-rose-500">
                                                                    {item.user
                                                                        ?.image ? (
                                                                        <img
                                                                            src={
                                                                                item
                                                                                    .user
                                                                                    .image
                                                                            }
                                                                            alt={
                                                                                item
                                                                                    .user
                                                                                    .username
                                                                            }
                                                                            className="h-full w-full object-cover"
                                                                        />
                                                                    ) : (
                                                                        <IconUser className="size-3.5" />
                                                                    )}
                                                                </div>
                                                                <div className="flex flex-col">
                                                                    <span className="text-xs font-bold text-foreground">
                                                                        {item
                                                                            .user
                                                                            ?.full_name ||
                                                                            'N/A'}
                                                                    </span>
                                                                    <span className="text-[9px] text-muted-foreground">
                                                                        @
                                                                        {item
                                                                            .user
                                                                            ?.username ||
                                                                            'N/A'}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="py-3 text-xs font-semibold text-muted-foreground">
                                                            {item.user?.email ||
                                                                'N/A'}
                                                        </td>
                                                        <td className="py-3 pr-3 text-right text-[10px] font-medium text-muted-foreground">
                                                            {item.created_at
                                                                ? new Date(
                                                                      item.created_at,
                                                                  ).toLocaleDateString()
                                                                : 'N/A'}
                                                        </td>
                                                    </tr>
                                                ),
                                            )}
                                        </tbody>
                                    </table>
                                ) : (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16 text-center text-muted-foreground">
                                        <IconInbox className="size-8 text-muted-foreground/45" />
                                        <span className="text-xs font-semibold">
                                            No likes recorded yet
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Table 1 Pagination */}
                            {stats?.liked_users?.last_page > 1 && (
                                <div className="flex items-center justify-between border-t pt-3.5">
                                    <span className="text-[10px] font-semibold text-muted-foreground">
                                        Page {stats.liked_users.current_page} of{' '}
                                        {stats.liked_users.last_page}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={likedPage === 1}
                                            onClick={() =>
                                                setLikedPage((prev) =>
                                                    Math.max(1, prev - 1),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronLeft className="size-3.5" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={
                                                likedPage ===
                                                stats.liked_users.last_page
                                            }
                                            onClick={() =>
                                                setLikedPage((prev) =>
                                                    Math.min(
                                                        stats.liked_users
                                                            .last_page,
                                                        prev + 1,
                                                    ),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronRight className="size-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Table 2: Who Saved This Product (lg:col-span-6) */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs lg:col-span-6">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconBookmark className="size-5 stroke-[1.8] text-amber-500" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Who Saved this Product
                                </h3>
                            </div>

                            <div className="min-h-[220px] overflow-x-auto">
                                {loadingStats ? (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16">
                                        <IconLoader2 className="size-6 animate-spin text-primary" />
                                        <span className="text-xs text-muted-foreground">
                                            Loading saves...
                                        </span>
                                    </div>
                                ) : stats?.saved_users?.data?.length > 0 ? (
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="border-b bg-muted/5 text-[10px] font-bold text-muted-foreground uppercase">
                                                <th className="py-2.5 pl-3">
                                                    Shopper
                                                </th>
                                                <th className="py-2.5">
                                                    Email
                                                </th>
                                                <th className="py-2.5 pr-3 text-right">
                                                    Saved Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.saved_users.data.map(
                                                (item: any) => (
                                                    <tr
                                                        key={item.id}
                                                        className="border-b transition-colors last:border-0 hover:bg-muted/5"
                                                    >
                                                        <td className="py-3 pl-3">
                                                            <div className="flex items-center gap-2.5">
                                                                <div className="flex size-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-amber-500/10 text-[10px] font-bold text-amber-500">
                                                                    {item.user
                                                                        ?.image ? (
                                                                        <img
                                                                            src={
                                                                                item
                                                                                    .user
                                                                                    .image
                                                                            }
                                                                            alt={
                                                                                item
                                                                                    .user
                                                                                    .username
                                                                            }
                                                                            className="h-full w-full object-cover"
                                                                        />
                                                                    ) : (
                                                                        <IconUser className="size-3.5" />
                                                                    )}
                                                                </div>
                                                                <div className="flex flex-col">
                                                                    <span className="text-xs font-bold text-foreground">
                                                                        {item
                                                                            .user
                                                                            ?.full_name ||
                                                                            'N/A'}
                                                                    </span>
                                                                    <span className="text-[9px] text-muted-foreground">
                                                                        @
                                                                        {item
                                                                            .user
                                                                            ?.username ||
                                                                            'N/A'}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="py-3 text-xs font-semibold text-muted-foreground">
                                                            {item.user?.email ||
                                                                'N/A'}
                                                        </td>
                                                        <td className="py-3 pr-3 text-right text-[10px] font-medium text-muted-foreground">
                                                            {item.created_at
                                                                ? new Date(
                                                                      item.created_at,
                                                                  ).toLocaleDateString()
                                                                : 'N/A'}
                                                        </td>
                                                    </tr>
                                                ),
                                            )}
                                        </tbody>
                                    </table>
                                ) : (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16 text-center text-muted-foreground">
                                        <IconInbox className="size-8 text-muted-foreground/45" />
                                        <span className="text-xs font-semibold">
                                            No saves recorded yet
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Table 2 Pagination */}
                            {stats?.saved_users?.last_page > 1 && (
                                <div className="flex items-center justify-between border-t pt-3.5">
                                    <span className="text-[10px] font-semibold text-muted-foreground">
                                        Page {stats.saved_users.current_page} of{' '}
                                        {stats.saved_users.last_page}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={savedPage === 1}
                                            onClick={() =>
                                                setSavedPage((prev) =>
                                                    Math.max(1, prev - 1),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronLeft className="size-3.5" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={
                                                savedPage ===
                                                stats.saved_users.last_page
                                            }
                                            onClick={() =>
                                                setSavedPage((prev) =>
                                                    Math.min(
                                                        stats.saved_users
                                                            .last_page,
                                                        prev + 1,
                                                    ),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronRight className="size-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Table 3: Product Drops & Performance (lg:col-span-12) */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs lg:col-span-12">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconRocket className="size-5 stroke-[1.8] text-indigo-500" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Drops Carrying this Product
                                </h3>
                            </div>

                            <div className="min-h-[180px] overflow-x-auto">
                                {loadingStats ? (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16">
                                        <IconLoader2 className="size-6 animate-spin text-primary" />
                                        <span className="text-xs text-muted-foreground">
                                            Loading drops list...
                                        </span>
                                    </div>
                                ) : stats?.drops?.data?.length > 0 ? (
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="border-b bg-muted/5 text-[10px] font-bold text-muted-foreground uppercase">
                                                <th className="py-2.5 pl-3">
                                                    Drop Title
                                                </th>
                                                <th className="py-2.5">
                                                    Schedule
                                                </th>
                                                <th className="py-2.5 text-center">
                                                    Status
                                                </th>
                                                <th className="py-2.5 text-center">
                                                    Drop Price
                                                </th>
                                                <th className="py-2.5 pr-3 text-right">
                                                    Exchanged Orders
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.drops.data.map(
                                                (item: any) => (
                                                    <tr
                                                        key={item.id}
                                                        className="border-b transition-colors last:border-0 hover:bg-muted/5"
                                                    >
                                                        <td className="py-3 pl-3">
                                                            <span className="text-xs font-extrabold text-foreground">
                                                                {item.title}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 text-xs font-semibold text-muted-foreground">
                                                            {item.starts_at
                                                                ? new Date(
                                                                      item.starts_at,
                                                                  ).toLocaleDateString()
                                                                : 'N/A'}{' '}
                                                            -{' '}
                                                            {item.ends_at
                                                                ? new Date(
                                                                      item.ends_at,
                                                                  ).toLocaleDateString()
                                                                : 'N/A'}
                                                        </td>
                                                        <td className="py-3 text-center">
                                                            <Badge className="border border-indigo-500/20 bg-indigo-50 text-[10px] text-indigo-700 capitalize dark:bg-indigo-500/10 dark:text-indigo-400">
                                                                {item.status ===
                                                                1
                                                                    ? 'Published'
                                                                    : item.status ===
                                                                        2
                                                                      ? 'Ended'
                                                                      : item.status ===
                                                                          3
                                                                        ? 'Cancelled'
                                                                        : 'Draft'}
                                                            </Badge>
                                                        </td>
                                                        <td className="py-3 text-center text-xs font-black text-foreground">
                                                            {item.drop_price
                                                                ? `${Number(item.drop_price).toFixed(2)} USD`
                                                                : 'Use Standard'}
                                                        </td>
                                                        <td className="py-3 pr-3 text-right">
                                                            <span className="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                                {
                                                                    item.orders_count
                                                                }{' '}
                                                                Orders
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ),
                                            )}
                                        </tbody>
                                    </table>
                                ) : (
                                    <div className="flex flex-col items-center justify-center gap-2 py-16 text-center text-muted-foreground">
                                        <IconInbox className="size-8 text-muted-foreground/45" />
                                        <span className="text-xs font-semibold">
                                            This product has not been added to
                                            any drops yet
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Table 3 Pagination */}
                            {stats?.drops?.last_page > 1 && (
                                <div className="flex items-center justify-between border-t pt-3.5">
                                    <span className="text-[10px] font-semibold text-muted-foreground">
                                        Page {stats.drops.current_page} of{' '}
                                        {stats.drops.last_page}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={dropsPage === 1}
                                            onClick={() =>
                                                setDropsPage((prev) =>
                                                    Math.max(1, prev - 1),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronLeft className="size-3.5" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            disabled={
                                                dropsPage ===
                                                stats.drops.last_page
                                            }
                                            onClick={() =>
                                                setDropsPage((prev) =>
                                                    Math.min(
                                                        stats.drops.last_page,
                                                        prev + 1,
                                                    ),
                                                )
                                            }
                                            className="h-7 w-7 p-0"
                                        >
                                            <IconChevronRight className="size-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
