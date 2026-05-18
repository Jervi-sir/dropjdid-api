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
          <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
            Active
          </Badge>
        );
      case 'suspended':
        return (
          <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
            Suspended
          </Badge>
        );
      case 'pending':
        return (
          <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100/80 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">
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
          <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
            Published
          </Badge>
        );
      case 'draft':
        return (
          <Badge className="bg-slate-100 text-slate-700 hover:bg-slate-200/80 border-slate-500/20 border dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
            Draft
          </Badge>
        );
      case 'archived':
        return (
          <Badge className="bg-zinc-100 text-zinc-700 hover:bg-zinc-200/80 border-zinc-500/20 border dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
            Archived
          </Badge>
        );
      case 'rejected':
        return (
          <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100/80 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">
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
        product.name.toLowerCase().includes(productSearch.toLowerCase()) ||
        product.description?.toLowerCase().includes(productSearch.toLowerCase()) ||
        product.category?.en.toLowerCase().includes(productSearch.toLowerCase());

      const matchesStatus =
        productStatusFilter === 'all' || product.status.toLowerCase() === productStatusFilter.toLowerCase();

      return matchesSearch && matchesStatus;
    });
  }, [store.products, productSearch, productStatusFilter]);

  return (
    <>
      <Head title={`Audit Store - ${store.store_name}`} />
      <div className="flex flex-col gap-6 p-4 lg:p-8 max-w-7xl mx-auto">

        {/* Back Link */}
        <div>
          <Link
            href="/admin/stores"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <IconArrowLeft className="size-4" />
            <span>Back to Stores</span>
          </Link>
        </div>

        {/* Store Title Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
          <div className="flex flex-col md:flex-row md:items-center gap-4">
            <div className="size-16 rounded-2xl overflow-hidden border shrink-0 bg-primary/10 text-primary flex items-center justify-center font-bold text-xl shadow-sm">
              {store.logo ? (
                <img src={store.logo} alt={store.store_name} className="w-full h-full object-cover" />
              ) : (
                store.store_name?.charAt(0).toUpperCase()
              )}
            </div>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-3xl font-extrabold tracking-tight text-foreground flex items-center gap-2">
                  {store.store_name}
                  {store.is_verified && (
                    <IconCircleCheck className="size-6 text-cyan-600 dark:text-cyan-400 shrink-0" />
                  )}
                </h1>
                {getStatusBadge(store.status)}
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                Store ID #{store.id} • Registered on {formatDate(store.created_at)}
              </p>
            </div>
          </div>
        </div>

        {/* Main 2-Column Dashboard */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

          {/* Left Column: Store Profiles & Status Modifiers (lg:col-span-4) */}
          <div className="lg:col-span-4 flex flex-col gap-6">

            {/* Profile Overview Card */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconShoppingBag className="size-5 text-muted-foreground" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Store Profile</h3>
              </div>
              <div className="flex flex-col gap-3 text-xs leading-relaxed text-muted-foreground">
                <p className="italic text-foreground mb-1">
                  {store.description || 'No description provided for this store.'}
                </p>
                <div className="flex items-center gap-2 text-muted-foreground border-t pt-3">
                  <IconUser className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-bold text-foreground">
                    Owner: {store.user?.full_name} (@{store.user?.username})
                  </span>
                </div>
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconMail className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-semibold text-foreground truncate">{store.user?.email || 'No owner email'}</span>
                </div>
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconPhone className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-semibold text-foreground">{store.phone_number || 'No phone number'}</span>
                </div>
                <div className="flex items-center gap-2 text-muted-foreground">
                  <IconMapPin className="size-4 text-muted-foreground shrink-0" />
                  <span className="font-semibold text-foreground">{store.wilaya?.name || 'No location set'}</span>
                </div>
              </div>
            </div>

            {/* Wallet Balance Card */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col justify-between">
              <span className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Operational Balance</span>
              <span className="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                ${store.balance.toFixed(2)}
              </span>
            </div>

            {/* Moderation Controls Form */}
            <form onSubmit={handleSubmit} className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-5">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconLock className="size-5 text-primary" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Moderation panel</h3>
              </div>

              {/* Status Selector */}
              <div className="flex flex-col gap-2">
                <label className="text-xs font-bold text-muted-foreground">Operational Status</label>
                <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                  <SelectTrigger className="w-full h-10 bg-background border-input">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="suspended">Suspended</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Verified Merchant Badge Button Toggle */}
              <button
                type="button"
                onClick={() => setData('is_verified', !data.is_verified)}
                className={`flex items-center justify-between p-3.5 rounded-xl border text-left transition-all ${
                  data.is_verified
                    ? 'border-cyan-500/40 bg-cyan-500/5 dark:bg-cyan-500/10'
                    : 'border-muted hover:border-foreground/20 bg-muted/5'
                }`}
              >
                <div className="flex flex-col gap-0.5">
                  <span className="text-xs font-bold text-foreground flex items-center gap-1.5">
                    <IconCircleCheck className="size-4 text-cyan-600 dark:text-cyan-400" />
                    <span>Verified Badge</span>
                  </span>
                  <span className="text-[10px] text-muted-foreground">
                    Display badge on product pages
                  </span>
                </div>
                <div
                  className={`size-5 rounded-full border flex items-center justify-center transition-all shrink-0 ${
                    data.is_verified ? 'bg-cyan-600 border-cyan-600 text-white' : 'border-muted-foreground/35 bg-background'
                  }`}
                >
                  {data.is_verified && <IconCheck className="size-3.5 stroke-[2.5]" />}
                </div>
              </button>

              <Button type="submit" disabled={processing} className="w-full h-10 mt-1">
                {processing ? 'Saving changes...' : 'Save Settings'}
              </Button>

              {recentlySuccessful && (
                <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                  <IconCheck className="size-4 shrink-0" />
                  <span>Store changes synchronized</span>
                </div>
              )}
            </form>
          </div>

          {/* Right Column: searchable associated products (lg:col-span-8) */}
          <div className="lg:col-span-8 flex flex-col gap-6">

            {/* Products Dashboard Table Container */}
            <div className="bg-card border rounded-2xl shadow-xs overflow-hidden flex flex-col">

              {/* Products Controls Header */}
              <div className="p-5 border-b flex flex-col gap-4 bg-muted/15">
                <div className="flex items-center justify-between">
                  <h3 className="font-extrabold text-sm uppercase tracking-wider text-foreground">
                    Associated Catalog ({filteredProducts.length})
                  </h3>
                </div>

                {/* Filter and search bar inside details */}
                <div className="flex flex-col sm:flex-row items-center gap-3">
                  <div className="relative flex-1 w-full">
                    <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      placeholder="Search store products by name or category..."
                      value={productSearch}
                      onChange={(e) => setProductSearch(e.target.value)}
                      className="pl-9 h-10 w-full bg-background"
                    />
                  </div>

                  <div className="w-full sm:w-36">
                    <Select value={productStatusFilter} onValueChange={setProductStatusFilter}>
                      <SelectTrigger className="h-10 bg-background">
                        <SelectValue placeholder="All Status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="published">Published</SelectItem>
                        <SelectItem value="draft">Draft</SelectItem>
                        <SelectItem value="archived">Archived</SelectItem>
                        <SelectItem value="rejected">Rejected</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>

              {/* Store Products Table */}
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader className="bg-muted/5 border-b">
                    <TableRow>
                      <TableHead className="w-[60px] pl-6 py-4">Thumbnail</TableHead>
                      <TableHead className="py-4">Product Name</TableHead>
                      <TableHead className="py-4">Category</TableHead>
                      <TableHead className="py-4">Original Price</TableHead>
                      <TableHead className="py-4">Show Price</TableHead>
                      <TableHead className="py-4">Status</TableHead>
                      <TableHead className="py-4">Created</TableHead>
                      <TableHead className="py-4 text-right pr-6 w-[80px]">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredProducts.length > 0 ? (
                      filteredProducts.map((product) => (
                        <TableRow key={product.id} className="hover:bg-muted/5 group/row transition-colors">
                          <TableCell className="pl-6 py-4">
                            <div className="size-10 rounded-lg overflow-hidden border shrink-0 bg-muted/10 flex items-center justify-center font-bold text-xs text-primary shadow-inner">
                              {product.image ? (
                                <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
                              ) : (
                                product.name.charAt(0).toUpperCase()
                              )}
                            </div>
                          </TableCell>
                          <TableCell className="py-4">
                            <div className="flex flex-col">
                              <span className="font-bold text-foreground text-sm">{product.name}</span>
                              <span className="text-[10px] text-muted-foreground truncate max-w-[200px] mt-0.5">
                                {product.description || 'No description provided.'}
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="py-4">
                            {product.category ? (
                              <Badge variant="outline" className="text-[10px] px-2.5">
                                {product.category.en}
                              </Badge>
                            ) : (
                              <span className="text-xs text-muted-foreground">None</span>
                            )}
                          </TableCell>
                          <TableCell className="py-4 text-xs font-semibold text-muted-foreground">
                            ${product.original_price.toFixed(2)}
                          </TableCell>
                          <TableCell className="py-4 text-xs font-bold text-foreground">
                            ${product.show_price.toFixed(2)}
                          </TableCell>
                          <TableCell className="py-4">
                            {getProductStatusBadge(product.status)}
                          </TableCell>
                          <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                            {formatDate(product.created_at)}
                          </TableCell>
                          <TableCell className="py-4 text-right pr-6">
                            <Button variant="ghost" size="xs" className="h-7 px-2 text-muted-foreground hover:text-foreground" asChild>
                              <Link href={`/admin/products/${product.id}`}>
                                <IconEye className="size-4" />
                              </Link>
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    ) : (
                      <TableRow>
                        <TableCell colSpan={8} className="py-12 text-center text-muted-foreground">
                          <div className="flex flex-col items-center justify-center gap-3">
                            <IconShoppingBag className="size-8 text-muted-foreground/55 stroke-[1.5]" />
                            <div className="flex flex-col gap-0.5">
                              <p className="font-semibold text-sm text-foreground">No products found</p>
                              <p className="text-xs">No store products match your current keyword or status filters.</p>
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
