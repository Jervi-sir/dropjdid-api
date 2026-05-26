import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ShowStoreController from '@/actions/App/Http/Controllers/Admin/Stores/ShowStoreController';
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
import {
    IconCalendar,
    IconShoppingBag,
    IconArrowLeft,
    IconCheck,
    IconMail,
    IconPhone,
    IconLock,
    IconMapPin,
    IconUser,
    IconCircleCheck,
    IconSearch,
    IconChevronRight,
    IconEye,
} from '@tabler/icons-react';

interface Product {
    id: number;
    name: string;
    description: string | null;
    original_price: number;
    show_price: number;
    status: string;
    category: {
        id: number;
        en: string;
    } | null;
    image: string | null;
    created_at: string;
}

interface Store {
    id: number;
    store_name: string;
    phone_number: string;
    password_plaintext: string | null;
    logo: string | null;
    description: string | null;
    balance: number;
    status: string;
    is_verified: boolean;
    user: {
        id: number;
        full_name: string;
        username: string;
        email: string;
    } | null;
    wilaya: {
        id: number;
        name: string;
    } | null;
    products: Product[];
    created_at: string;
}

interface StoreShowProps {
    store: Store;
    statuses: Record<number, string>;
}

export default function StoreShow({ store, statuses }: StoreShowProps) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        status: store.status,
        is_verified: store.is_verified,
    });

    const [productSearch, setProductSearch] = React.useState('');
    const [productStatusFilter, setProductStatusFilter] = React.useState('all');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(ShowStoreController.update.url(store.id), {
            preserveScroll: true,
        });
    };

    const getStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'active':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Active
                    </Badge>
                );
            case 'suspended':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100/80 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Suspended
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100/80 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getProductStatusBadge = (status: string) => {
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
                    <Badge className="border border-zinc-500/20 bg-zinc-100 text-zinc-700 hover:bg-zinc-200/80 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
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

    // Filter products list locally in the page
    const filteredProducts = React.useMemo(() => {
        return store.products.filter((product) => {
            const matchesSearch =
                product.name
                    .toLowerCase()
                    .includes(productSearch.toLowerCase()) ||
                product.description
                    ?.toLowerCase()
                    .includes(productSearch.toLowerCase()) ||
                product.category?.en
                    .toLowerCase()
                    .includes(productSearch.toLowerCase());

            const matchesStatus =
                productStatusFilter === 'all' ||
                product.status.toLowerCase() ===
                    productStatusFilter.toLowerCase();

            return matchesSearch && matchesStatus;
        });
    }, [store.products, productSearch, productStatusFilter]);

    return (
        <>
            <Head title={`Audit Store - ${store.store_name}`} />
            <div className="mx-auto flex max-w-7xl flex-col gap-6 p-4 lg:p-8">
                {/* Back Link */}
                <div>
                    <Link
                        href="/admin/stores"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <IconArrowLeft className="size-4" />
                        <span>Back to Stores</span>
                    </Link>
                </div>

                {/* Store Title Banner */}
                <div className="flex flex-col justify-between gap-4 border-b pb-6 md:flex-row md:items-center">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center">
                        <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border bg-primary/10 text-xl font-bold text-primary shadow-sm">
                            {store.logo ? (
                                <img
                                    src={store.logo}
                                    alt={store.store_name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                store.store_name?.charAt(0).toUpperCase()
                            )}
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="flex items-center gap-2 text-3xl font-extrabold tracking-tight text-foreground">
                                    {store.store_name}
                                    {store.is_verified && (
                                        <IconCircleCheck className="size-6 shrink-0 text-cyan-600 dark:text-cyan-400" />
                                    )}
                                </h1>
                                {getStatusBadge(store.status)}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Store ID #{store.id} • Registered on{' '}
                                {formatDate(store.created_at)}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Main 2-Column Dashboard */}
                <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    {/* Left Column: Store Profiles & Status Modifiers (lg:col-span-4) */}
                    <div className="flex flex-col gap-6 lg:col-span-4">
                        {/* Profile Overview Card */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconShoppingBag className="size-5 text-muted-foreground" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Store Profile
                                </h3>
                            </div>
                            <div className="flex flex-col gap-3 text-xs leading-relaxed text-muted-foreground">
                                <p className="mb-1 text-foreground italic">
                                    {store.description ||
                                        'No description provided for this store.'}
                                </p>
                                <div className="flex items-center gap-2 border-t pt-3 text-muted-foreground">
                                    <IconUser className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="font-bold text-foreground">
                                        Owner: {store.user?.full_name} (@
                                        {store.user?.username})
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconMail className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="truncate font-semibold text-foreground">
                                        {store.user?.email || 'No owner email'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconPhone className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="font-semibold text-foreground">
                                        {store.phone_number ||
                                            'No phone number'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <IconMapPin className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="font-semibold text-foreground">
                                        {store.wilaya?.name ||
                                            'No location set'}
                                    </span>
                                </div>
                                {store.password_plaintext && (
                                    <>
                                        <hr className="my-2 border-muted/50" />
                                        <div className="flex items-center gap-2 text-muted-foreground">
                                            <IconLock className="size-4 shrink-0 text-amber-600 dark:text-amber-500" />
                                            <div className="flex items-center gap-1.5">
                                                <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                    Credentials:
                                                </span>
                                                <span className="rounded-md border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 font-mono text-[11px] text-amber-700 select-all dark:text-amber-400">
                                                    {store.password_plaintext}
                                                </span>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Wallet Balance Card */}
                        <div className="flex flex-col justify-between rounded-2xl border bg-card p-5 shadow-xs">
                            <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                Operational Balance
                            </span>
                            <span className="mt-2 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                                ${store.balance.toFixed(2)}
                            </span>
                        </div>

                        {/* Moderation Controls Form */}
                        <form
                            onSubmit={handleSubmit}
                            className="flex flex-col gap-5 rounded-2xl border bg-card p-5 shadow-xs"
                        >
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconLock className="size-5 text-primary" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Moderation panel
                                </h3>
                            </div>

                            {/* Status Selector */}
                            <div className="flex flex-col gap-2">
                                <label className="text-xs font-bold text-muted-foreground">
                                    Operational Status
                                </label>
                                <Select
                                    value={data.status}
                                    onValueChange={(val) =>
                                        setData('status', val)
                                    }
                                >
                                    <SelectTrigger className="h-10 w-full border-input bg-background">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="active">
                                            Active
                                        </SelectItem>
                                        <SelectItem value="suspended">
                                            Suspended
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Verified Merchant Badge Button Toggle */}
                            <button
                                type="button"
                                onClick={() =>
                                    setData('is_verified', !data.is_verified)
                                }
                                className={`flex items-center justify-between rounded-xl border p-3.5 text-left transition-all ${
                                    data.is_verified
                                        ? 'border-cyan-500/40 bg-cyan-500/5 dark:bg-cyan-500/10'
                                        : 'border-muted bg-muted/5 hover:border-foreground/20'
                                }`}
                            >
                                <div className="flex flex-col gap-0.5">
                                    <span className="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                        <IconCircleCheck className="size-4 text-cyan-600 dark:text-cyan-400" />
                                        <span>Verified Badge</span>
                                    </span>
                                    <span className="text-[10px] text-muted-foreground">
                                        Display badge on product pages
                                    </span>
                                </div>
                                <div
                                    className={`flex size-5 shrink-0 items-center justify-center rounded-full border transition-all ${
                                        data.is_verified
                                            ? 'border-cyan-600 bg-cyan-600 text-white'
                                            : 'border-muted-foreground/35 bg-background'
                                    }`}
                                >
                                    {data.is_verified && (
                                        <IconCheck className="size-3.5 stroke-[2.5]" />
                                    )}
                                </div>
                            </button>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="mt-1 h-10 w-full"
                            >
                                {processing
                                    ? 'Saving changes...'
                                    : 'Save Settings'}
                            </Button>

                            {recentlySuccessful && (
                                <div className="flex items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <IconCheck className="size-4 shrink-0" />
                                    <span>Store changes synchronized</span>
                                </div>
                            )}
                        </form>
                    </div>

                    {/* Right Column: searchable associated products (lg:col-span-8) */}
                    <div className="flex flex-col gap-6 lg:col-span-8">
                        {/* Products Dashboard Table Container */}
                        <div className="flex flex-col overflow-hidden rounded-2xl border bg-card shadow-xs">
                            {/* Products Controls Header */}
                            <div className="flex flex-col gap-4 border-b bg-muted/15 p-5">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-sm font-extrabold tracking-wider text-foreground uppercase">
                                        Associated Catalog (
                                        {filteredProducts.length})
                                    </h3>
                                </div>

                                {/* Filter and search bar inside details */}
                                <div className="flex flex-col items-center gap-3 sm:flex-row">
                                    <div className="relative w-full flex-1">
                                        <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search store products by name or category..."
                                            value={productSearch}
                                            onChange={(e) =>
                                                setProductSearch(e.target.value)
                                            }
                                            className="h-10 w-full bg-background pl-9"
                                        />
                                    </div>

                                    <div className="w-full sm:w-36">
                                        <Select
                                            value={productStatusFilter}
                                            onValueChange={
                                                setProductStatusFilter
                                            }
                                        >
                                            <SelectTrigger className="h-10 bg-background">
                                                <SelectValue placeholder="All Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">
                                                    All Status
                                                </SelectItem>
                                                <SelectItem value="published">
                                                    Published
                                                </SelectItem>
                                                <SelectItem value="draft">
                                                    Draft
                                                </SelectItem>
                                                <SelectItem value="archived">
                                                    Archived
                                                </SelectItem>
                                                <SelectItem value="rejected">
                                                    Rejected
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>

                            {/* Store Products Table */}
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader className="border-b bg-muted/5">
                                        <TableRow>
                                            <TableHead className="w-[60px] py-4 pl-6">
                                                Thumbnail
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Product Name
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Category
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Original Price
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Show Price
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Status
                                            </TableHead>
                                            <TableHead className="py-4">
                                                Created
                                            </TableHead>
                                            <TableHead className="w-[80px] py-4 pr-6 text-right">
                                                Action
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredProducts.length > 0 ? (
                                            filteredProducts.map((product) => (
                                                <TableRow
                                                    key={product.id}
                                                    className="group/row transition-colors hover:bg-muted/5"
                                                >
                                                    <TableCell className="py-4 pl-6">
                                                        <div className="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted/10 text-xs font-bold text-primary shadow-inner">
                                                            {product.image ? (
                                                                <img
                                                                    src={
                                                                        product.image
                                                                    }
                                                                    alt={
                                                                        product.name
                                                                    }
                                                                    className="h-full w-full object-cover"
                                                                />
                                                            ) : (
                                                                product?.name
                                                                    ?.charAt(0)
                                                                    ?.toUpperCase()
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        <div className="flex flex-col">
                                                            <span className="text-sm font-bold text-foreground">
                                                                {product.name}
                                                            </span>
                                                            <span className="mt-0.5 max-w-[200px] truncate text-[10px] text-muted-foreground">
                                                                {product.description ||
                                                                    'No description provided.'}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {product.category ? (
                                                            <Badge
                                                                variant="outline"
                                                                className="px-2.5 text-[10px]"
                                                            >
                                                                {
                                                                    product
                                                                        .category
                                                                        .en
                                                                }
                                                            </Badge>
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground">
                                                                None
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                                                        $
                                                        {product.original_price.toFixed(
                                                            2,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-xs font-bold text-foreground">
                                                        $
                                                        {product.show_price.toFixed(
                                                            2,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {getProductStatusBadge(
                                                            product.status,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                                                        {formatDate(
                                                            product.created_at,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4 pr-6 text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="xs"
                                                            className="h-7 px-2 text-muted-foreground hover:text-foreground"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/products/${product.id}`}
                                                            >
                                                                <IconEye className="size-4" />
                                                            </Link>
                                                        </Button>
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
                                                        <IconShoppingBag className="size-8 stroke-[1.5] text-muted-foreground/55" />
                                                        <div className="flex flex-col gap-0.5">
                                                            <p className="text-sm font-semibold text-foreground">
                                                                No products
                                                                found
                                                            </p>
                                                            <p className="text-xs">
                                                                No store
                                                                products match
                                                                your current
                                                                keyword or
                                                                status filters.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
