import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowRight,
    Boxes,
    Building2,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    Clock,
    Eye,
    Filter,
    Layers,
    Package,
    PackageCheck,
    RefreshCw,
    Search,
    Send,
    ShoppingCart,
    Store as StoreIcon,
    Truck,
    X,
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
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

// Types
interface StoreItem {
    id: number;
    name: string;
    image_url?: string | null;
}

interface ProductItem {
    id: number;
    name: string;
    main_image?: {
        image_url: string;
    } | null;
}

interface SupplyRequestItemData {
    id: number;
    product_name: string;
    requested_quantity: number;
    fulfilled_quantity: number;
    received_quantity: number;
    size?: { id: number; code: string } | null;
    product?: ProductItem | null;
}

interface SupplyRequestData {
    id: number;
    reference_code: string;
    store_id: number;
    status: string;
    tracking_number: string | null;
    courier_name: string | null;
    notes: string | null;
    sent_at: string | null;
    shipped_at: string | null;
    received_at: string | null;
    items_count?: number;
    order_items_count?: number;
    store: StoreItem;
    items: SupplyRequestItemData[];
}

interface PendingDemandSize {
    size: string;
    total_quantity: number;
    order_item_ids: number[];
    order_numbers: string[];
}

interface PendingDemandProduct {
    product_id: number;
    product_name: string;
    image_url?: string | null;
    total_quantity: number;
    sizes: PendingDemandSize[];
    order_item_ids: number[];
}

interface PendingDemandItem {
    id: number;
    order_id: number;
    order_number?: string;
    product_id: number;
    product_name: string;
    image_url?: string | null;
    size?: string | null;
    quantity: number;
    created_at: string;
}

interface StoreDemandGroup {
    store: StoreItem;
    total_items_count: number;
    affected_orders_count: number;
    products?: PendingDemandProduct[];
    order_items: PendingDemandItem[];
}

