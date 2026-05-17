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
  const { data, setData, put, processing, errors, reset, recentlySuccessful } = useForm({
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
          reset('rejection_reason_en', 'rejection_reason_fr', 'rejection_reason_ar');
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
      case 'ended':
        return (
          <Badge className="bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80 border-indigo-500/20 border dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30">
            Ended
          </Badge>
        );
      case 'cancelled':
        return (
          <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100/80 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">
            Cancelled
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
            <Link href="/admin/drops" className="hover:text-primary transition-colors">Drops</Link>
            <span>/</span>
            <span className="text-foreground">Manage Drop #{drop.id}</span>
          </div>
          
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <Button variant="outline" size="sm" asChild className="h-9 px-3">
                <Link href="/admin/drops">
                  <IconChevronLeft className="size-4 -ml-1" />
                  <span>Back to Drops</span>
                </Link>
              </Button>
              <div>
                <div className="flex items-center gap-2.5">
                  <h1 className="text-2xl font-extrabold tracking-tight text-foreground">{drop.title || 'Untitled Drop'}</h1>
                  {getStatusBadge(drop.status)}
                </div>
                <p className="text-xs text-muted-foreground mt-1">
                  Manage, review status, reject and audit drop details.
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Layout Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          
          {/* Left Column: Drop Details and Status Management (1/3 width) */}
          <div className="flex flex-col gap-6 lg:col-span-1">
            
            {/* Status Manager Panel */}
            <div className="bg-card border rounded-xl shadow-xs overflow-hidden">
              <div className="p-5 border-b border-muted/50 bg-muted/10">
                <h2 className="font-bold text-sm text-foreground flex items-center gap-2">
                  <IconListDetails className="size-4 text-primary" />
                  <span>Status Management</span>
                </h2>
              </div>
              <div className="p-5">
                <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-semibold text-muted-foreground">Select New Status</label>
                    <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                      <SelectTrigger className="w-full h-10 bg-background border-input">
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent>
                        {Object.entries(statuses).map(([key, label]) => (
                          <SelectItem key={key} value={label}>
                            <span className="capitalize">{label}</span>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.status && <p className="text-xs text-rose-500 font-semibold">{errors.status}</p>}
                  </div>

                  {/* Collapsible Rejection Inputs (Transitions smoothly when 'rejected' selected) */}
                  <div
                    className={`transition-all duration-300 ease-in-out overflow-hidden flex flex-col gap-4 ${
                      data.status === 'rejected' ? 'max-h-[450px] opacity-100 mt-2' : 'max-h-0 opacity-0 pointer-events-none'
                    }`}
                  >
                    <div className="border-t border-rose-200/40 pt-4 flex flex-col gap-3.5">
                      <div className="flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                        <IconAlertTriangle className="size-4" />
                        <span>Rejection Reasons Required</span>
                      </div>

                      {/* English Reason */}
                      <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-medium text-muted-foreground">Reason (English)</label>
                        <Input
                          placeholder="Why is this drop rejected? (English)"
                          value={data.rejection_reason_en}
                          onChange={(e) => setData('rejection_reason_en', e.target.value)}
                          className="h-10 bg-background"
                        />
                        {errors.rejection_reason_en && (
                          <p className="text-xs text-rose-500 font-semibold">{errors.rejection_reason_en}</p>
                        )}
                      </div>

                      {/* French Reason */}
                      <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-medium text-muted-foreground">Reason (French)</label>
                        <Input
                          placeholder="Pourquoi ce drop est rejeté ? (Français)"
                          value={data.rejection_reason_fr}
                          onChange={(e) => setData('rejection_reason_fr', e.target.value)}
                          className="h-10 bg-background"
                        />
                        {errors.rejection_reason_fr && (
                          <p className="text-xs text-rose-500 font-semibold">{errors.rejection_reason_fr}</p>
                        )}
                      </div>

                      {/* Arabic Reason (RTL support!) */}
                      <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-medium text-muted-foreground">Reason (Arabic)</label>
                        <Input
                          dir="rtl"
                          placeholder="ما هو سبب رفض هذه المجموعة؟ (العربية)"
                          value={data.rejection_reason_ar}
                          onChange={(e) => setData('rejection_reason_ar', e.target.value)}
                          className="h-10 bg-background text-right"
                        />
                        {errors.rejection_reason_ar && (
                          <p className="text-xs text-rose-500 font-semibold">{errors.rejection_reason_ar}</p>
                        )}
                      </div>
                    </div>
                  </div>

                  <Button
                    type="submit"
                    disabled={processing}
                    className="w-full h-10 mt-2 bg-primary text-primary-foreground hover:bg-primary/95 transition-all"
                  >
                    {processing ? 'Saving...' : 'Save Changes'}
                  </Button>

                  {recentlySuccessful && (
                    <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-1.5 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                      <IconCheck className="size-4" />
                      <span>Status updated successfully</span>
                    </div>
                  )}
                </form>
              </div>
            </div>

            {/* Drop Info Card */}
            <div className="bg-card border rounded-xl shadow-xs overflow-hidden">
              <div className="p-5 border-b border-muted/50 bg-muted/10">
                <h2 className="font-bold text-sm text-foreground flex items-center gap-2">
                  <IconFileDescription className="size-4 text-primary" />
                  <span>Drop Information</span>
                </h2>
              </div>
              <div className="p-5 flex flex-col gap-4 text-sm">
                
                {/* Description */}
                <div className="flex flex-col gap-1">
                  <span className="text-xs text-muted-foreground font-semibold">Description</span>
                  <p className="text-foreground leading-relaxed bg-muted/20 p-3 rounded-lg border border-muted/30">
                    {drop.description || 'No description provided.'}
                  </p>
                </div>

                {/* Creator Details */}
                <div className="flex items-center gap-3 bg-muted/10 p-3 rounded-lg border">
                  <div className="size-9 bg-primary/10 rounded-full flex items-center justify-center text-primary">
                    <IconUser className="size-5" />
                  </div>
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground font-semibold">Creator</span>
                    <span className="font-semibold text-foreground">
                      {drop.creator ? `@${drop.creator.username}` : 'Anonymous'}
                    </span>
                  </div>
                </div>

                {/* Starts At & Ends At */}
                <div className="grid grid-cols-2 gap-3.5">
                  <div className="flex flex-col gap-1">
                    <span className="text-xs text-muted-foreground font-semibold flex items-center gap-1">
                      <IconCalendar className="size-3.5" />
                      Starts At
                    </span>
                    <span className="font-medium text-xs bg-muted/35 px-2 py-1.5 rounded-md border">
                      {formatDate(drop.starts_at)}
                    </span>
                  </div>
                  <div className="flex flex-col gap-1">
                    <span className="text-xs text-muted-foreground font-semibold flex items-center gap-1">
                      <IconCalendar className="size-3.5" />
                      Ends At
                    </span>
                    <span className="font-medium text-xs bg-muted/35 px-2 py-1.5 rounded-md border">
                      {formatDate(drop.ends_at)}
                    </span>
                  </div>
                </div>

                {/* Drop Images (If present) */}
                {drop.images && drop.images.length > 0 && (
                  <div className="flex flex-col gap-2 mt-2">
                    <span className="text-xs text-muted-foreground font-semibold">Drop Gallery</span>
                    <div className="grid grid-cols-3 gap-2">
                      {drop.images.map((img) => (
                        <div key={img.id} className="relative aspect-square border rounded-lg overflow-hidden bg-muted/20">
                          <img
                            src={img.image}
                            alt="Drop"
                            className="w-full h-full object-cover"
                            onError={(e) => {
                              (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=300&q=80';
                            }}
                          />
                          {img.is_main && (
                            <Badge className="absolute top-1 left-1 text-[9px] h-4 px-1.5 bg-primary/90 text-primary-foreground font-semibold">
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
            {drop.rejection_reasons && drop.rejection_reasons.length > 0 && (
              <div className="bg-card border border-rose-200/50 rounded-xl shadow-xs overflow-hidden dark:border-rose-950/40">
                <div className="p-5 border-b border-rose-100 bg-rose-50/20 dark:bg-rose-950/10 dark:border-rose-950/30">
                  <h2 className="font-bold text-sm text-rose-700 dark:text-rose-400 flex items-center gap-2">
                    <IconBan className="size-4" />
                    <span>Rejection History</span>
                  </h2>
                </div>
                <div className="p-5 flex flex-col gap-4">
                  <div className="relative pl-4 border-l-2 border-rose-200 dark:border-rose-900 flex flex-col gap-5">
                    {drop.rejection_reasons.map((reason, idx) => (
                      <div key={reason.id || idx} className="relative flex flex-col gap-2">
                        {/* Dot marker */}
                        <div className="absolute -left-[21px] top-1.5 size-2.5 rounded-full bg-rose-500 border-2 border-card" />
                        
                        <div className="bg-rose-500/5 border border-rose-200/30 rounded-lg p-3 flex flex-col gap-2">
                          <div className="flex items-center justify-between text-[10px] text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider">
                            <span>Audit #{reason.id || drop.rejection_reasons.length - idx}</span>
                            {idx === 0 && <span className="bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 px-1.5 py-0.5 rounded-sm">Current</span>}
                          </div>
                          
                          <div className="flex flex-col gap-1.5 text-xs">
                            <p className="text-foreground font-medium"><span className="text-muted-foreground mr-1.5 font-normal">EN:</span>{reason.en}</p>
                            <p className="text-foreground font-medium"><span className="text-muted-foreground mr-1.5 font-normal">FR:</span>{reason.fr}</p>
                            <p className="text-foreground font-medium text-right" dir="rtl"><span className="text-muted-foreground ml-1.5 font-normal" dir="ltr">AR:</span>{reason.ar}</p>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </div>
          
          {/* Right Column: Linked Products (2/3 width) */}
          <div className="lg:col-span-2 flex flex-col gap-6">
            <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">
              <div className="p-5 border-b border-muted/50 bg-muted/10 flex items-center justify-between">
                <h2 className="font-bold text-sm text-foreground flex items-center gap-2">
                  <IconFolder className="size-4 text-primary" />
                  <span>Linked Products ({products.length})</span>
                </h2>
                <Badge variant="outline" className="font-semibold text-xs py-0.5 px-2.5">
                  Collection
                </Badge>
              </div>

              {products.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader className="bg-muted/30">
                      <TableRow>
                        <TableHead className="font-semibold text-xs py-3.5 w-12 pl-6">ID</TableHead>
                        <TableHead className="font-semibold text-xs py-3.5">Product Details</TableHead>
                        <TableHead className="font-semibold text-xs py-3.5">Store / Owner</TableHead>
                        <TableHead className="font-semibold text-xs py-3.5 text-right">Original Price</TableHead>
                        <TableHead className="font-semibold text-xs py-3.5 text-right">Drop Price</TableHead>
                        <TableHead className="font-semibold text-xs py-3.5 pr-6 text-center">Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {products.map((product) => (
                        <TableRow key={product.id} className="hover:bg-muted/20 group transition-colors">
                          <TableCell className="font-mono text-xs text-muted-foreground font-semibold py-4 pl-6">
                            #{product.id}
                          </TableCell>
                          <TableCell className="py-4">
                            <div className="flex items-center gap-3">
                              <div className="size-11 rounded-lg overflow-hidden border bg-muted/10 shrink-0">
                                <img
                                  src={product.image || ''}
                                  alt={product.name}
                                  className="w-full h-full object-cover"
                                  onError={(e) => {
                                    (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                                  }}
                                />
                              </div>
                              <span className="font-semibold text-sm text-foreground group-hover:text-primary transition-colors">
                                {product.name}
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="py-4">
                            {product.store ? (
                              <div className="flex flex-col gap-0.5 text-sm">
                                <span className="font-semibold text-foreground">{product.store.name}</span>
                                <span className="text-xs text-muted-foreground">@{product.store.username || 'unknown'}</span>
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground">No Store</span>
                            )}
                          </TableCell>
                          <TableCell className="py-4 text-right font-medium text-muted-foreground text-sm line-through">
                            ${product.original_price.toFixed(2)}
                          </TableCell>
                          <TableCell className="py-4 text-right font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                            ${product.drop_price.toFixed(2)}
                          </TableCell>
                          <TableCell className="py-4 pr-6 text-center">
                            <Badge variant="outline" className="capitalize text-xs font-semibold px-2 py-0.5 border-neutral-300 dark:border-neutral-700">
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
                    <IconFolder className="size-10 text-muted-foreground/50 stroke-[1.5]" />
                    <div className="flex flex-col gap-0.5">
                      <p className="font-semibold text-sm text-foreground">No products linked</p>
                      <p className="text-xs">There are no products associated with this drop yet.</p>
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
