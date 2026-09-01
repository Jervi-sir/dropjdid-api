import { Head, Link, router } from '@inertiajs/react';
import {
    AlertCircle,
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    Filter,
    MapPin,
    Package,
    Phone,
    RefreshCw,
    Search,
    ShoppingBag,
    Store as StoreIcon,
    Truck,
    User,
    XCircle,
} from 'lucide-react';
import React, { useState, useEffect } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface OrderStatusItem {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
    color: string | null;
    icon: string | null;
    sort_order: number;
}

interface ProductItem {
    id: number;
    name: string;
    main_image?: {
        image_url: string;
    } | null;
}

interface OrderItemData {
    id: number;
    product_name: string;
    quantity: number;
    unit_price: number | string;
    total_price: number | string;
    size?: {
        id: number;
        code: string;
        en?: string;
    } | null;
    product?: ProductItem | null;
}

interface StoreItem {
    id: number;
    name: string;
    image_url?: string | null;
}

interface WilayaItem {
    id: number;
    number: string;
    code: string;
    en: string;
    fr: string;
    ar: string;
}

interface OrderRecord {
    id: number;
    order_number: string;
    full_name: string;
    phone_number: string;
    wilaya: string;
    baladiya: string;
    home_address: string;
    delivery_method: string;
    delivery_fees: number | string;
    subtotal: number | string;
    total: number | string;
    order_status_code: string;
    has_claim_issue: boolean;
    claim_issue?: string | null;
    created_at: string;
    updated_at: string;
    store?: StoreItem | null;
    order_status?: OrderStatusItem | null;
    items?: OrderItemData[];
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

interface PageProps {
    orders: PaginatedData<OrderRecord>;
    statuses: OrderStatusItem[];
    statusCounts: Record<string, number>;
    stores: StoreItem[];
    wilayas: WilayaItem[];
    filters: {
        status: string;
        search: string;
        store_id: string;
        wilaya_id: string;
        delivery_method: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/dashboard',
    },
    {
        title: 'Orders Management',
        href: '/admin/orders',
    },
];

export default function ListOrdersPage({
    orders,
    statuses,
    statusCounts,
    stores,
    wilayas,
    filters,
}: PageProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [currentStatus, setCurrentStatus] = useState(filters.status || 'all');
    const [currentStore, setCurrentStore] = useState(filters.store_id || 'all');
    const [currentWilaya, setCurrentWilaya] = useState(
        filters.wilaya_id || 'all',
    );
    const [currentDelivery, setCurrentDelivery] = useState(
        filters.delivery_method || 'all',
    );
    const [updatingOrderId, setUpdatingOrderId] = useState<number | null>(null);

    // Debounce search update
    useEffect(() => {
        const timeout = setTimeout(() => {
            if (search !== (filters.search || '')) {
                applyFilters({ search });
            }
        }, 400);
        return () => clearTimeout(timeout);
    }, [search]);

    const applyFilters = (newFilters: Partial<typeof filters>) => {
        const params: Record<string, string> = {
            status: currentStatus,
            search: search,
            store_id: currentStore,
            wilaya_id: currentWilaya,
            delivery_method: currentDelivery,
            ...newFilters,
        };

        // Clean up 'all' or empty values from URL
        Object.keys(params).forEach((key) => {
            if (params[key] === 'all' || params[key] === '') {
                delete params[key];
            }
        });

        router.get('/admin/orders', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const handleStatusTabChange = (statusCode: string) => {
        setCurrentStatus(statusCode);
        applyFilters({ status: statusCode });
    };

    const handleStoreChange = (val: string) => {
        setCurrentStore(val);
        applyFilters({ store_id: val });
    };

    const handleWilayaChange = (val: string) => {
        setCurrentWilaya(val);
        applyFilters({ wilaya_id: val });
    };

    const handleDeliveryChange = (val: string) => {
        setCurrentDelivery(val);
        applyFilters({ delivery_method: val });
    };

    const handleResetFilters = () => {
        setSearch('');
        setCurrentStatus('all');
        setCurrentStore('all');
        setCurrentWilaya('all');
        setCurrentDelivery('all');
        router.get(
            '/admin/orders',
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleUpdateOrderStatus = (
        orderId: number,
        newStatusCode: string,
    ) => {
        setUpdatingOrderId(orderId);
        router.post(
            `/admin/orders/${orderId}/status`,
            { order_status_code: newStatusCode },
            {
                preserveScroll: true,
                onFinish: () => setUpdatingOrderId(null),
            },
        );
    };

    const formatCurrency = (val: number | string | undefined) => {
        const num = Number(val) || 0;
        return num.toLocaleString('fr-DZ') + ' DZD';
    };

    const getStatusColor = (code: string) => {
        const matched = statuses.find((s) => s.code === code);
        return matched?.color || '#8C94A3';
    };

    return (
        <>
            <Head title="Orders Management" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header Title Section */}
                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Orders Management
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage all store customer orders, monitor logistics,
                            and change fulfillment statuses.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.reload()}
                            className="gap-2"
                        >
                            <RefreshCw className="size-4" />
                            Refresh
                        </Button>
                    </div>
                </div>

                {/* Status Metric Tabs */}
                <div className="flex flex-wrap gap-2 overflow-x-auto pb-1">
                    <button
                        onClick={() => handleStatusTabChange('all')}
                        className={`flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-all ${
                            currentStatus === 'all'
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                        }`}
                    >
                        <span>All Orders</span>
                        <Badge
                            variant="secondary"
                            className="px-1.5 py-0 text-xs"
                        >
                            {statusCounts.all || 0}
                        </Badge>
                    </button>

                    {statuses.map((st) => {
                        const count = statusCounts[st.code] || 0;
                        const isSelected = currentStatus === st.code;
                        return (
                            <button
                                key={st.code}
                                onClick={() => handleStatusTabChange(st.code)}
                                className={`flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-all ${
                                    isSelected
                                        ? 'text-white shadow-sm'
                                        : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                                style={{
                                    backgroundColor: isSelected
                                        ? st.color || '#111'
                                        : undefined,
                                }}
                            >
                                <span
                                    className="size-2 rounded-full"
                                    style={{
                                        backgroundColor: isSelected
                                            ? '#fff'
                                            : st.color || '#888',
                                    }}
                                />
                                <span>{st.en}</span>
                                <Badge
                                    variant="secondary"
                                    className={`px-1.5 py-0 text-xs ${isSelected ? 'bg-white/20 text-white' : ''}`}
                                >
                                    {count}
                                </Badge>
                            </button>
                        );
                    })}
                </div>

                {/* Filter and Search Bar Card */}
                <Card className="border-border/60 shadow-xs">
                    <CardContent className="grid grid-cols-1 gap-3 p-4 md:grid-cols-12 md:items-center">
                        {/* Search Input */}
                        <div className="relative md:col-span-4">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search by order #, name, phone, item..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-9"
                            />
                        </div>

                        {/* Store Selector */}
                        <div className="md:col-span-3">
                            <Select
                                value={currentStore}
                                onValueChange={handleStoreChange}
                            >
                                <SelectTrigger className="w-full">
                                    <div className="flex items-center gap-2 truncate">
                                        <StoreIcon className="size-4 shrink-0 text-muted-foreground" />
                                        <SelectValue placeholder="All Stores" />
                                    </div>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Stores
                                    </SelectItem>
                                    {stores.map((s) => (
                                        <SelectItem
                                            key={s.id}
                                            value={String(s.id)}
                                        >
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Wilaya Selector */}
                        <div className="md:col-span-2">
                            <Select
                                value={currentWilaya}
                                onValueChange={handleWilayaChange}
                            >
                                <SelectTrigger className="w-full">
                                    <div className="flex items-center gap-2 truncate">
                                        <MapPin className="size-4 shrink-0 text-muted-foreground" />
                                        <SelectValue placeholder="All Wilayas" />
                                    </div>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Wilayas
                                    </SelectItem>
                                    {wilayas.map((w) => (
                                        <SelectItem
                                            key={w.id}
                                            value={String(w.id)}
                                        >
                                            {w.number} - {w.en}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Delivery Method Selector */}
                        <div className="md:col-span-2">
                            <Select
                                value={currentDelivery}
                                onValueChange={handleDeliveryChange}
                            >
                                <SelectTrigger className="w-full">
                                    <div className="flex items-center gap-2 truncate">
                                        <Truck className="size-4 shrink-0 text-muted-foreground" />
                                        <SelectValue placeholder="All Deliveries" />
                                    </div>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Delivery Types
                                    </SelectItem>
                                    <SelectItem value="home">
                                        Home Delivery
                                    </SelectItem>
                                    <SelectItem value="desk">
                                        Stop Desk / Office
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Clear Filters Button */}
                        <div className="md:col-span-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={handleResetFilters}
                                className="w-full px-2 text-muted-foreground hover:text-foreground"
                                title="Reset all filters"
                            >
                                Reset
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Orders List Container */}
                <div className="flex flex-col gap-4">
                    {orders.data.length === 0 ? (
                        <Card className="border-border/60 py-12 text-center shadow-xs">
                            <CardContent className="flex flex-col items-center justify-center gap-3">
                                <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                                    <Package className="size-6 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold">
                                    No orders found
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    No customer orders match your current filter
                                    selection. Try changing or clearing your
                                    search filters.
                                </p>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handleResetFilters}
                                    className="mt-2"
                                >
                                    Clear Filters
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        orders.data.map((order) => {
                            const statusColor = getStatusColor(
                                order.order_status_code,
                            );
                            const items = order.items || [];
                            const isUpdating = updatingOrderId === order.id;

                            return (
                                <Card
                                    key={order.id}
                                    className="overflow-hidden border-border/70 shadow-xs transition-all hover:shadow-md"
                                >
                                    {/* Order Card Header */}
                                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border/50 bg-muted/30 px-4 py-3 md:px-6">
                                        <div className="flex flex-wrap items-center gap-3">
                                            <div className="flex items-center gap-2">
                                                <ShoppingBag className="size-4 text-primary" />
                                                <span className="font-mono text-base font-bold text-foreground">
                                                    {order.order_number}
                                                </span>
                                            </div>

                                            <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                <Calendar className="size-3.5" />
                                                <span>
                                                    {new Date(
                                                        order.created_at,
                                                    ).toLocaleString('fr-DZ')}
                                                </span>
                                            </div>

                                            {order.store && (
                                                <Badge
                                                    variant="outline"
                                                    className="flex items-center gap-1 text-xs"
                                                >
                                                    <StoreIcon className="size-3" />
                                                    <span>
                                                        {order.store.name}
                                                    </span>
                                                </Badge>
                                            )}
                                        </div>

                                        {/* Status Changer Select */}
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs font-medium text-muted-foreground">
                                                Status:
                                            </span>
                                            <Select
                                                value={order.order_status_code}
                                                disabled={isUpdating}
                                                onValueChange={(val) =>
                                                    handleUpdateOrderStatus(
                                                        order.id,
                                                        val,
                                                    )
                                                }
                                            >
                                                <SelectTrigger
                                                    className="h-8 min-w-[140px] text-xs font-semibold"
                                                    style={{
                                                        borderColor:
                                                            statusColor,
                                                        color: statusColor,
                                                    }}
                                                >
                                                    <div className="flex items-center gap-2">
                                                        <span
                                                            className="size-2 rounded-full"
                                                            style={{
                                                                backgroundColor:
                                                                    statusColor,
                                                            }}
                                                        />
                                                        <SelectValue />
                                                    </div>
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {statuses.map((st) => (
                                                        <SelectItem
                                                            key={st.code}
                                                            value={st.code}
                                                        >
                                                            <div className="flex items-center gap-2 text-xs">
                                                                <span
                                                                    className="size-2 rounded-full"
                                                                    style={{
                                                                        backgroundColor:
                                                                            st.color ||
                                                                            '#888',
                                                                    }}
                                                                />
                                                                <span>
                                                                    {st.en}
                                                                </span>
                                                                <span className="text-[10px] text-muted-foreground">
                                                                    ({st.fr})
                                                                </span>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    {/* Order Card Body */}
                                    <CardContent className="grid grid-cols-1 gap-4 p-4 md:grid-cols-12 md:p-6">
                                        {/* Customer Details Column */}
                                        <div className="space-y-2 text-sm md:col-span-4">
                                            <div className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Customer Details
                                            </div>
                                            <div className="flex items-center gap-2 font-medium text-foreground">
                                                <User className="size-4 text-muted-foreground" />
                                                <span>{order.full_name}</span>
                                            </div>
                                            <div className="flex items-center gap-2 text-muted-foreground">
                                                <Phone className="size-4" />
                                                <a
                                                    href={`tel:${order.phone_number}`}
                                                    className="font-mono text-sm underline hover:text-primary"
                                                >
                                                    {order.phone_number}
                                                </a>
                                            </div>
                                            <div className="flex items-start gap-2 text-muted-foreground">
                                                <MapPin className="mt-0.5 size-4 shrink-0" />
                                                <div>
                                                    <div className="font-medium text-foreground">
                                                        {order.wilaya}{' '}
                                                        {order.baladiya
                                                            ? `• ${order.baladiya}`
                                                            : ''}
                                                    </div>
                                                    {order.home_address ? (
                                                        <div className="text-xs">
                                                            {order.home_address}
                                                        </div>
                                                    ) : (
                                                        <div className="text-xs text-muted-foreground/80 italic">
                                                            Stop desk pickup
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2 pt-1 text-xs">
                                                <Truck className="size-3.5 text-muted-foreground" />
                                                <span className="text-foreground capitalize">
                                                    {order.delivery_method ===
                                                    'desk'
                                                        ? 'Delivery Office (Stop Desk)'
                                                        : 'Home Delivery'}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Ordered Products Items Column */}
                                        <div className="space-y-2 md:col-span-5">
                                            <div className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Ordered Items ({items.length})
                                            </div>
                                            <div className="divide-y divide-border/60 rounded-md border p-2">
                                                {items.map((item) => (
                                                    <div
                                                        key={item.id}
                                                        className="flex items-center justify-between gap-3 py-1.5 first:pt-0 last:pb-0"
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            {item.product
                                                                ?.main_image
                                                                ?.image_url ? (
                                                                <img
                                                                    src={
                                                                        item
                                                                            .product
                                                                            .main_image
                                                                            .image_url
                                                                    }
                                                                    alt={
                                                                        item.product_name
                                                                    }
                                                                    className="size-8 rounded object-cover"
                                                                />
                                                            ) : (
                                                                <div className="flex size-8 items-center justify-center rounded bg-muted">
                                                                    <Package className="size-4 text-muted-foreground" />
                                                                </div>
                                                            )}
                                                            <div>
                                                                <div className="line-clamp-1 text-xs font-medium text-foreground">
                                                                    {
                                                                        item.product_name
                                                                    }
                                                                </div>
                                                                <div className="text-[11px] text-muted-foreground">
                                                                    Qty:{' '}
                                                                    <span className="font-semibold text-foreground">
                                                                        {
                                                                            item.quantity
                                                                        }
                                                                    </span>
                                                                    {item.size
                                                                        ?.code &&
                                                                        ` • Size: ${item.size.code}`}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div className="font-mono text-xs font-semibold text-foreground">
                                                            {formatCurrency(
                                                                item.total_price,
                                                            )}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        {/* Financial Summary Breakdown Column */}
                                        <div className="space-y-2 rounded-lg bg-muted/40 p-3 text-sm md:col-span-3">
                                            <div className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Payment Summary
                                            </div>
                                            <div className="flex justify-between text-xs text-muted-foreground">
                                                <span>Subtotal:</span>
                                                <span className="font-mono font-medium text-foreground">
                                                    {formatCurrency(
                                                        order.subtotal,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex justify-between text-xs text-muted-foreground">
                                                <span>Delivery:</span>
                                                <span className="font-mono font-medium text-foreground">
                                                    {formatCurrency(
                                                        order.delivery_fees,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex justify-between border-t border-border/60 pt-2 text-sm font-bold text-foreground">
                                                <span>Total:</span>
                                                <span className="font-mono text-base text-primary">
                                                    {formatCurrency(
                                                        order.total,
                                                    )}
                                                </span>
                                            </div>

                                            {order.has_claim_issue && (
                                                <div className="mt-2 flex items-start gap-1.5 rounded bg-destructive/10 p-2 text-xs text-destructive">
                                                    <AlertCircle className="size-4 shrink-0" />
                                                    <span>
                                                        {order.claim_issue ||
                                                            'Claim reported'}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })
                    )}
                </div>

                {/* Pagination Controls */}
                {orders.total > orders.per_page && (
                    <div className="flex flex-col items-center justify-between gap-3 pt-2 sm:flex-row">
                        <div className="text-xs text-muted-foreground">
                            Showing{' '}
                            <span className="font-semibold text-foreground">
                                {orders.from || 0}
                            </span>{' '}
                            to{' '}
                            <span className="font-semibold text-foreground">
                                {orders.to || 0}
                            </span>{' '}
                            of{' '}
                            <span className="font-semibold text-foreground">
                                {orders.total}
                            </span>{' '}
                            orders
                        </div>

                        <div className="flex items-center gap-1">
                            {orders.links.map((link, i) => {
                                const isPageNum =
                                    !link.label.includes('Previous') &&
                                    !link.label.includes('Next');
                                return link.url ? (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        preserveScroll
                                        className={`flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-xs font-medium transition-colors ${
                                            link.active
                                                ? 'bg-primary font-bold text-primary-foreground shadow-xs'
                                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        key={i}
                                        className="flex h-8 min-w-8 items-center justify-center px-2 text-xs text-muted-foreground/40"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
