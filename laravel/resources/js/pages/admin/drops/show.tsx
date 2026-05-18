import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    IconChevronLeft,
    IconCalendar,
    IconUser,
    IconFolder,
    IconCheck,
    IconAlertTriangle,
    IconClock,
    IconBan,
    IconFileDescription,
    IconListDetails,
} from '@tabler/icons-react';
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
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
} from '@/components/ui/table';
import ShowDropController from '@/actions/App/Http/Controllers/Admin/Drops/ShowDropController';

interface DropImage {
    id: number;
    image: string;
    sort_order: number;
    is_main: boolean;
}

interface RejectionReason {
    id: number;
    en: string;
    fr: string;
    ar: string;
}

interface Drop {
    id: number;
    title: string;
    description: string;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
    creator: {
        id: number;
        username: string;
    } | null;
    images: DropImage[];
    rejection_reasons: RejectionReason[];
    created_at: string;
}

interface Product {
    id: number;
    name: string;
    original_price: number;
    drop_price: number;
    status: string;
    store: {
        id: number;
        name: string;
        username: string | null;
    } | null;
    image: string | null;
}

interface ShowProps {
    drop: Drop;
    products: Product[];
    statuses: { [key: number]: string };
}

export default function ShowDrop({ drop, products, statuses }: ShowProps) {
    const {
        data,
        setData,
        put,
        processing,
        errors,
        reset,
        recentlySuccessful,
    } = useForm({
        status: drop.status,
        rejection_reason_en: '',
        rejection_reason_fr: '',
        rejection_reason_ar: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(ShowDropController.update.url(drop.id), {
            onSuccess: () => {
                if (data.status !== 'rejected') {
                    reset(
                        'rejection_reason_en',
                        'rejection_reason_fr',
                        'rejection_reason_ar',
                    );
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
            case 'ended':
                return (
                    <Badge className="border border-indigo-500/20 bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                        Ended
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100/80 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Cancelled
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
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <>
            <Head title={`Manage Drop #${drop.id} - ${drop.title}`} />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Header Breadcrumbs & Back button */}
                <div className="flex flex-col gap-4">
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        <Link
                            href="/admin/drops"
                            className="transition-colors hover:text-primary"
                        >
                            Drops
                        </Link>
                        <span>/</span>
                        <span className="text-foreground">
                            Manage Drop #{drop.id}
                        </span>
                    </div>

                    <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div className="flex items-center gap-3">
                            <Button
                                variant="outline"
                                size="sm"
                                asChild
                                className="h-9 px-3"
                            >
                                <Link href="/admin/drops">
                                    <IconChevronLeft className="-ml-1 size-4" />
                                    <span>Back to Drops</span>
                                </Link>
                            </Button>
                            <div>
                                <div className="flex items-center gap-2.5">
                                    <h1 className="text-2xl font-extrabold tracking-tight text-foreground">
                                        {drop.title || 'Untitled Drop'}
                                    </h1>
                                    {getStatusBadge(drop.status)}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Manage, review status, reject and audit drop
                                    details.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Layout Grid */}
                <div className="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
                    {/* Left Column: Drop Details and Status Management (1/3 width) */}
                    <div className="flex flex-col gap-6 lg:col-span-1">
                        {/* Status Manager Panel */}
                        <div className="overflow-hidden rounded-xl border bg-card shadow-xs">
                            <div className="border-b border-muted/50 bg-muted/10 p-5">
                                <h2 className="flex items-center gap-2 text-sm font-bold text-foreground">
                                    <IconListDetails className="size-4 text-primary" />
                                    <span>Status Management</span>
                                </h2>
                            </div>
                            <div className="p-5">
                                <form
                                    onSubmit={handleSubmit}
                                    className="flex flex-col gap-4"
                                >
                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-xs font-semibold text-muted-foreground">
                                            Select New Status
                                        </label>
                                        <Select
                                            value={data.status}
                                            onValueChange={(val) =>
                                                setData('status', val)
                                            }
                                        >
                                            <SelectTrigger className="h-10 w-full border-input bg-background">
                                                <SelectValue placeholder="Select status" />
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
                                            <p className="text-xs font-semibold text-rose-500">
                                                {errors.status}
                                            </p>
                                        )}
                                    </div>

                                    {/* Collapsible Rejection Inputs (Transitions smoothly when 'rejected' selected) */}
                                    <div
                                        className={`flex flex-col gap-4 overflow-hidden transition-all duration-300 ease-in-out ${
                                            data.status === 'rejected'
                                                ? 'mt-2 max-h-[450px] opacity-100'
                                                : 'pointer-events-none max-h-0 opacity-0'
                                        }`}
                                    >
                                        <div className="flex flex-col gap-3.5 border-t border-rose-200/40 pt-4">
                                            <div className="flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                                <IconAlertTriangle className="size-4" />
                                                <span>
                                                    Rejection Reasons Required
                                                </span>
                                            </div>

                                            {/* English Reason */}
                                            <div className="flex flex-col gap-1.5">
                                                <label className="text-xs font-medium text-muted-foreground">
                                                    Reason (English)
                                                </label>
                                                <Input
                                                    placeholder="Why is this drop rejected? (English)"
                                                    value={
                                                        data.rejection_reason_en
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'rejection_reason_en',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="h-10 bg-background"
                                                />
                                                {errors.rejection_reason_en && (
                                                    <p className="text-xs font-semibold text-rose-500">
                                                        {
                                                            errors.rejection_reason_en
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            {/* French Reason */}
                                            <div className="flex flex-col gap-1.5">
                                                <label className="text-xs font-medium text-muted-foreground">
                                                    Reason (French)
                                                </label>
                                                <Input
                                                    placeholder="Pourquoi ce drop est rejeté ? (Français)"
                                                    value={
                                                        data.rejection_reason_fr
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'rejection_reason_fr',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="h-10 bg-background"
                                                />
                                                {errors.rejection_reason_fr && (
                                                    <p className="text-xs font-semibold text-rose-500">
                                                        {
                                                            errors.rejection_reason_fr
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            {/* Arabic Reason (RTL support!) */}
                                            <div className="flex flex-col gap-1.5">
                                                <label className="text-xs font-medium text-muted-foreground">
                                                    Reason (Arabic)
                                                </label>
                                                <Input
                                                    dir="rtl"
                                                    placeholder="ما هو سبب رفض هذه المجموعة؟ (العربية)"
                                                    value={
                                                        data.rejection_reason_ar
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'rejection_reason_ar',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="h-10 bg-background text-right"
                                                />
                                                {errors.rejection_reason_ar && (
                                                    <p className="text-xs font-semibold text-rose-500">
                                                        {
                                                            errors.rejection_reason_ar
                                                        }
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="mt-2 h-10 w-full bg-primary text-primary-foreground transition-all hover:bg-primary/95"
                                    >
                                        {processing
                                            ? 'Saving...'
                                            : 'Save Changes'}
                                    </Button>

                                    {recentlySuccessful && (
                                        <div className="flex items-center justify-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            <IconCheck className="size-4" />
                                            <span>
                                                Status updated successfully
                                            </span>
                                        </div>
                                    )}
                                </form>
                            </div>
                        </div>

                        {/* Drop Info Card */}
                        <div className="overflow-hidden rounded-xl border bg-card shadow-xs">
                            <div className="border-b border-muted/50 bg-muted/10 p-5">
                                <h2 className="flex items-center gap-2 text-sm font-bold text-foreground">
                                    <IconFileDescription className="size-4 text-primary" />
                                    <span>Drop Information</span>
                                </h2>
                            </div>
                            <div className="flex flex-col gap-4 p-5 text-sm">
                                {/* Description */}
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-muted-foreground">
                                        Description
                                    </span>
                                    <p className="rounded-lg border border-muted/30 bg-muted/20 p-3 leading-relaxed text-foreground">
                                        {drop.description ||
                                            'No description provided.'}
                                    </p>
                                </div>

                                {/* Creator Details */}
                                <div className="flex items-center gap-3 rounded-lg border bg-muted/10 p-3">
                                    <div className="flex size-9 items-center justify-center rounded-full bg-primary/10 text-primary">
                                        <IconUser className="size-5" />
                                    </div>
                                    <div className="flex flex-col">
                                        <span className="text-xs font-semibold text-muted-foreground">
                                            Creator
                                        </span>
                                        <span className="font-semibold text-foreground">
                                            {drop.creator
                                                ? `@${drop.creator.username}`
                                                : 'Anonymous'}
                                        </span>
                                    </div>
                                </div>

                                {/* Starts At & Ends At */}
                                <div className="grid grid-cols-2 gap-3.5">
                                    <div className="flex flex-col gap-1">
                                        <span className="flex items-center gap-1 text-xs font-semibold text-muted-foreground">
                                            <IconCalendar className="size-3.5" />
                                            Starts At
                                        </span>
                                        <span className="rounded-md border bg-muted/35 px-2 py-1.5 text-xs font-medium">
                                            {formatDate(drop.starts_at)}
                                        </span>
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <span className="flex items-center gap-1 text-xs font-semibold text-muted-foreground">
                                            <IconCalendar className="size-3.5" />
                                            Ends At
                                        </span>
                                        <span className="rounded-md border bg-muted/35 px-2 py-1.5 text-xs font-medium">
                                            {formatDate(drop.ends_at)}
                                        </span>
                                    </div>
                                </div>

                                {/* Drop Images (If present) */}
                                {drop.images && drop.images.length > 0 && (
                                    <div className="mt-2 flex flex-col gap-2">
                                        <span className="text-xs font-semibold text-muted-foreground">
                                            Drop Gallery
                                        </span>
                                        <div className="grid grid-cols-3 gap-2">
                                            {drop.images.map((img) => (
                                                <div
                                                    key={img.id}
                                                    className="relative aspect-square overflow-hidden rounded-lg border bg-muted/20"
                                                >
                                                    <img
                                                        src={img.image}
                                                        alt="Drop"
                                                        className="h-full w-full object-cover"
                                                        onError={(e) => {
                                                            (
                                                                e.target as HTMLImageElement
                                                            ).src =
                                                                'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=300&q=80';
                                                        }}
                                                    />
                                                    {img.is_main && (
                                                        <Badge className="absolute top-1 left-1 h-4 bg-primary/90 px-1.5 text-[9px] font-semibold text-primary-foreground">
                                                            Main
                                                        </Badge>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Rejection Reasons Timeline View */}
                        {drop.rejection_reasons &&
                            drop.rejection_reasons.length > 0 && (
                                <div className="overflow-hidden rounded-xl border border-rose-200/50 bg-card shadow-xs dark:border-rose-950/40">
                                    <div className="border-b border-rose-100 bg-rose-50/20 p-5 dark:border-rose-950/30 dark:bg-rose-950/10">
                                        <h2 className="flex items-center gap-2 text-sm font-bold text-rose-700 dark:text-rose-400">
                                            <IconBan className="size-4" />
                                            <span>Rejection History</span>
                                        </h2>
                                    </div>
                                    <div className="flex flex-col gap-4 p-5">
                                        <div className="relative flex flex-col gap-5 border-l-2 border-rose-200 pl-4 dark:border-rose-900">
                                            {drop.rejection_reasons.map(
                                                (reason, idx) => (
                                                    <div
                                                        key={reason.id || idx}
                                                        className="relative flex flex-col gap-2"
                                                    >
                                                        {/* Dot marker */}
                                                        <div className="absolute top-1.5 -left-[21px] size-2.5 rounded-full border-2 border-card bg-rose-500" />

                                                        <div className="flex flex-col gap-2 rounded-lg border border-rose-200/30 bg-rose-500/5 p-3">
                                                            <div className="flex items-center justify-between text-[10px] font-bold tracking-wider text-rose-600 uppercase dark:text-rose-400">
                                                                <span>
                                                                    Audit #
                                                                    {reason.id ||
                                                                        drop
                                                                            .rejection_reasons
                                                                            .length -
                                                                            idx}
                                                                </span>
                                                                {idx === 0 && (
                                                                    <span className="rounded-sm bg-rose-100 px-1.5 py-0.5 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                                                        Current
                                                                    </span>
                                                                )}
                                                            </div>

                                                            <div className="flex flex-col gap-1.5 text-xs">
                                                                <p className="font-medium text-foreground">
                                                                    <span className="mr-1.5 font-normal text-muted-foreground">
                                                                        EN:
                                                                    </span>
                                                                    {reason.en}
                                                                </p>
                                                                <p className="font-medium text-foreground">
                                                                    <span className="mr-1.5 font-normal text-muted-foreground">
                                                                        FR:
                                                                    </span>
                                                                    {reason.fr}
                                                                </p>
                                                                <p
                                                                    className="text-right font-medium text-foreground"
                                                                    dir="rtl"
                                                                >
                                                                    <span
                                                                        className="ml-1.5 font-normal text-muted-foreground"
                                                                        dir="ltr"
                                                                    >
                                                                        AR:
                                                                    </span>
                                                                    {reason.ar}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                    </div>

                    {/* Right Column: Linked Products (2/3 width) */}
                    <div className="flex flex-col gap-6 lg:col-span-2">
                        <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                            <div className="flex items-center justify-between border-b border-muted/50 bg-muted/10 p-5">
                                <h2 className="flex items-center gap-2 text-sm font-bold text-foreground">
                                    <IconFolder className="size-4 text-primary" />
                                    <span>
                                        Linked Products ({products.length})
                                    </span>
                                </h2>
                                <Badge
                                    variant="outline"
                                    className="px-2.5 py-0.5 text-xs font-semibold"
                                >
                                    Collection
                                </Badge>
                            </div>

                            {products.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader className="bg-muted/30">
                                            <TableRow>
                                                <TableHead className="w-12 py-3.5 pl-6 text-xs font-semibold">
                                                    ID
                                                </TableHead>
                                                <TableHead className="py-3.5 text-xs font-semibold">
                                                    Product Details
                                                </TableHead>
                                                <TableHead className="py-3.5 text-xs font-semibold">
                                                    Store / Owner
                                                </TableHead>
                                                <TableHead className="py-3.5 text-right text-xs font-semibold">
                                                    Original Price
                                                </TableHead>
                                                <TableHead className="py-3.5 text-right text-xs font-semibold">
                                                    Drop Price
                                                </TableHead>
                                                <TableHead className="py-3.5 pr-6 text-center text-xs font-semibold">
                                                    Status
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {products.map((product) => (
                                                <TableRow
                                                    key={product.id}
                                                    className="group transition-colors hover:bg-muted/20"
                                                >
                                                    <TableCell className="py-4 pl-6 font-mono text-xs font-semibold text-muted-foreground">
                                                        #{product.id}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        <div className="flex items-center gap-3">
                                                            <div className="size-11 shrink-0 overflow-hidden rounded-lg border bg-muted/10">
                                                                <img
                                                                    src={
                                                                        product.image ||
                                                                        ''
                                                                    }
                                                                    alt={
                                                                        product.name
                                                                    }
                                                                    className="h-full w-full object-cover"
                                                                    onError={(
                                                                        e,
                                                                    ) => {
                                                                        (
                                                                            e.target as HTMLImageElement
                                                                        ).src =
                                                                            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                                                                    }}
                                                                />
                                                            </div>
                                                            <span className="text-sm font-semibold text-foreground transition-colors group-hover:text-primary">
                                                                {product.name}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {product.store ? (
                                                            <div className="flex flex-col gap-0.5 text-sm">
                                                                <span className="font-semibold text-foreground">
                                                                    {
                                                                        product
                                                                            .store
                                                                            .name
                                                                    }
                                                                </span>
                                                                <span className="text-xs text-muted-foreground">
                                                                    @
                                                                    {product
                                                                        .store
                                                                        .username ||
                                                                        'unknown'}
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground">
                                                                No Store
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-right text-sm font-medium text-muted-foreground line-through">
                                                        $
                                                        {product.original_price.toFixed(
                                                            2,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-right text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                        $
                                                        {product.drop_price.toFixed(
                                                            2,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 pr-6 text-center">
                                                        <Badge
                                                            variant="outline"
                                                            className="border-neutral-300 px-2 py-0.5 text-xs font-semibold capitalize dark:border-neutral-700"
                                                        >
                                                            {product.status}
                                                        </Badge>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <div className="py-12 text-center text-muted-foreground">
                                    <div className="flex flex-col items-center justify-center gap-3">
                                        <IconFolder className="size-10 stroke-[1.5] text-muted-foreground/50" />
                                        <div className="flex flex-col gap-0.5">
                                            <p className="text-sm font-semibold text-foreground">
                                                No products linked
                                            </p>
                                            <p className="text-xs">
                                                There are no products associated
                                                with this drop yet.
                                            </p>
                                        </div>
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
