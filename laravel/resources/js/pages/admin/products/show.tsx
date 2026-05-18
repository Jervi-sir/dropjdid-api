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
    product.images?.[0]?.image || 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80'
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
      const res = await fetch(`/admin/products/${product.id}/stats?liked_page=${likedPage}&saved_page=${savedPage}&drops_page=${dropsPage}`, {
        headers: { Accept: 'application/json' },
      });
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

  const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
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
          <Badge className="bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 border-indigo-500/20 border dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30">
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

  return (
    <>
      <Head title={`Audit Product - ${product.name}`} />
      <div className="flex flex-col gap-6 p-4 lg:p-8 max-w-7xl mx-auto">
        
        {/* Navigation Bar */}
        <div>
          <Link
            href="/admin/products"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <IconArrowLeft className="size-4" />
            <span>Back to Products</span>
          </Link>
        </div>

        {/* Dashboard Title */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
          <div className="flex flex-col md:flex-row md:items-center gap-3.5">
            <div className="size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm border border-primary/20">
              <IconShoppingBag className="size-6 stroke-[1.5]" />
            </div>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-3xl font-extrabold tracking-tight text-foreground">{product.name}</h1>
                {getStatusBadge(product.status)}
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                Audit Product ID #{product.id} • Registered on {formatDate(product.created_at)}
              </p>
            </div>
          </div>
        </div>

        {/* 3-Column Dashboard Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          {/* Column 1: Image Gallery & Description (lg:col-span-5) */}
          <div className="lg:col-span-5 flex flex-col gap-6">
            
            {/* Gallery Card */}
            <div className="bg-card border rounded-2xl overflow-hidden shadow-xs p-4 flex flex-col gap-4">
              <div className="aspect-square rounded-xl overflow-hidden border bg-muted/15 relative shadow-inner">
                <img src={activeImage} alt={product.name} className="w-full h-full object-cover" />
              </div>
              {product.images && product.images.length > 0 && (
                <div className="grid grid-cols-5 gap-2.5">
                  {product.images.map((img) => (
                    <button
                      key={img.id}
                      onClick={() => setActiveImage(img.image)}
                      className={`aspect-square rounded-lg overflow-hidden border transition-all ${
                        activeImage === img.image ? 'border-primary ring-2 ring-primary/25 scale-[1.03]' : 'border-muted hover:border-foreground/40'
                      }`}
                    >
                      <img src={img.image} alt="product thumbnail" className="w-full h-full object-cover" />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Description and Features */}
            <div className="bg-card border rounded-2xl p-6 shadow-xs flex flex-col gap-4">
              <h3 className="font-bold text-sm uppercase tracking-wider text-muted-foreground">Product Description</h3>
              <p className="text-sm text-foreground leading-relaxed whitespace-pre-wrap">{product.description}</p>

              <hr className="border-muted/65" />

              <div className="grid grid-cols-2 gap-4 text-xs">
                <div className="flex flex-col gap-1 border-r pr-2">
                  <span className="text-muted-foreground uppercase font-bold text-[10px]">Gender Target</span>
                  <span className="font-bold text-foreground capitalize">{product.gender?.en || 'Unisex'}</span>
                </div>
                <div className="flex flex-col gap-1">
                  <span className="text-muted-foreground uppercase font-bold text-[10px]">Payment Type</span>
                  <span className="font-bold text-foreground capitalize">{product.payment_method?.en || 'Standard'}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Column 2: Status Moderator Form (lg:col-span-4) */}
          <div className="lg:col-span-4 flex flex-col gap-6">
            
            <form onSubmit={handleSubmit} className="bg-card border rounded-2xl p-6 shadow-xs flex flex-col gap-5">
              <div className="flex items-center justify-between border-b pb-4">
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Moderator Hub</h3>
                <IconCircleCheck className="size-5 text-primary" />
              </div>

              {/* Status Selector */}
              <div className="flex flex-col gap-2">
                <label className="text-xs font-semibold text-muted-foreground">Modify Status</label>
                <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                  <SelectTrigger className="w-full h-11 bg-background border-input">
                    <SelectValue placeholder="Select Status" />
                  </SelectTrigger>
                  <SelectContent>
                    {Object.entries(statuses).map(([key, label]) => (
                      <SelectItem key={key} value={label}>
                        <span className="capitalize">{label}</span>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-500 mt-1 font-semibold">{errors.status}</p>}
              </div>

              {/* Dynamic Localized Rejection Fields (English, French, Arabic RTL supported) */}
              <div
                className={`transition-all duration-300 ease-in-out overflow-hidden flex flex-col gap-4 ${
                  data.status === 'rejected' ? 'max-h-[450px] opacity-100 mt-2' : 'max-h-0 opacity-0 pointer-events-none'
                }`}
              >
                <div className="border-t pt-4 flex flex-col gap-4">
                  <div className="flex items-center gap-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                    <IconAlertTriangle className="size-4 shrink-0" />
                    <span>Rejection translations required</span>
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (English)</label>
                    <Input
                      placeholder="Type rejection reason in English..."
                      value={data.rejection_reason_en}
                      onChange={(e) => setData('rejection_reason_en', e.target.value)}
                      className="h-10 bg-background"
                    />
                    {errors.rejection_reason_en && (
                      <p className="text-[10px] text-rose-500 font-bold">{errors.rejection_reason_en}</p>
                    )}
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (French)</label>
                    <Input
                      placeholder="Saisir la raison en français..."
                      value={data.rejection_reason_fr}
                      onChange={(e) => setData('rejection_reason_fr', e.target.value)}
                      className="h-10 bg-background"
                    />
                    {errors.rejection_reason_fr && (
                      <p className="text-[10px] text-rose-500 font-bold">{errors.rejection_reason_fr}</p>
                    )}
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (Arabic)</label>
                    <Input
                      dir="rtl"
                      placeholder="أدخل سبب الرفض باللغة العربية..."
                      value={data.rejection_reason_ar}
                      onChange={(e) => setData('rejection_reason_ar', e.target.value)}
                      className="h-10 bg-background text-right"
                    />
                    {errors.rejection_reason_ar && (
                      <p className="text-[10px] text-rose-500 font-bold">{errors.rejection_reason_ar}</p>
                    )}
                  </div>
                </div>
              </div>

              {/* Submit and Notifications */}
              <Button type="submit" disabled={processing} className="w-full h-11 text-xs font-bold uppercase tracking-wider mt-2">
                {processing ? 'Applying decisions...' : 'Commit Audit Decisions'}
              </Button>

              {recentlySuccessful && (
                <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                  <IconCheck className="size-4 shrink-0" />
                  <span>Audit updated successfully</span>
                </div>
              )}
            </form>
          </div>

          {/* Column 3: Store owner & rejection history (lg:col-span-3) */}
          <div className="lg:col-span-3 flex flex-col gap-6">
            
            {/* Store Card */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconShoppingBag className="size-5 text-muted-foreground" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Seller Profile</h3>
              </div>
              <div className="flex items-center gap-3">
                <div className="size-11 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0">
                  {product.store?.name?.charAt(0).toUpperCase() || 'S'}
                </div>
                <div className="flex flex-col">
                  <span className="font-bold text-foreground text-sm leading-tight">{product.store?.name || 'Unknown Store'}</span>
                  <span className="text-xs text-muted-foreground mt-0.5">
                    {product.store?.username ? `@${product.store.username}` : ''}
                  </span>
                </div>
              </div>
            </div>

            {/* Rejection History Log */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconBan className="size-5 text-rose-500" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Audit Log</h3>
              </div>

              {product.rejection_reasons && product.rejection_reasons.length > 0 ? (
                <div className="flex flex-col gap-4 max-h-[300px] overflow-y-auto pr-1">
                  {product.rejection_reasons.map((reason, index) => (
                    <div key={reason.id || index} className="flex items-start gap-2.5 text-xs pb-3 border-b last:border-0 last:pb-0">
                      <div className="size-5 rounded-full bg-rose-500/15 text-rose-500 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                        {reason.id || index + 1}
                      </div>
                      <div className="flex flex-col flex-1 gap-1 leading-relaxed">
                        <span className="font-bold text-foreground">Audit Comment</span>
                        <div className="flex flex-col gap-1 text-muted-foreground mt-1">
                          {reason.en && (
                            <div>
                              <strong className="text-[9px] uppercase tracking-wider font-bold text-foreground">EN:</strong> {reason.en}
                            </div>
                          )}
                          {reason.fr && (
                            <div>
                              <strong className="text-[9px] uppercase tracking-wider font-bold text-foreground">FR:</strong> {reason.fr}
                            </div>
                          )}
                          {reason.ar && (
                            <div className="text-right mt-0.5" dir="rtl">
                              <strong className="text-[9px] uppercase tracking-wider font-bold text-foreground">AR:</strong> {reason.ar}
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-6 text-xs text-muted-foreground">
                  No rejection history found. This product is clean.
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Product Performance Stats Panel */}
        <div className="mt-12 border-t pt-10 flex flex-col gap-6">
          <div>
            <h2 className="text-2xl font-black tracking-tight text-foreground">Interaction & Distribution Stats</h2>
            <p className="text-sm text-muted-foreground mt-1">
              Analyze product popularity, active shopper wishlists, and orders distributed across publishing drops.
            </p>
          </div>

          {/* KPI Counters Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {/* Likes KPI */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
              <div className="size-11 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                <IconHeart className="size-6 stroke-[1.8]" />
              </div>
              <div className="flex flex-col">
                <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Total Likes</span>
                <span className="text-xl font-black text-foreground mt-0.5">
                  {loadingStats ? <IconLoader2 className="size-4 animate-spin text-muted-foreground" /> : (stats?.kpis?.liked_count ?? 0)} Likes
                </span>
              </div>
            </div>

            {/* Saves KPI */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
              <div className="size-11 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <IconBookmark className="size-6 stroke-[1.8]" />
              </div>
              <div className="flex flex-col">
                <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Times Saved</span>
                <span className="text-xl font-black text-foreground mt-0.5">
                  {loadingStats ? <IconLoader2 className="size-4 animate-spin text-muted-foreground" /> : (stats?.kpis?.saved_count ?? 0)} Saves
                </span>
              </div>
            </div>

            {/* Orders KPI */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
              <div className="size-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <IconShoppingBag className="size-6 stroke-[1.8]" />
              </div>
              <div className="flex flex-col">
                <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Total Orders</span>
                <span className="text-xl font-black text-foreground mt-0.5">
                  {loadingStats ? <IconLoader2 className="size-4 animate-spin text-muted-foreground" /> : (stats?.kpis?.orders_count ?? 0)} Orders
                </span>
              </div>
            </div>

            {/* Drops KPI */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
              <div className="size-11 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <IconRocket className="size-6 stroke-[1.8]" />
              </div>
              <div className="flex flex-col">
                <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Drops Carrying Item</span>
                <span className="text-xl font-black text-foreground mt-0.5">
                  {loadingStats ? <IconLoader2 className="size-4 animate-spin text-muted-foreground" /> : (stats?.kpis?.drops_count ?? 0)} Drops
                </span>
              </div>
            </div>
          </div>

          {/* Interactive Three Tables View */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mt-4">
            
            {/* Table 1: Who Liked This Product (lg:col-span-6) */}
            <div className="lg:col-span-6 flex flex-col gap-4 bg-card border rounded-2xl p-5 shadow-xs">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconHeart className="size-5 text-rose-500 stroke-[1.8]" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Who Liked this Product</h3>
              </div>

              <div className="overflow-x-auto min-h-[220px]">
                {loadingStats ? (
                  <div className="flex flex-col items-center justify-center py-16 gap-2">
                    <IconLoader2 className="size-6 animate-spin text-primary" />
                    <span className="text-xs text-muted-foreground">Loading likes...</span>
                  </div>
                ) : stats?.liked_users?.data?.length > 0 ? (
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="border-b text-[10px] uppercase font-bold text-muted-foreground bg-muted/5">
                        <th className="py-2.5 pl-3">Shopper</th>
                        <th className="py-2.5">Email</th>
                        <th className="py-2.5 pr-3 text-right">Liked Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      {stats.liked_users.data.map((item: any) => (
                        <tr key={item.id} className="border-b last:border-0 hover:bg-muted/5 transition-colors">
                          <td className="py-3 pl-3">
                            <div className="flex items-center gap-2.5">
                              <div className="size-7 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold text-[10px] overflow-hidden shrink-0">
                                {item.user?.image ? (
                                  <img src={item.user.image} alt={item.user.username} className="w-full h-full object-cover" />
                                ) : (
                                  <IconUser className="size-3.5" />
                                )}
                              </div>
                              <div className="flex flex-col">
                                <span className="font-bold text-xs text-foreground">{item.user?.full_name || 'N/A'}</span>
                                <span className="text-[9px] text-muted-foreground">@{item.user?.username || 'N/A'}</span>
                              </div>
                            </div>
                          </td>
                          <td className="py-3 text-xs text-muted-foreground font-semibold">{item.user?.email || 'N/A'}</td>
                          <td className="py-3 pr-3 text-right text-[10px] text-muted-foreground font-medium">
                            {item.created_at ? new Date(item.created_at).toLocaleDateString() : 'N/A'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : (
                  <div className="flex flex-col items-center justify-center py-16 text-center text-muted-foreground gap-2">
                    <IconInbox className="size-8 text-muted-foreground/45" />
                    <span className="text-xs font-semibold">No likes recorded yet</span>
                  </div>
                )}
              </div>

              {/* Table 1 Pagination */}
              {stats?.liked_users?.last_page > 1 && (
                <div className="flex items-center justify-between border-t pt-3.5">
                  <span className="text-[10px] font-semibold text-muted-foreground">
                    Page {stats.liked_users.current_page} of {stats.liked_users.last_page}
                  </span>
                  <div className="flex items-center gap-1">
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={likedPage === 1}
                      onClick={() => setLikedPage((prev) => Math.max(1, prev - 1))}
                      className="h-7 w-7 p-0"
                    >
                      <IconChevronLeft className="size-3.5" />
                    </Button>
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={likedPage === stats.liked_users.last_page}
                      onClick={() => setLikedPage((prev) => Math.min(stats.liked_users.last_page, prev + 1))}
                      className="h-7 w-7 p-0"
                    >
                      <IconChevronRight className="size-3.5" />
                    </Button>
                  </div>
                </div>
              )}
            </div>

            {/* Table 2: Who Saved This Product (lg:col-span-6) */}
            <div className="lg:col-span-6 flex flex-col gap-4 bg-card border rounded-2xl p-5 shadow-xs">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconBookmark className="size-5 text-amber-500 stroke-[1.8]" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Who Saved this Product</h3>
              </div>

              <div className="overflow-x-auto min-h-[220px]">
                {loadingStats ? (
                  <div className="flex flex-col items-center justify-center py-16 gap-2">
                    <IconLoader2 className="size-6 animate-spin text-primary" />
                    <span className="text-xs text-muted-foreground">Loading saves...</span>
                  </div>
                ) : stats?.saved_users?.data?.length > 0 ? (
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="border-b text-[10px] uppercase font-bold text-muted-foreground bg-muted/5">
                        <th className="py-2.5 pl-3">Shopper</th>
                        <th className="py-2.5">Email</th>
                        <th className="py-2.5 pr-3 text-right">Saved Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      {stats.saved_users.data.map((item: any) => (
                        <tr key={item.id} className="border-b last:border-0 hover:bg-muted/5 transition-colors">
                          <td className="py-3 pl-3">
                            <div className="flex items-center gap-2.5">
                              <div className="size-7 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold text-[10px] overflow-hidden shrink-0">
                                {item.user?.image ? (
                                  <img src={item.user.image} alt={item.user.username} className="w-full h-full object-cover" />
                                ) : (
                                  <IconUser className="size-3.5" />
                                )}
                              </div>
                              <div className="flex flex-col">
                                <span className="font-bold text-xs text-foreground">{item.user?.full_name || 'N/A'}</span>
                                <span className="text-[9px] text-muted-foreground">@{item.user?.username || 'N/A'}</span>
                              </div>
                            </div>
                          </td>
                          <td className="py-3 text-xs text-muted-foreground font-semibold">{item.user?.email || 'N/A'}</td>
                          <td className="py-3 pr-3 text-right text-[10px] text-muted-foreground font-medium">
                            {item.created_at ? new Date(item.created_at).toLocaleDateString() : 'N/A'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : (
                  <div className="flex flex-col items-center justify-center py-16 text-center text-muted-foreground gap-2">
                    <IconInbox className="size-8 text-muted-foreground/45" />
                    <span className="text-xs font-semibold">No saves recorded yet</span>
                  </div>
                )}
              </div>

              {/* Table 2 Pagination */}
              {stats?.saved_users?.last_page > 1 && (
                <div className="flex items-center justify-between border-t pt-3.5">
                  <span className="text-[10px] font-semibold text-muted-foreground">
                    Page {stats.saved_users.current_page} of {stats.saved_users.last_page}
                  </span>
                  <div className="flex items-center gap-1">
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={savedPage === 1}
                      onClick={() => setSavedPage((prev) => Math.max(1, prev - 1))}
                      className="h-7 w-7 p-0"
                    >
                      <IconChevronLeft className="size-3.5" />
                    </Button>
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={savedPage === stats.saved_users.last_page}
                      onClick={() => setSavedPage((prev) => Math.min(stats.saved_users.last_page, prev + 1))}
                      className="h-7 w-7 p-0"
                    >
                      <IconChevronRight className="size-3.5" />
                    </Button>
                  </div>
                </div>
              )}
            </div>

            {/* Table 3: Product Drops & Performance (lg:col-span-12) */}
            <div className="lg:col-span-12 flex flex-col gap-4 bg-card border rounded-2xl p-5 shadow-xs">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconRocket className="size-5 text-indigo-500 stroke-[1.8]" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Drops Carrying this Product</h3>
              </div>

              <div className="overflow-x-auto min-h-[180px]">
                {loadingStats ? (
                  <div className="flex flex-col items-center justify-center py-16 gap-2">
                    <IconLoader2 className="size-6 animate-spin text-primary" />
                    <span className="text-xs text-muted-foreground">Loading drops list...</span>
                  </div>
                ) : stats?.drops?.data?.length > 0 ? (
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="border-b text-[10px] uppercase font-bold text-muted-foreground bg-muted/5">
                        <th className="py-2.5 pl-3">Drop Title</th>
                        <th className="py-2.5">Schedule</th>
                        <th className="py-2.5 text-center">Status</th>
                        <th className="py-2.5 text-center">Drop Price</th>
                        <th className="py-2.5 pr-3 text-right">Exchanged Orders</th>
                      </tr>
                    </thead>
                    <tbody>
                      {stats.drops.data.map((item: any) => (
                        <tr key={item.id} className="border-b last:border-0 hover:bg-muted/5 transition-colors">
                          <td className="py-3 pl-3">
                            <span className="font-extrabold text-xs text-foreground">{item.title}</span>
                          </td>
                          <td className="py-3 text-xs text-muted-foreground font-semibold">
                            {item.starts_at ? new Date(item.starts_at).toLocaleDateString() : 'N/A'} - {item.ends_at ? new Date(item.ends_at).toLocaleDateString() : 'N/A'}
                          </td>
                          <td className="py-3 text-center">
                            <Badge className="bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-500/20 text-[10px] capitalize">
                              {item.status === 1 ? 'Published' : item.status === 2 ? 'Ended' : item.status === 3 ? 'Cancelled' : 'Draft'}
                            </Badge>
                          </td>
                          <td className="py-3 text-center text-xs font-black text-foreground">
                            {item.drop_price ? `${Number(item.drop_price).toFixed(2)} USD` : 'Use Standard'}
                          </td>
                          <td className="py-3 pr-3 text-right">
                            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                              {item.orders_count} Orders
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : (
                  <div className="flex flex-col items-center justify-center py-16 text-center text-muted-foreground gap-2">
                    <IconInbox className="size-8 text-muted-foreground/45" />
                    <span className="text-xs font-semibold">This product has not been added to any drops yet</span>
                  </div>
                )}
              </div>

              {/* Table 3 Pagination */}
              {stats?.drops?.last_page > 1 && (
                <div className="flex items-center justify-between border-t pt-3.5">
                  <span className="text-[10px] font-semibold text-muted-foreground">
                    Page {stats.drops.current_page} of {stats.drops.last_page}
                  </span>
                  <div className="flex items-center gap-1">
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={dropsPage === 1}
                      onClick={() => setDropsPage((prev) => Math.max(1, prev - 1))}
                      className="h-7 w-7 p-0"
                    >
                      <IconChevronLeft className="size-3.5" />
                    </Button>
                    <Button
                      variant="outline"
                      size="xs"
                      disabled={dropsPage === stats.drops.last_page}
                      onClick={() => setDropsPage((prev) => Math.min(stats.drops.last_page, prev + 1))}
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
