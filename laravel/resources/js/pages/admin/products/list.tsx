import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowProductController from '@/actions/App/Http/Controllers/Admin/Products/ShowProductController';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from '@/components/ui/sheet';
import {
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
} from '@/components/ui/table';
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
  IconSearch,
  IconCalendar,
  IconUser,
  IconHeart,
  IconBookmark,
  IconRefresh,
  IconPlus,
  IconFolder,
  IconChevronLeft,
  IconChevronRight,
  IconCheck,
  IconAlertTriangle,
  IconLoader2,
  IconShoppingBag,
  IconTag,
  IconCircleCheck,
} from '@tabler/icons-react';
import { History } from 'lucide-react';

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
  } | null;
  quality: {
    id: number;
    en: string;
  } | null;
  image: string | null;
  liked_count: number;
  saved_count: number;
  order_items_count: number;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedProducts {
  data: Product[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface FilterProps {
  search: string;
  status: string;
  per_page: number;
}

interface ProductListProps {
  products: PaginatedProducts;
  filters: FilterProps;
  statuses: { [key: number]: string };
  stats: {
    total: number;
    draft: number;
    published: number;
    archived: number;
    rejected: number;
  };
}

export default function ProductList({ products, filters, statuses, stats }: ProductListProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
  const [perPage, setPerPage] = React.useState(filters.per_page?.toString() || '10');

  // Sheet Preview States
  const [selectedProductId, setSelectedProductId] = React.useState<number | null>(null);
  const [sheetOpen, setSheetOpen] = React.useState(false);
  const [loadingProduct, setLoadingProduct] = React.useState(false);
  const [previewProduct, setPreviewProduct] = React.useState<any>(null);

  // Sheet Status Management Form States
  const [formStatus, setFormStatus] = React.useState<string>('');
  const [reasonEn, setReasonEn] = React.useState<string>('');
  const [reasonFr, setReasonFr] = React.useState<string>('');
  const [reasonAr, setReasonAr] = React.useState<string>('');
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const [submitSuccess, setSubmitSuccess] = React.useState(false);

  React.useEffect(() => {
    if (selectedProductId && sheetOpen) {
      setLoadingProduct(true);
      setPreviewProduct(null);
      setSubmitSuccess(false);

      fetch(ShowProductController.show.url(selectedProductId), {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then((res) => res.json())
        .then((data) => {
          setPreviewProduct(data.product);
          setFormStatus(data.product.status);
          setReasonEn('');
          setReasonFr('');
          setReasonAr('');
        })
        .catch((err) => console.error(err))
        .finally(() => setLoadingProduct(false));
    }
  }, [selectedProductId, sheetOpen]);

  const handleSheetSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitSuccess(false);

    router.put(
      ShowProductController.update.url(selectedProductId!),
      {
        status: formStatus,
        rejection_reason_en: reasonEn,
        rejection_reason_fr: reasonFr,
        rejection_reason_ar: reasonAr,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          setSubmitSuccess(true);
          // Refetch to show updated details
          fetch(ShowProductController.show.url(selectedProductId!), {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          })
            .then((res) => res.json())
            .then((data) => {
              setPreviewProduct(data.product);
            });

          if (formStatus !== 'rejected') {
            setReasonEn('');
            setReasonFr('');
            setReasonAr('');
          }
        },
        onFinish: () => {
          setIsSubmitting(false);
        },
      }
    );
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

  const applyFilters = (search = searchTerm, status = statusVal, limit = perPage) => {
    router.get(
      '/admin/products',
      {
        search: search || undefined,
        status: status === 'all' ? undefined : status,
        per_page: limit === '10' ? undefined : limit,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      }
    );
  };

  React.useEffect(() => {
    const timer = setTimeout(() => {
      if (searchTerm !== (filters.search || '')) {
        applyFilters(searchTerm, statusVal, perPage);
      }
    }, 450);
    return () => clearTimeout(timer);
  }, [searchTerm]);

  const handleStatusChange = (value: string) => {
    setStatusVal(value);
    applyFilters(searchTerm, value, perPage);
  };

  const handlePerPageChange = (value: string) => {
    setPerPage(value);
    applyFilters(searchTerm, statusVal, value);
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setStatusVal('all');
    setPerPage('10');
    router.get('/admin/products', {}, { preserveState: true, replace: true });
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not set';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  return (
    <>
      <Head title="Products Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Header Section */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Products</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Review store products, manage quality labels, and moderate publishing states.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" onClick={handleClearFilters}>
              <IconRefresh className="size-4" />
              <span>Refresh</span>
            </Button>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Products</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.total}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Published</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.published}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Drafts</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.draft}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-indigo-500">Archived</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.archived}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-rose-500">Rejected</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.rejected}</span>
          </div>
        </div>

        {/* Filters and Table Container */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">

          {/* Controls Bar */}
          <div className="p-4 border-b flex flex-col sm:flex-row items-center gap-4 justify-between bg-muted/20">
            <div className="flex flex-1 flex-col sm:flex-row items-center gap-2 w-full">
              <div className="relative w-full sm:max-w-xs">
                <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search products..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-9 h-10 w-full bg-background"
                />
              </div>

              <div className="w-full sm:w-44">
                <Select value={statusVal} onValueChange={handleStatusChange}>
                  <SelectTrigger className="h-10 bg-background border-input">
                    <SelectValue placeholder="All Statuses" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    {Object.entries(statuses).map(([key, value]) => (
                      <SelectItem key={key} value={value}>
                        <span className="capitalize">{value}</span>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="flex items-center gap-2 shrink-0">
              <span className="text-xs text-muted-foreground font-semibold">Limit</span>
              <Select value={perPage} onValueChange={handlePerPageChange}>
                <SelectTrigger className="h-10 w-20 bg-background">
                  <SelectValue placeholder="10" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {/* Table Element */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/15 border-b">
                <TableRow>
                  <TableHead className="w-[80px] pl-6 py-4">Image</TableHead>
                  <TableHead className="min-w-[180px] py-4">Product Info</TableHead>
                  <TableHead className="py-4">Seller Store</TableHead>
                  <TableHead className="py-4 text-right">Pricing Stack</TableHead>
                  <TableHead className="py-4">Category</TableHead>
                  <TableHead className="py-4">Quality</TableHead>
                  <TableHead className="py-4">Created</TableHead>
                  <TableHead className="py-4">Status</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[160px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {products.data.length > 0 ? (
                  products.data.map((prod) => (
                    <TableRow key={prod.id} className="hover:bg-muted/5 group/row transition-colors">
                      <TableCell className="pl-6 py-4">
                        <div className="size-11 rounded-lg overflow-hidden border shrink-0 bg-muted/10">
                          <img
                            src={prod.image || ''}
                            alt={prod.name}
                            className="w-full h-full object-cover"
                            onError={(e) => {
                              (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                            }}
                          />
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-col max-w-[240px]">
                          <span className="font-bold text-foreground text-sm truncate">{prod.name}</span>
                          <span className="text-xs text-muted-foreground line-clamp-1 mt-0.5">{prod.description}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-col">
                          <span className="font-semibold text-foreground text-xs">
                            {prod.store ? prod.store.name : 'Unknown Store'}
                          </span>
                          <span className="text-[10px] text-muted-foreground">
                            {prod.store?.username ? `@${prod.store.username}` : ''}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-right">
                        <div className="flex flex-col justify-end items-end gap-0.5">
                          <div className="flex items-center gap-1">
                            <span className="text-[10px] text-muted-foreground font-semibold">Orig:</span>
                            <span className="font-bold text-xs text-foreground">${prod.original_price.toFixed(2)}</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <span className="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Show:</span>
                            <span className="font-bold text-xs text-emerald-600 dark:text-emerald-400">${prod.show_price.toFixed(2)}</span>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {prod.category ? (
                          <Badge variant="outline" className="capitalize text-[10px] py-0.5 px-2 bg-muted/20 border-muted">
                            {prod.category.en}
                          </Badge>
                        ) : (
                          <span className="text-xs text-muted-foreground">None</span>
                        )}
                      </TableCell>
                      <TableCell className="py-4">
                        {prod.quality ? (
                          <Badge variant="outline" className="capitalize text-[10px] py-0.5 px-2 bg-indigo-500/5 text-indigo-600 dark:text-indigo-400 border-indigo-500/10">
                            {prod.quality.en}
                          </Badge>
                        ) : (
                          <span className="text-xs text-muted-foreground">None</span>
                        )}
                      </TableCell>
                      <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconCalendar className="size-3.5 text-muted-foreground" />
                          <span>{formatDate(prod.created_at)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {getStatusBadge(prod.status)}
                      </TableCell>
                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-1.5">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5"
                            onClick={() => {
                              setSelectedProductId(prod.id);
                              setSheetOpen(true);
                            }}
                          >
                            Manage
                          </Button>
                          <Button variant="ghost" size="xs" className="h-7 px-2 text-muted-foreground hover:text-foreground" asChild>
                            <Link href={ShowProductController.show.url(prod.id)}>
                              Details
                            </Link>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={9} className="py-12 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconFolder className="size-10 text-muted-foreground/55 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No products found</p>
                          <p className="text-xs">Try adjusting your filters or search query.</p>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination Controls */}
          {products.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {products.from} to {products.to} of {products.total} products
              </span>

              <div className="flex items-center gap-1.5">
                {products.links.map((link, idx) => {
                  const isPrev = link.label.includes('Previous');
                  const isNext = link.label.includes('Next');

                  let label = link.label;
                  if (isPrev) label = 'Previous';
                  if (isNext) label = 'Next';

                  const isDisabled = !link.url;

                  return (
                    <Link
                      key={idx}
                      href={link.url || '#'}
                      disabled={isDisabled}
                      preserveScroll
                      preserveState
                      className={`
                        inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none 
                        ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'}
                        ${link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm' : 'border border-border bg-card text-foreground'}
                      `}
                    >
                      {isPrev && <IconChevronLeft className="size-3.5 -ml-0.5" />}
                      <span>{label}</span>
                      {isNext && <IconChevronRight className="size-3.5 -mr-0.5" />}
                    </Link>
                  );
                })}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Dynamic Product Management Sheet */}
      <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
        <SheetContent side="right" className="w-full sm:max-w-xl overflow-y-auto bg-card border-l shadow-xl flex flex-col p-0">
          <SheetHeader className="p-6 border-b border-muted/50 bg-muted/10">
            <div className="flex items-center justify-between gap-3">
              <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                Manage Product Preview #{selectedProductId}
              </SheetTitle>
              {previewProduct && getStatusBadge(previewProduct.status)}
            </div>
            <SheetDescription className="text-xs text-muted-foreground">
              Review details, images, seller info, and perform quick status adjustments.
            </SheetDescription>
          </SheetHeader>

          {loadingProduct ? (
            <div className="flex-grow flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Loading product information...</span>
            </div>
          ) : previewProduct ? (
            <div className="flex flex-col p-4 gap-4">

              {/* Product Overview stats */}
              <div className="flex flex-col gap-3 text-sm border bg-muted/10 p-4 rounded-xl">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  Product Overview
                </div>
                <div className="flex items-start gap-4">
                  <div className="size-20 rounded-lg overflow-hidden border shrink-0 bg-muted/10 shadow-sm">
                    <img
                      src={previewProduct.images?.[0]?.image || ''}
                      alt={previewProduct.name}
                      className="w-full h-full object-cover"
                      onError={(e) => {
                        (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                      }}
                    />
                  </div>
                  <div className="flex flex-col gap-1">
                    <span className="font-bold text-foreground text-sm">{previewProduct.name}</span>
                    <span className="text-xs text-muted-foreground line-clamp-2">{previewProduct.description}</span>
                  </div>
                </div>

                <hr className="border-muted/50 my-1" />

                <div className="grid grid-cols-2 gap-4">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Seller Store</span>
                    <span className="font-bold text-foreground text-xs">
                      {previewProduct.store ? previewProduct.store.name : 'Unknown Store'}
                    </span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Category</span>
                    <span className="font-bold text-foreground text-xs">
                      {previewProduct.category ? previewProduct.category.en : 'None'}
                    </span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Original Price</span>
                    <span className="font-medium text-foreground text-xs">${previewProduct.original_price?.toFixed(2)}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Show Price</span>
                    <span className="font-bold text-emerald-600 dark:text-emerald-400 text-xs">${previewProduct.show_price?.toFixed(2)}</span>
                  </div>
                </div>
              </div>

              {/* Status Update Form inside sheet */}
              <form onSubmit={handleSheetSubmit} className="flex flex-col gap-4 border p-4 rounded-xl bg-card">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  Status Updater
                </div>
                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-semibold text-muted-foreground">Select New Status</label>
                  <Select value={formStatus} onValueChange={setFormStatus}>
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
                </div>

                {/* Localized Rejection Fields (Collapsible inside sheet) */}
                <div
                  className={`transition-all duration-300 ease-in-out overflow-hidden flex flex-col gap-3.5 ${formStatus === 'rejected' ? 'max-h-[350px] opacity-100 mt-2' : 'max-h-0 opacity-0 pointer-events-none'
                    }`}
                >
                  <div className="border-t pt-3 flex flex-col gap-3">
                    <div className="flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                      <IconAlertTriangle className="size-4" />
                      <span>Rejection Reasons Required</span>
                    </div>

                    <div className="flex flex-col gap-1">
                      <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (English)</label>
                      <Input
                        placeholder="Rejection reason in English"
                        value={reasonEn}
                        onChange={(e) => setReasonEn(e.target.value)}
                        className="h-9 bg-background"
                      />
                    </div>
                    <div className="flex flex-col gap-1">
                      <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (French)</label>
                      <Input
                        placeholder="Raison du rejet en français"
                        value={reasonFr}
                        onChange={(e) => setReasonFr(e.target.value)}
                        className="h-9 bg-background"
                      />
                    </div>
                    <div className="flex flex-col gap-1">
                      <label className="text-[10px] font-bold uppercase text-muted-foreground">Reason (Arabic)</label>
                      <Input
                        dir="rtl"
                        placeholder="سبب الرفض باللغة العربية"
                        value={reasonAr}
                        onChange={(e) => setReasonAr(e.target.value)}
                        className="h-9 bg-background text-right"
                      />
                    </div>
                  </div>
                </div>

                <Button type="submit" disabled={isSubmitting} className="w-full h-10 mt-1">
                  {isSubmitting ? 'Saving changes...' : 'Save Status'}
                </Button>

                {submitSuccess && (
                  <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-1.5 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                    <IconCheck className="size-4" />
                    <span>Status updated successfully</span>
                  </div>
                )}
              </form>

              {/* Product Gallery Section */}
              {previewProduct.images && previewProduct.images.length > 1 && (
                <div className="flex flex-col gap-2">
                  <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground">
                    Product Gallery ({previewProduct.images.length} images)
                  </div>
                  <div className="grid grid-cols-4 gap-2">
                    {previewProduct.images.map((img: any) => (
                      <div key={img.id} className="aspect-square rounded-lg overflow-hidden border bg-muted/10 hover:border-primary transition-colors">
                        <img
                          src={img.image}
                          alt="Product detail"
                          className="w-full h-full object-cover"
                        />
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Historical Audit Timeline (Rejections history) */}
              {previewProduct.rejection_reasons && previewProduct.rejection_reasons.length > 0 && (
                <div className="flex flex-col gap-2.5">
                  <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground">
                    Historical Audit Timeline
                  </div>
                  <div className="flex flex-col gap-3 max-h-[180px] overflow-y-auto pr-1 border rounded-lg p-3 bg-muted/5">
                    {previewProduct.rejection_reasons.map((audit: any, index: number) => (
                      <div key={audit.id || index} className="flex items-start gap-2 text-xs border-b last:border-0 pb-2 last:pb-0">
                        <div className="size-5 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0 mt-0.5">
                          <History className="size-3.5" />
                        </div>
                        <div className="flex flex-col flex-1 gap-1">
                          <span className="font-bold text-foreground">Audit #{audit.id || index + 1}</span>
                          <div className="flex flex-col gap-0.5 text-muted-foreground">
                            {audit.en && <div><strong className="text-[10px] uppercase text-foreground">EN:</strong> {audit.en}</div>}
                            {audit.fr && <div><strong className="text-[10px] uppercase text-foreground">FR:</strong> {audit.fr}</div>}
                            {audit.ar && <div className="text-right" dir="rtl"><strong className="text-[10px] uppercase text-foreground">AR:</strong> {audit.ar}</div>}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Show Full Details Button */}
              <Link href={ShowProductController.show.url(previewProduct.id)}>
                <Button type="button" className="w-full h-10 mt-2" variant="outline">
                  Show full details
                </Button>
              </Link>

            </div>
          ) : null}
        </SheetContent>
      </Sheet>
    </>
  );
}