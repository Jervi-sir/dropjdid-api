import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ListLabelsController from '@/actions/App/Http/Controllers/Admin/Labels/ListLabelsController';
import UpsertLabelController from '@/actions/App/Http/Controllers/Admin/Labels/UpsertLabelController';
import ShowLabelController from '@/actions/App/Http/Controllers/Admin/Labels/ShowLabelController';
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
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import {
  IconSearch,
  IconPlus,
  IconEdit,
  IconTrash,
  IconTags,
  IconChevronLeft,
  IconChevronRight,
  IconRefresh,
  IconGlobe,
  IconAlertTriangle,
} from '@tabler/icons-react';

interface Label {
  id: number;
  code: string;
  en: string;
  fr: string;
  ar: string;
  keywords_count: number;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedLabels {
  data: Label[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface LabelListProps {
  labels: PaginatedLabels;
  filters: {
    search: string;
    per_page: number;
  };
}

export default function LabelList({ labels, filters }: LabelListProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');

  // Dialog state
  const [dialogOpen, setDialogOpen] = React.useState(false);
  const [editingLabel, setEditingLabel] = React.useState<Label | null>(null);

  // Form hooks
  const { data, setData, post, put, delete: destroy, processing, errors, reset, clearErrors } = useForm({
    code: '',
    en: '',
    fr: '',
    ar: '',
  });

  const openAddDialog = () => {
    setEditingLabel(null);
    reset();
    clearErrors();
    setDialogOpen(true);
  };

  const openEditDialog = (label: Label) => {
    setEditingLabel(label);
    setData({
      code: label.code,
      en: label.en,
      fr: label.fr,
      ar: label.ar,
    });
    clearErrors();
    setDialogOpen(true);
  };

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingLabel) {
      put(UpsertLabelController.update.url(editingLabel.id), {
        onSuccess: () => {
          setDialogOpen(false);
          reset();
        },
      });
    } else {
      post(UpsertLabelController.store.url(), {
        onSuccess: () => {
          setDialogOpen(false);
          reset();
        },
      });
    }
  };

  const handleDeleteLabel = (labelId: number) => {
    if (confirm('Are you sure you want to delete this label and all its associated keywords?')) {
      router.delete(UpsertLabelController.destroy.url(labelId));
    }
  };

  const applySearch = (search = searchTerm) => {
    router.get(
      '/admin/labels',
      { search: search || undefined },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  };

  React.useEffect(() => {
    const timer = setTimeout(() => {
      if (searchTerm !== (filters.search || '')) {
        applySearch(searchTerm);
      }
    }, 450);
    return () => clearTimeout(timer);
  }, [searchTerm]);

  return (
    <>
      <Head title="Labels Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Page Title & Add Button */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Catalog Labels</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Group and catalog feeds using dynamic localized translation fields and searchable tags.
            </p>
          </div>
          <div>
            <Button className="h-10 px-4 font-bold gap-1.5 shadow-md shrink-0" onClick={openAddDialog}>
              <IconPlus className="size-4 stroke-[2.5]" />
              <span>Add New Label</span>
            </Button>
          </div>
        </div>

        {/* Filters and Labels Table */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">

          {/* Search bar */}
          <div className="p-4 border-b flex flex-col sm:flex-row items-center gap-4 justify-between bg-muted/20">
            <div className="relative w-full sm:max-w-xs">
              <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search code or translation..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9 h-10 w-full bg-background"
              />
            </div>
            <Button variant="outline" size="sm" onClick={() => applySearch(searchTerm)}>
              <IconRefresh className="size-4" />
              <span>Refresh</span>
            </Button>
          </div>

          {/* Table Element */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/15 border-b">
                <TableRow>
                  <TableHead className="pl-6 py-4">System Code</TableHead>
                  <TableHead className="py-4">
                    <div className="flex items-center gap-1">
                      <IconGlobe className="size-3.5" />
                      <span>English</span>
                    </div>
                  </TableHead>
                  <TableHead className="py-4">French</TableHead>
                  <TableHead className="py-4 text-right pr-12">Arabic</TableHead>
                  <TableHead className="py-4 text-center">Associated Keywords</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[240px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {labels.data.length > 0 ? (
                  labels.data.map((lbl) => (
                    <TableRow key={lbl.id} className="hover:bg-muted/5 group/row transition-colors">
                      <TableCell className="pl-6 py-4 font-extrabold text-foreground text-xs uppercase tracking-wider">
                        {lbl.code}
                      </TableCell>
                      <TableCell className="py-4 text-xs font-semibold text-foreground">
                        {lbl.en}
                      </TableCell>
                      <TableCell className="py-4 text-xs text-muted-foreground">
                        {lbl.fr}
                      </TableCell>
                      <TableCell className="py-4 text-xs text-muted-foreground text-right pr-12 font-medium" dir="rtl">
                        {lbl.ar}
                      </TableCell>
                      <TableCell className="py-4 text-center">
                        <Badge variant="outline" className="px-2.5 py-0.5 bg-muted/10 font-bold text-xs">
                          {lbl.keywords_count} Keywords
                        </Badge>
                      </TableCell>
                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-1.5">
                          <Button variant="outline" size="xs" className="h-7 px-2.5" asChild>
                            <Link href={ShowLabelController.url(lbl.id)}>
                              Keywords
                            </Link>
                          </Button>
                          <Button
                            variant="ghost"
                            size="xs"
                            className="h-7 px-2 text-muted-foreground hover:text-foreground"
                            onClick={() => openEditDialog(lbl)}
                          >
                            <IconEdit className="size-4" />
                          </Button>
                          <Button
                            variant="ghost"
                            size="xs"
                            className="h-7 px-2 text-muted-foreground hover:text-rose-600"
                            onClick={() => handleDeleteLabel(lbl.id)}
                          >
                            <IconTrash className="size-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={6} className="py-12 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconTags className="size-10 text-muted-foreground/55 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No labels found</p>
                          <p className="text-xs">Create a new label or adjust your filter options.</p>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination links */}
          {labels.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {labels.from} to {labels.to} of {labels.total} labels
              </span>

              <div className="flex items-center gap-1.5">
                {labels.links.map((link, idx) => {
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

      {/* Add / Edit Label Dialog */}
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-lg bg-card">
          <form onSubmit={handleFormSubmit} className="flex flex-col gap-4">
            <DialogHeader>
              <DialogTitle className="text-lg font-extrabold text-foreground">
                {editingLabel ? 'Edit Label' : 'Add New Label'}
              </DialogTitle>
              <DialogDescription className="text-xs text-muted-foreground">
                Define the code and translations across languages to classify feed assets dynamically.
              </DialogDescription>
            </DialogHeader>

            <div className="grid grid-cols-1 gap-4 py-2">
              {/* System Code */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">System Code</label>
                <Input
                  placeholder="e.g. SEASON_DEAL"
                  value={data.code}
                  onChange={(e) => setData('code', e.target.value.toUpperCase())}
                  disabled={!!editingLabel}
                  required
                  className="h-10 bg-background"
                />
                {errors.code && <span className="text-xs text-rose-500 font-semibold">{errors.code}</span>}
              </div>

              {/* English */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">English (EN)</label>
                <Input
                  placeholder="e.g. Season Deals"
                  value={data.en}
                  onChange={(e) => setData('en', e.target.value)}
                  required
                  className="h-10 bg-background"
                />
                {errors.en && <span className="text-xs text-rose-500 font-semibold">{errors.en}</span>}
              </div>

              {/* French */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">French (FR)</label>
                <Input
                  placeholder="e.g. Offres de saison"
                  value={data.fr}
                  onChange={(e) => setData('fr', e.target.value)}
                  required
                  className="h-10 bg-background"
                />
                {errors.fr && <span className="text-xs text-rose-500 font-semibold">{errors.fr}</span>}
              </div>

              {/* Arabic */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider text-right">Arabic (AR)</label>
                <Input
                  placeholder="مثال: عروض الموسم"
                  value={data.ar}
                  onChange={(e) => setData('ar', e.target.value)}
                  required
                  dir="rtl"
                  className="h-10 bg-background text-right"
                />
                {errors.ar && <span className="text-xs text-rose-500 font-semibold text-right">{errors.ar}</span>}
              </div>
            </div>

            <DialogFooter className="mt-2 border-t pt-4">
              <Button type="button" variant="outline" onClick={() => setDialogOpen(false)} disabled={processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={processing} className="font-semibold shadow-xs">
                {processing ? 'Saving...' : editingLabel ? 'Save Changes' : 'Create Label'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </>
  );
}
