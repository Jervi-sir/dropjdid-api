import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ShowDropController from '@/actions/App/Http/Controllers/Admin/Drops/ShowDropController';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from '@/components/ui/sheet';
import {
  IconCheck,
  IconAlertTriangle,
  IconLoader2,
  IconBan,
} from '@tabler/icons-react';
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
  IconLayoutGrid,
  IconFolder,
  IconChevronLeft,
  IconChevronRight,
} from '@tabler/icons-react';

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
  products_count: number;
  liked_drops_count: number;
  saved_drops_count: number;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedDrops {
  data: Drop[];
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

interface DropListProps {
  drops: PaginatedDrops;
  filters: FilterProps;
  statuses: { [key: number]: string };
  stats: {
    total: number;
    draft: number;
    published: number;
    ended: number;
    cancelled: number;
    rejected: number;
  };
}

export default function DropList({ drops, filters, statuses, stats }: DropListProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [statusVal, setStatusVal] = React.useState(filters.status || 'all');
  const [perPage, setPerPage] = React.useState(filters.per_page?.toString() || '10');

  // Sheet Preview States
  const [selectedDropId, setSelectedDropId] = React.useState<number | null>(null);
  const [sheetOpen, setSheetOpen] = React.useState(false);
  const [loadingDrop, setLoadingDrop] = React.useState(false);
  const [previewDrop, setPreviewDrop] = React.useState<any>(null);
  const [previewProducts, setPreviewProducts] = React.useState<any[]>([]);

  // Sheet Status Management Form States
  const [formStatus, setFormStatus] = React.useState<string>('');
  const [reasonEn, setReasonEn] = React.useState<string>('');
  const [reasonFr, setReasonFr] = React.useState<string>('');
  const [reasonAr, setReasonAr] = React.useState<string>('');
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const [submitSuccess, setSubmitSuccess] = React.useState(false);

  React.useEffect(() => {
    if (selectedDropId && sheetOpen) {
      setLoadingDrop(true);
      setPreviewDrop(null);
      setPreviewProducts([]);
      setSubmitSuccess(false);

      fetch(ShowDropController.show.url(selectedDropId), {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then((res) => res.json())
        .then((data) => {
          setPreviewDrop(data.drop);
          setPreviewProducts(data.products || []);
          setFormStatus(data.drop.status);
          setReasonEn('');
          setReasonFr('');
          setReasonAr('');
        })
        .catch((err) => console.error(err))
        .finally(() => setLoadingDrop(false));
    }
  }, [selectedDropId, sheetOpen]);

  const handleSheetSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitSuccess(false);

    router.put(
      ShowDropController.update.url(selectedDropId!),
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
          // Refetch the preview drop to show updated history
          fetch(ShowDropController.show.url(selectedDropId!), {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          })
            .then((res) => res.json())
            .then((data) => {
              setPreviewDrop(data.drop);
              setPreviewProducts(data.products || []);
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

  // Perform search and apply filters
  const applyFilters = (search = searchTerm, status = statusVal, limit = perPage) => {
    router.get(
      '/admin/drops',
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

  // Debounced search effect
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
    router.get('/admin/drops', {}, { preserveState: true, replace: true });
  };

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Drops', href: '/admin/drops' },
  ];

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
    });
  };

  return (
    <>
      <Head title="Drops Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Header Section */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Drops</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Monitor, moderate, and manage user drops, collections, and statuses.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" onClick={handleClearFilters}>
              <IconRefresh className="size-4" />
              <span>Refresh</span>
            </Button>
            <Button size="sm" className="bg-primary text-primary-foreground hover:bg-primary/90">
              <IconPlus className="size-4" />
              <span>Create Drop</span>
            </Button>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Drops</span>
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
            <span className="text-xs font-semibold uppercase tracking-wider text-indigo-500">Ended</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.ended}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-amber-500">Cancelled</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.cancelled}</span>
          </div>
          <div className="bg-card hover:bg-card/85 transition-colors border rounded-xl p-4 flex flex-col justify-between shadow-xs">
            <span className="text-xs font-semibold uppercase tracking-wider text-rose-500">Rejected</span>
            <span className="text-2xl font-bold text-foreground mt-2">{stats.rejected}</span>
          </div>
        </div>

        {/* Filter and Table Container */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">

          {/* Controls Bar */}
          <div className="p-4 border-b flex flex-col sm:flex-row items-center gap-4 justify-between bg-muted/20">
            <div className="flex flex-1 flex-col sm:flex-row items-center gap-2 w-full">
              <div className="relative w-full sm:max-w-xs">
                <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search drops or creators..."
                  className="pl-8 h-9 w-full bg-background"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>

              <Select value={statusVal} onValueChange={handleStatusChange}>
                <SelectTrigger className="w-full sm:w-44 h-9 bg-background">
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

              {(searchTerm || statusVal !== 'all' || perPage !== '10') && (
                <Button variant="ghost" size="sm" onClick={handleClearFilters} className="text-xs font-semibold hover:bg-muted py-1 h-9 px-3">
                  Clear Filters
                </Button>
              )}
            </div>

            <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
              <span className="text-xs text-muted-foreground whitespace-nowrap">Show</span>
              <Select value={perPage} onValueChange={handlePerPageChange}>
                <SelectTrigger className="w-18 h-9 bg-background">
                  <SelectValue placeholder="10" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                </SelectContent>
              </Select>
              <span className="text-xs text-muted-foreground whitespace-nowrap">per page</span>
            </div>
          </div>

          {/* Table Element */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/40">
                <TableRow>
                  <TableHead className="font-semibold text-xs py-3.5 w-12 pl-6">ID</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5">Drop Details</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5">Creator</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5">Status</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5 text-center">Products</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5 text-center">Likes / Saves</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5">Starts At</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5">Ends At</TableHead>
                  <TableHead className="font-semibold text-xs py-3.5 text-right pr-6">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {drops.data.length > 0 ? (
                  drops.data.map((drop) => (
                    <TableRow key={drop.id} className="hover:bg-muted/30 group transition-colors">
                      <TableCell className="font-mono text-xs text-muted-foreground font-semibold py-4 pl-6">
                        #{drop.id}
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex flex-col">
                          <span className="font-semibold text-sm text-foreground group-hover:text-primary transition-colors">
                            {drop.title || 'Untitled'}
                          </span>
                          <span className="text-xs text-muted-foreground line-clamp-1 max-w-[260px] mt-0.5">
                            {drop.description || 'No description provided.'}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        {drop.creator ? (
                          <div className="flex items-center gap-1.5 text-sm text-foreground">
                            <IconUser className="size-4 text-muted-foreground" />
                            <span className="font-medium">@{drop.creator.username}</span>
                          </div>
                        ) : (
                          <span className="text-xs text-muted-foreground">Anonymous</span>
                        )}
                      </TableCell>
                      <TableCell className="py-4">
                        {getStatusBadge(drop.status)}
                      </TableCell>
                      <TableCell className="py-4 text-center">
                        <Badge variant="secondary" className="px-2 py-0.5 rounded-md font-semibold text-xs">
                          {drop.products_count}
                        </Badge>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex items-center justify-center gap-3 text-xs text-muted-foreground">
                          <span className="flex items-center gap-1">
                            <IconHeart className="size-3 text-rose-500 fill-rose-500/20" />
                            {drop.liked_drops_count}
                          </span>
                          <span className="flex items-center gap-1">
                            <IconBookmark className="size-3 text-blue-500 fill-blue-500/20" />
                            {drop.saved_drops_count}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconCalendar className="size-3.5 text-muted-foreground" />
                          <span>{formatDate(drop.starts_at)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-xs font-medium text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                          <IconCalendar className="size-3.5 text-muted-foreground" />
                          <span>{formatDate(drop.ends_at)}</span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-1.5">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5"
                            onClick={() => {
                              setSelectedDropId(drop.id);
                              setSheetOpen(true);
                            }}
                          >
                            Manage
                          </Button>
                          <Button variant="ghost" size="xs" className="h-7 px-2 text-muted-foreground hover:text-foreground" asChild>
                            <Link href={ShowDropController.show.url(drop.id)}>
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
                          <p className="font-semibold text-sm text-foreground">No drops found</p>
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
          {drops.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {drops.from} to {drops.to} of {drops.total} drops
              </span>

              <div className="flex items-center gap-1.5">
                {drops.links.map((link, idx) => {
                  const isPrev = link.label.includes('Previous');
                  const isNext = link.label.includes('Next');

                  // Style non-page labels or keep as text
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

      {/* Dynamic Drop Management Sheet */}
      <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
        <SheetContent side="right" className="w-full sm:max-w-xl overflow-y-auto bg-card border-l shadow-xl flex flex-col p-0">
          <SheetHeader className="p-6 border-b border-muted/50 bg-muted/10">
            <div className="flex items-center justify-between gap-3">
              <SheetTitle className="text-lg font-extrabold tracking-tight text-foreground">
                Manage Drop Preview #{selectedDropId}
              </SheetTitle>
              {previewDrop && getStatusBadge(previewDrop.status)}
            </div>
            <SheetDescription className="text-xs text-muted-foreground">
              Review stats, products list, and perform quick status adjustments.
            </SheetDescription>
          </SheetHeader>

          {loadingDrop ? (
            <div className="flex-grow flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Loading drop information...</span>
            </div>
          ) : previewDrop ? (
            <div className="flex flex-col p-3 gap-2">

              {/* Drop Overview stats */}
              <div className="flex flex-col gap-3 text-sm border bg-muted/10 p-4 rounded-xl">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  Overview
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Title</span>
                    <span className="font-bold text-foreground text-xs">{previewDrop.title}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Creator</span>
                    <span className="font-bold text-foreground text-xs">
                      {previewDrop.creator ? `@${previewDrop.creator.username}` : 'Anonymous'}
                    </span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Starts At</span>
                    <span className="font-medium text-foreground text-xs">{formatDate(previewDrop.starts_at)}</span>
                  </div>
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] text-muted-foreground font-bold uppercase">Ends At</span>
                    <span className="font-medium text-foreground text-xs">{formatDate(previewDrop.ends_at)}</span>
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

              {/* Linked Products Preview in Sheet */}
              <div className="flex flex-col gap-3 flex-grow">
                <div className="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">
                  Associated Products ({previewProducts.length})
                </div>

                {previewProducts.length > 0 ? (
                  <div className="flex flex-col gap-2.5 max-h-[300px] overflow-y-auto pr-1">
                    {previewProducts.map((prod) => (
                      <div key={prod.id} className="flex items-center justify-between p-2.5 border rounded-lg bg-muted/5 group hover:bg-muted/15 transition-colors">
                        <div className="flex items-center gap-3">
                          <div className="size-10 rounded-md overflow-hidden border shrink-0 bg-muted/10">
                            <img
                              src={prod.image || ''}
                              alt={prod.name}
                              className="w-full h-full object-cover"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=150&q=80';
                              }}
                            />
                          </div>
                          <div className="flex flex-col">
                            <span className="font-semibold text-xs text-foreground group-hover:text-primary transition-colors line-clamp-1">{prod.name}</span>
                            <span className="text-[10px] text-muted-foreground capitalize">{prod.status}</span>
                          </div>
                        </div>
                        <div className="flex flex-col items-end shrink-0">
                          <span className="font-bold text-xs text-emerald-600 dark:text-emerald-400">${prod.drop_price.toFixed(2)}</span>
                          <span className="text-[10px] text-muted-foreground line-through">${prod.original_price.toFixed(2)}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-6 border border-dashed rounded-lg text-xs text-muted-foreground">
                    No products linked to this drop yet.
                  </div>
                )}
              </div>

              <Link href={ShowDropController.show({ drop: previewDrop.id }).url}>
                <Button type="submit" className="w-full h-10 mt-2" variant='outline'>
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