interface ReadyToBoxOrder {
    id: number;
    order_number: string;
    full_name: string;
    phone_number: string;
    wilaya: string;
    baladiya: string;
    delivery_method: string;
    total: string | number;
    store: StoreItem;
    order_status: { code: string; en: string; color: string };
    items: Array<{
        id: number;
        product_name: string;
        quantity: number;
        fulfillment_status: string;
        size?: { code: string } | null;
        product?: ProductItem | null;
    }>;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Props {
    supplyRequests: PaginatedData<SupplyRequestData>;
    storesWithDemands: StoreDemandGroup[];
    readyToBoxOrders: PaginatedData<ReadyToBoxOrder>;
    stores: StoreItem[];
    filters: {
        tab: string;
        status: string;
        search: string;
        store_id: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Supply Requests', href: '/admin/supply-requests' },
];

export default function IndexSupplyRequestsPage({
    supplyRequests,
    storesWithDemands,
    readyToBoxOrders,
    stores,
    filters,
}: Props) {
    const [activeTab, setActiveTab] = useState<
        'requests' | 'demands' | 'ready_to_box'
    >((filters.tab as any) || 'requests');
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [storeFilter, setStoreFilter] = useState(filters.store_id || 'all');

    // Create Modal State
    const [selectedStoreDemand, setSelectedStoreDemand] =
        useState<StoreDemandGroup | null>(null);
    const [selectedItemIds, setSelectedItemIds] = useState<number[]>([]);
    const [requestNotes, setRequestNotes] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Receive at Hub Modal State
    const [receivingRequest, setReceivingRequest] =
        useState<SupplyRequestData | null>(null);
    const [receivedQuantities, setReceivedQuantities] = useState<
        Record<number, number>
    >({});

    // Status Update Modal
    const [editingRequest, setEditingRequest] =
        useState<SupplyRequestData | null>(null);
    const [editStatus, setEditStatus] = useState('');
    const [trackingNumber, setTrackingNumber] = useState('');
    const [courierName, setCourierName] = useState('');

    const applyFilters = (newFilters: Partial<typeof filters>) => {
        router.get(
            '/admin/supply-requests',
            {
                tab: activeTab,
                search,
                status: statusFilter,
                store_id: storeFilter,
                ...newFilters,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleTabChange = (tab: 'requests' | 'demands' | 'ready_to_box') => {
        setActiveTab(tab);
        applyFilters({ tab });
    };

    // Open Batch Creation Modal
    const openCreateBatchModal = (group: StoreDemandGroup) => {
        setSelectedStoreDemand(group);
        setSelectedItemIds(group.order_items.map((i) => i.id));
        setRequestNotes('');
    };

    const toggleSelectItem = (id: number) => {
        setSelectedItemIds((prev) =>
            prev.includes(id)
                ? prev.filter((item) => item !== id)
                : [...prev, id],
        );
    };

    const toggleProductSelection = (product: PendingDemandProduct) => {
        const allProductItemIds = product.order_item_ids;
        const allSelected = allProductItemIds.every((id) =>
            selectedItemIds.includes(id),
        );

        if (allSelected) {
            setSelectedItemIds((prev) =>
                prev.filter((id) => !allProductItemIds.includes(id)),
            );
        } else {
            setSelectedItemIds((prev) =>
                Array.from(new Set([...prev, ...allProductItemIds])),
            );
        }
    };

    const toggleSizeSelection = (size: PendingDemandSize) => {
        const sizeItemIds = size.order_item_ids;
        const allSelected = sizeItemIds.every((id) =>
            selectedItemIds.includes(id),
        );

        if (allSelected) {
            setSelectedItemIds((prev) =>
                prev.filter((id) => !sizeItemIds.includes(id)),
            );
        } else {
            setSelectedItemIds((prev) =>
                Array.from(new Set([...prev, ...sizeItemIds])),
            );
        }
    };

    const handleCreateSupplyRequest = () => {
        if (!selectedStoreDemand || selectedItemIds.length === 0) return;
        setIsSubmitting(true);

        router.post(
            '/admin/supply-requests',
            {
                store_id: selectedStoreDemand.store.id,
                order_item_ids: selectedItemIds,
                notes: requestNotes,
            },
            {
                onSuccess: () => {
                    setSelectedStoreDemand(null);
                    setActiveTab('requests');
                },
                onFinish: () => setIsSubmitting(false),
            },
        );
    };

    // Open Receive Modal
    const openReceiveModal = (req: SupplyRequestData) => {
        setReceivingRequest(req);
        const initial: Record<number, number> = {};
        req.items.forEach((item) => {
            initial[item.id] = item.requested_quantity;
        });
        setReceivedQuantities(initial);
    };

    const handleReceiveSubmit = () => {
        if (!receivingRequest) return;
        setIsSubmitting(true);

        const itemsPayload = Object.entries(receivedQuantities).map(
            ([itemId, qty]) => ({
                item_id: parseInt(itemId, 10),
                received_quantity: qty,
            }),
        );

        router.post(
            `/admin/supply-requests/${receivingRequest.id}/receive`,
            { received_items: itemsPayload },
            {
                onSuccess: () => setReceivingRequest(null),
                onFinish: () => setIsSubmitting(false),
            },
        );
    };

    // Open Status Edit Modal
    const openEditModal = (req: SupplyRequestData) => {
        setEditingRequest(req);
        setEditStatus(req.status);
        setTrackingNumber(req.tracking_number || '');
        setCourierName(req.courier_name || '');
    };

    const handleStatusSubmit = () => {
        if (!editingRequest) return;
        setIsSubmitting(true);

        router.post(
            `/admin/supply-requests/${editingRequest.id}/status`,
            {
                status: editStatus,
                tracking_number: trackingNumber,
                courier_name: courierName,
            },
            {
                onSuccess: () => setEditingRequest(null),
                onFinish: () => setIsSubmitting(false),
            },
        );
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'draft':
                return (
                    <Badge
                        variant="outline"
                        className="bg-slate-50 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300"
                    >
                        Draft
                    </Badge>
                );
            case 'sent':
                return (
                    <Badge className="bg-blue-500 hover:bg-blue-600">
                        Sent to Store
                    </Badge>
                );
            case 'preparing':
                return (
                    <Badge className="bg-amber-500 hover:bg-amber-600">
                        Preparing
                    </Badge>
                );
            case 'shipped_to_hub':
                return (
                    <Badge className="bg-purple-500 hover:bg-purple-600">
                        Shipped to Hub
                    </Badge>
                );
            case 'received_at_hub':
                return (
                    <Badge className="bg-emerald-500 hover:bg-emerald-600">
                        Received in Hub
                    </Badge>
                );
            case 'completed':
                return (
                    <Badge className="bg-green-600 hover:bg-green-700">
                        Completed
                    </Badge>
                );
            case 'cancelled':
                return <Badge variant="destructive">Cancelled</Badge>;
            default:
                return <Badge>{status}</Badge>;
        }
    };

    return (
        <>
            <Head title="Supply Requests Management" />

            <div className="flex-1 space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Supply Requests & Hub Consolidation
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Batch customer orders by product & size, dispatch
                            store supply requests, and check in goods at the
                            central hub.
                        </p>
                    </div>
                </div>

                {/* Tabs Navigation */}
                <div className="flex border-b border-border">
                    <button
                        onClick={() => handleTabChange('requests')}
                        className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors ${
                            activeTab === 'requests'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Truck className="h-4 w-4" />
                        Supply Requests ({supplyRequests.total})
                    </button>
                    <button
                        onClick={() => handleTabChange('demands')}
                        className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors ${
                            activeTab === 'demands'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Layers className="h-4 w-4" />
                        Pending Store Demands ({storesWithDemands.length}{' '}
                        stores)
                    </button>
                    <button
                        onClick={() => handleTabChange('ready_to_box')}
                        className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors ${
                            activeTab === 'ready_to_box'
                                ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <PackageCheck className="h-4 w-4 text-emerald-500" />
                        Ready to Box Orders ({readyToBoxOrders.total})
                    </button>
                </div>

                {/* TAB 1: SUPPLY REQUESTS */}
                {activeTab === 'requests' && (
                    <div className="space-y-4">
                        {/* Filters */}
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
                            <div className="relative">
                                <Search className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search ref, courier, tracking..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) =>
                                        e.key === 'Enter' &&
                                        applyFilters({ search })
                                    }
                                    className="pl-8"
                                />
                            </div>

                            <Select
                                value={statusFilter}
                                onValueChange={(val) => {
                                    setStatusFilter(val);
                                    applyFilters({ status: val });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Statuses
                                    </SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">
                                        Sent to Store
                                    </SelectItem>
                                    <SelectItem value="preparing">
                                        Preparing
                                    </SelectItem>
                                    <SelectItem value="shipped_to_hub">
                                        Shipped to Hub
                                    </SelectItem>
                                    <SelectItem value="received_at_hub">
                                        Received at Hub
                                    </SelectItem>
                                    <SelectItem value="completed">
                                        Completed
                                    </SelectItem>
                                    <SelectItem value="cancelled">
                                        Cancelled
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Select
                                value={storeFilter}
                                onValueChange={(val) => {
                                    setStoreFilter(val);
                                    applyFilters({ store_id: val });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="All Stores" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        All Stores
                                    </SelectItem>
                                    {stores.map((s) => (
                                        <SelectItem
                                            key={s.id}
                                            value={s.id.toString()}
                                        >
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Button
                                variant="outline"
                                onClick={() => {
                                    setSearch('');
                                    setStatusFilter('all');
                                    setStoreFilter('all');
                                    applyFilters({
                                        search: '',
                                        status: 'all',
                                        store_id: 'all',
                                    });
                                }}
                                className="flex items-center gap-2"
                            >
                                <RefreshCw className="h-4 w-4" /> Reset Filters
                            </Button>
                        </div>

                        {/* Requests Table / Cards */}
                        {supplyRequests.data.length === 0 ? (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center p-12 text-center">
                                    <Truck className="mb-3 h-12 w-12 text-muted-foreground/50" />
                                    <h3 className="text-base font-semibold">
                                        No supply requests found
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Head over to the{' '}
                                        <strong>"Pending Store Demands"</strong>{' '}
                                        tab to batch items and create your first
                                        supply request.
                                    </p>
                                </CardContent>
                            </Card>
                        ) : (
                            <div className="space-y-3">
                                {supplyRequests.data.map((req) => (
                                    <Card
                                        key={req.id}
                                        className="overflow-hidden border border-border/80 transition-all hover:border-border"
                                    >
                                        <div className="flex flex-col justify-between gap-4 p-4 sm:p-5 md:flex-row md:items-center">
                                            <div className="space-y-1.5">
                                                <div className="flex items-center gap-3">
                                                    <span className="font-mono text-base font-bold text-foreground">
                                                        {req.reference_code}
                                                    </span>
                                                    {getStatusBadge(req.status)}
                                                </div>
                                                <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                                    <span className="flex items-center gap-1 font-medium text-foreground">
                                                        <StoreIcon className="h-3.5 w-3.5" />
                                                        {req.store?.name}
                                                    </span>
                                                    <span>•</span>
                                                    <span>
                                                        {req.items?.length || 0}{' '}
                                                        variant line(s)
                                                    </span>
                                                    <span>•</span>
                                                    <span>
                                                        {req.order_items_count ||
                                                            0}{' '}
                                                        customer orders linked
                                                    </span>
                                                    {req.courier_name && (
                                                        <>
                                                            <span>•</span>
                                                            <span className="font-medium text-primary">
                                                                Courier:{' '}
                                                                {
                                                                    req.courier_name
                                                                }{' '}
                                                                {req.tracking_number
                                                                    ? `(#${req.tracking_number})`
                                                                    : ''}
                                                            </span>
                                                        </>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="flex flex-wrap items-center gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        openEditModal(req)
                                                    }
                                                >
                                                    Update Status
                                                </Button>

                                                {[
                                                    'shipped_to_hub',
                                                    'preparing',
                                                    'sent',
                                                ].includes(req.status) && (
                                                    <Button
                                                        size="sm"
                                                        className="bg-emerald-600 text-white hover:bg-emerald-700"
                                                        onClick={() =>
                                                            openReceiveModal(
                                                                req,
                                                            )
                                                        }
                                                    >
                                                        <PackageCheck className="mr-1.5 h-4 w-4" />
                                                        Check In at Hub
                                                    </Button>
                                                )}
                                            </div>
                                        </div>

                                        {/* Items breakdown preview */}
                                        <div className="border-t border-border/60 bg-muted/40 px-4 py-3">
                                            <div className="mb-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Requested Variants:
                                            </div>
                                            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
                                                {req.items.map((item) => (
                                                    <div
                                                        key={item.id}
                                                        className="flex items-center justify-between rounded border border-border/50 bg-card p-2 text-xs"
                                                    >
                                                        <div className="mr-2 truncate font-medium">
                                                            {item.product_name}{' '}
                                                            {item.size
                                                                ? `(${item.size.code})`
                                                                : ''}
                                                        </div>
                                                        <div className="flex shrink-0 items-center gap-1 font-mono">
                                                            <span className="text-muted-foreground">
                                                                Req:
                                                            </span>
                                                            <span className="font-bold">
                                                                {
                                                                    item.requested_quantity
                                                                }
                                                            </span>
                                                            <span className="text-muted-foreground">
                                                                | Rec:
                                                            </span>
                                                            <span
                                                                className={
                                                                    item.received_quantity >=
                                                                    item.requested_quantity
                                                                        ? 'font-bold text-emerald-600'
                                                                        : 'font-bold text-amber-600'
                                                                }
                                                            >
                                                                {
                                                                    item.received_quantity
                                                                }
                                                            </span>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* TAB 2: PENDING DEMANDS (GROUPED BY PRODUCT & SIZES) */}
                {activeTab === 'demands' && (
                    <div className="space-y-4">
                        {storesWithDemands.length === 0 ? (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center p-12 text-center">
                                    <CheckCircle2 className="mb-3 h-12 w-12 text-emerald-500" />
                                    <h3 className="text-base font-semibold">
                                        No pending demands
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        All current customer order items have
                                        already been batched into supply
                                        requests!
                                    </p>
                                </CardContent>
                            </Card>
                        ) : (
                            <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                {storesWithDemands.map((group) => (
                                    <Card
                                        key={group.store.id}
                                        className="flex flex-col justify-between border-border/80 shadow-sm"
                                    >
                                        <CardHeader className="border-b border-border/60 bg-muted/20 pb-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-3">
                                                    <div className="rounded-lg bg-primary/10 p-2.5 text-primary">
                                                        <StoreIcon className="h-5 w-5" />
                                                    </div>
                                                    <div>
                                                        <CardTitle className="text-base font-bold">
                                                            {group.store.name}
                                                        </CardTitle>
                                                        <CardDescription>
                                                            {
                                                                group.affected_orders_count
                                                            }{' '}
                                                            customer orders
                                                            affected
                                                        </CardDescription>
                                                    </div>
                                                </div>
                                                <Badge
                                                    variant="secondary"
                                                    className="px-3 py-1 text-sm font-bold"
                                                >
                                                    {group.total_items_count}{' '}
                                                    pcs total
                                                </Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-3 p-4">
                                            <div className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                Demands by Product & Size:
                                            </div>

                                            {/* Products Breakdown List */}
                                            <div className="max-h-72 space-y-2.5 overflow-y-auto pr-1">
                                                {group.products &&
                                                group.products.length > 0
                                                    ? group.products.map(
                                                          (prod) => (
                                                              <div
                                                                  key={
                                                                      prod.product_id
                                                                  }
                                                                  className="space-y-2 rounded-lg border border-border/60 bg-muted/40 p-3"
                                                              >
                                                                  <div className="flex items-center justify-between">
                                                                      <div className="flex items-center gap-2">
                                                                          {prod.image_url ? (
                                                                              <img
                                                                                  src={
                                                                                      prod.image_url
                                                                                  }
                                                                                  alt=""
                                                                                  className="h-7 w-7 rounded border object-cover"
                                                                              />
                                                                          ) : (
                                                                              <Package className="h-4 w-4 text-muted-foreground" />
                                                                          )}
                                                                          <span className="text-sm font-semibold text-foreground">
                                                                              {
                                                                                  prod.product_name
                                                                              }
                                                                          </span>
                                                                      </div>
                                                                      <span className="rounded border bg-background px-2 py-0.5 font-mono text-xs font-bold">
                                                                          Total:{' '}
                                                                          {
                                                                              prod.total_quantity
                                                                          }{' '}
                                                                          pcs
                                                                      </span>
                                                                  </div>

                                                                  {/* Sizes Pills */}
                                                                  <div className="flex flex-wrap gap-1.5 pt-1">
                                                                      {prod.sizes.map(
                                                                          (
                                                                              s,
                                                                          ) => (
                                                                              <div
                                                                                  key={
                                                                                      s.size
                                                                                  }
                                                                                  className="flex items-center gap-1.5 rounded border border-border/80 bg-background px-2 py-1 text-xs shadow-2xs"
                                                                              >
                                                                                  <span className="font-medium text-muted-foreground">
                                                                                      Size{' '}
                                                                                      {
                                                                                          s.size
                                                                                      }
                                                                                      :
                                                                                  </span>
                                                                                  <span className="font-mono font-bold text-foreground">
                                                                                      {
                                                                                          s.total_quantity
                                                                                      }{' '}
                                                                                      pcs
                                                                                  </span>
                                                                              </div>
                                                                          ),
                                                                      )}
                                                                  </div>
                                                              </div>
                                                          ),
                                                      )
                                                    : group.order_items.map(
                                                          (oi) => (
                                                              <div
                                                                  key={oi.id}
                                                                  className="flex items-center justify-between rounded border border-border/40 bg-muted/40 px-2 py-1 text-xs"
                                                              >
                                                                  <span className="mr-2 truncate">
                                                                      {
                                                                          oi.product_name
                                                                      }{' '}
                                                                      (
                                                                      {oi.size ||
                                                                          'Std'}
                                                                      )
                                                                  </span>
                                                                  <span className="font-mono font-medium">
                                                                      x
                                                                      {
                                                                          oi.quantity
                                                                      }
                                                                  </span>
                                                              </div>
                                                          ),
                                                      )}
                                            </div>

                                            <Button
                                                className="mt-4 flex w-full items-center justify-center gap-2"
                                                onClick={() =>
                                                    openCreateBatchModal(group)
                                                }
                                            >
                                                <Send className="h-4 w-4" />
                                                Batch & Generate Supply Request
                                            </Button>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* TAB 3: READY TO BOX ORDERS */}
                {activeTab === 'ready_to_box' && (
                    <div className="space-y-4">
                        <div className="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/30">
                            <PackageCheck className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            <div className="text-sm">
                                <span className="font-semibold text-emerald-900 dark:text-emerald-300">
                                    Fulfillment Ready Orders:{' '}
                                </span>
                                <span className="text-emerald-700 dark:text-emerald-400">
                                    All items for the customer orders below have
                                    successfully arrived at the Hub from their
                                    respective stores. You can now box them up
                                    and dispatch them to the buyer.
                                </span>
                            </div>
                        </div>

                        {readyToBoxOrders.data.length === 0 ? (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center p-12 text-center">
                                    <Package className="mb-3 h-12 w-12 text-muted-foreground/50" />
                                    <h3 className="text-base font-semibold">
                                        No orders ready to box yet
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Once all items for an order arrive at
                                        the central hub, they will appear here
                                        ready for shipping.
                                    </p>
                                </CardContent>
                            </Card>
                        ) : (
                            <div className="space-y-3">
                                {readyToBoxOrders.data.map((order) => (
                                    <Card
                                        key={order.id}
                                        className="border-emerald-500/40"
                                    >
                                        <CardContent className="flex flex-col justify-between gap-4 p-4 md:flex-row md:items-center">
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-3">
                                                    <span className="font-mono text-base font-bold">
                                                        {order.order_number}
                                                    </span>
                                                    <Badge className="bg-emerald-600">
                                                        All Items in Hub
                                                    </Badge>
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    Recipient:{' '}
                                                    <strong>
                                                        {order.full_name}
                                                    </strong>{' '}
                                                    ({order.phone_number}) •{' '}
                                                    {order.wilaya},{' '}
                                                    {order.baladiya}
                                                </div>
                                                <div className="mt-1 text-xs text-muted-foreground">
                                                    Items:{' '}
                                                    {order.items
                                                        .map(
                                                            (i) =>
                                                                `${i.product_name} (${i.size?.code || 'Std'}) x${i.quantity}`,
                                                        )
                                                        .join(', ')}
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-2">
                                                <Button
                                                    size="sm"
                                                    className="bg-primary text-primary-foreground hover:bg-primary/90"
                                                    onClick={() =>
                                                        router.get(
                                                            `/admin/orders?search=${order.order_number}`,
                                                        )
                                                    }
                                                >
                                                    View in Orders Screen
                                                    <ArrowRight className="ml-1.5 h-4 w-4" />
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* CREATE SUPPLY REQUEST MODAL (HIERARCHICAL BY PRODUCT & SIZE) */}
            <Dialog
                open={!!selectedStoreDemand}
                onOpenChange={() => setSelectedStoreDemand(null)}
            >
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Generate Supply Request</DialogTitle>
                        <DialogDescription>
                            Review products & size breakdown for{' '}
                            <strong>{selectedStoreDemand?.store.name}</strong>.
                        </DialogDescription>
                    </DialogHeader>

                    {selectedStoreDemand && (
                        <div className="my-2 space-y-4">
                            <div className="flex items-center justify-between border-b pb-2">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">
                                    Select Items to Include
                                </span>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                        if (
                                            selectedItemIds.length ===
                                            selectedStoreDemand.order_items
                                                .length
                                        ) {
                                            setSelectedItemIds([]);
                                        } else {
                                            setSelectedItemIds(
                                                selectedStoreDemand.order_items.map(
                                                    (i) => i.id,
                                                ),
                                            );
                                        }
                                    }}
                                    className="h-7 text-xs"
                                >
                                    {selectedItemIds.length ===
                                    selectedStoreDemand.order_items.length
                                        ? 'Deselect All'
                                        : 'Select All'}
                                </Button>
                            </div>

                            {/* Grouped by Product with Sizes Breakdown */}
                            <div className="max-h-72 space-y-3 overflow-y-auto pr-1">
                                {selectedStoreDemand.products &&
                                selectedStoreDemand.products.length > 0
                                    ? selectedStoreDemand.products.map(
                                          (prod) => {
                                              const isProductFullySelected =
                                                  prod.order_item_ids.every(
                                                      (id) =>
                                                          selectedItemIds.includes(
                                                              id,
                                                          ),
                                                  );
                                              const isProductPartiallySelected =
                                                  prod.order_item_ids.some(
                                                      (id) =>
                                                          selectedItemIds.includes(
                                                              id,
                                                          ),
                                                  ) && !isProductFullySelected;

                                              return (
                                                  <div
                                                      key={prod.product_id}
                                                      className="space-y-2.5 rounded-lg border border-border bg-card p-3"
                                                  >
                                                      {/* Product Header */}
                                                      <div
                                                          className="flex cursor-pointer items-center justify-between"
                                                          onClick={() =>
                                                              toggleProductSelection(
                                                                  prod,
                                                              )
                                                          }
                                                      >
                                                          <div className="flex items-center gap-2.5">
                                                              <Checkbox
                                                                  checked={
                                                                      isProductFullySelected
                                                                          ? true
                                                                          : isProductPartiallySelected
                                                                            ? 'indeterminate'
                                                                            : false
                                                                  }
                                                              />
                                                              <span className="text-sm font-semibold">
                                                                  {
                                                                      prod.product_name
                                                                  }
                                                              </span>
                                                          </div>
                                                          <span className="font-mono text-xs text-muted-foreground">
                                                              Total:{' '}
                                                              {
                                                                  prod.total_quantity
                                                              }{' '}
                                                              pcs
                                                          </span>
                                                      </div>

                                                      {/* Sizes Breakdown List */}
                                                      <div className="ml-2 space-y-1.5 border-l-2 border-muted pl-6">
                                                          {prod.sizes.map(
                                                              (s) => {
                                                                  const isSizeSelected =
                                                                      s.order_item_ids.every(
                                                                          (
                                                                              id,
                                                                          ) =>
                                                                              selectedItemIds.includes(
                                                                                  id,
                                                                              ),
                                                                      );
                                                                  return (
                                                                      <div
                                                                          key={
                                                                              s.size
                                                                          }
                                                                          onClick={() =>
                                                                              toggleSizeSelection(
                                                                                  s,
                                                                              )
                                                                          }
                                                                          className={`flex cursor-pointer items-center justify-between rounded p-1.5 text-xs transition-colors ${
                                                                              isSizeSelected
                                                                                  ? 'bg-primary/10 font-medium text-primary'
                                                                                  : 'bg-muted/40 text-muted-foreground'
                                                                          }`}
                                                                      >
                                                                          <div className="flex items-center gap-2">
                                                                              <Checkbox
                                                                                  checked={
                                                                                      isSizeSelected
                                                                                  }
                                                                              />
                                                                              <span>
                                                                                  Size:{' '}
                                                                                  <strong>
                                                                                      {
                                                                                          s.size
                                                                                      }
                                                                                  </strong>
                                                                              </span>
                                                                              <span className="text-[11px] opacity-70">
                                                                                  (
                                                                                  {
                                                                                      s
                                                                                          .order_numbers
                                                                                          .length
                                                                                  }{' '}
                                                                                  orders:{' '}
                                                                                  {s.order_numbers
                                                                                      .slice(
                                                                                          0,
                                                                                          2,
                                                                                      )
                                                                                      .join(
                                                                                          ', ',
                                                                                      )}
                                                                                  {s
                                                                                      .order_numbers
                                                                                      .length >
                                                                                  2
                                                                                      ? '...'
                                                                                      : ''}
                                                                                  )
                                                                              </span>
                                                                          </div>
                                                                          <span className="font-mono font-bold">
                                                                              {
                                                                                  s.total_quantity
                                                                              }{' '}
                                                                              pcs
                                                                          </span>
                                                                      </div>
                                                                  );
                                                              },
                                                          )}
                                                      </div>
                                                  </div>
                                              );
                                          },
                                      )
                                    : selectedStoreDemand.order_items.map(
                                          (item) => (
                                              <div
                                                  key={item.id}
                                                  onClick={() =>
                                                      toggleSelectItem(item.id)
                                                  }
                                                  className="flex cursor-pointer items-center justify-between rounded border p-2"
                                              >
                                                  <div className="flex items-center gap-2">
                                                      <Checkbox
                                                          checked={selectedItemIds.includes(
                                                              item.id,
                                                          )}
                                                      />
                                                      <span className="text-xs">
                                                          {item.product_name} (
                                                          {item.size || 'Std'})
                                                      </span>
                                                  </div>
                                                  <span className="font-mono text-xs">
                                                      x{item.quantity}
                                                  </span>
                                              </div>
                                          ),
                                      )}
                            </div>

                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-muted-foreground">
                                    Notes for Store (Optional)
                                </label>
                                <Textarea
                                    placeholder="e.g. Please pack neatly and label each size variant..."
                                    value={requestNotes}
                                    onChange={(e) =>
                                        setRequestNotes(e.target.value)
                                    }
                                    rows={2}
                                />
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setSelectedStoreDemand(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleCreateSupplyRequest}
                            disabled={
                                selectedItemIds.length === 0 || isSubmitting
                            }
                        >
                            {isSubmitting
                                ? 'Creating...'
                                : `Dispatch Request (${selectedItemIds.length} items)`}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* RECEIVE AT HUB MODAL */}
            <Dialog
                open={!!receivingRequest}
                onOpenChange={() => setReceivingRequest(null)}
            >
                <DialogContent className="max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Check In Items at Hub</DialogTitle>
                        <DialogDescription>
                            Verify quantities received for{' '}
                            <strong>{receivingRequest?.reference_code}</strong>{' '}
                            ({receivingRequest?.store.name}).
                        </DialogDescription>
                    </DialogHeader>

                    {receivingRequest && (
                        <div className="my-2 space-y-4">
                            <div className="max-h-60 space-y-3 overflow-y-auto pr-1">
                                {receivingRequest.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center justify-between gap-4 rounded-lg border border-border p-3"
                                    >
                                        <div className="space-y-0.5">
                                            <div className="text-sm font-medium">
                                                {item.product_name}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                Size: {item.size?.code || 'Std'}{' '}
                                                • Total Expected:{' '}
                                                <strong>
                                                    {item.requested_quantity}
                                                </strong>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <label className="text-xs text-muted-foreground">
                                                Qty Rec:
                                            </label>
                                            <Input
                                                type="number"
                                                min="0"
                                                max={item.requested_quantity}
                                                className="w-20 text-center font-mono"
                                                value={
                                                    receivedQuantities[
                                                        item.id
                                                    ] ?? item.requested_quantity
                                                }
                                                onChange={(e) => {
                                                    const val =
                                                        parseInt(
                                                            e.target.value,
                                                            10,
                                                        ) || 0;
                                                    setReceivedQuantities(
                                                        (prev) => ({
                                                            ...prev,
                                                            [item.id]: val,
                                                        }),
                                                    );
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setReceivingRequest(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            className="bg-emerald-600 text-white hover:bg-emerald-700"
                            onClick={handleReceiveSubmit}
                            disabled={isSubmitting}
                        >
                            {isSubmitting
                                ? 'Recording...'
                                : 'Confirm Hub Reception'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* STATUS UPDATE MODAL */}
            <Dialog
                open={!!editingRequest}
                onOpenChange={() => setEditingRequest(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Update Request Status</DialogTitle>
                        <DialogDescription>
                            {editingRequest?.reference_code} (
                            {editingRequest?.store.name})
                        </DialogDescription>
                    </DialogHeader>

                    <div className="my-2 space-y-4">
                        <div className="space-y-1.5">
                            <label className="text-xs font-semibold text-muted-foreground">
                                Status
                            </label>
                            <Select
                                value={editStatus}
                                onValueChange={setEditStatus}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">
                                        Sent to Store
                                    </SelectItem>
                                    <SelectItem value="preparing">
                                        Preparing
                                    </SelectItem>
                                    <SelectItem value="shipped_to_hub">
                                        Shipped to Hub
                                    </SelectItem>
                                    <SelectItem value="received_at_hub">
                                        Received at Hub
                                    </SelectItem>
                                    <SelectItem value="completed">
                                        Completed
                                    </SelectItem>
                                    <SelectItem value="cancelled">
                                        Cancelled
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-semibold text-muted-foreground">
                                Courier Name
                            </label>
                            <Input
                                placeholder="e.g. Yalidine, ZR Express, Internal Van"
                                value={courierName}
                                onChange={(e) => setCourierName(e.target.value)}
                            />
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-semibold text-muted-foreground">
                                Tracking Number
                            </label>
                            <Input
                                placeholder="e.g. YL-9849202"
                                value={trackingNumber}
                                onChange={(e) =>
                                    setTrackingNumber(e.target.value)
                                }
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setEditingRequest(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleStatusSubmit}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
