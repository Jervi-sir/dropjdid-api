import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import UpsertLabelController from '@/actions/App/Http/Controllers/Admin/Labels/UpsertLabelController';
import UpsertKeywordController from '@/actions/App/Http/Controllers/Admin/Labels/UpsertKeywordController';
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
  IconArrowLeft,
  IconPlus,
  IconEdit,
  IconTrash,
  IconKey,
  IconCheck,
  IconGlobe,
  IconAlertTriangle,
  IconSettings,
} from '@tabler/icons-react';

interface Label {
  id: number;
  code: string;
  en: string;
  fr: string;
  ar: string;
}

interface Keyword {
  id: number;
  code: string;
  products_count: number;
  created_at: string;
}

interface KeywordListProps {
  label: Label;
  keywords: Keyword[];
}

export default function KeywordList({ label, keywords }: KeywordListProps) {
  // Label Edit Form
  const labelForm = useForm({
    code: label.code,
    en: label.en,
    fr: label.fr,
    ar: label.ar,
  });

  // Keyword Add/Edit States
  const [keywordDialogOpen, setKeywordDialogOpen] = React.useState(false);
  const [editingKeyword, setEditingKeyword] = React.useState<Keyword | null>(null);

  // Keyword Form
  const keywordForm = useForm({
    code: '',
  });

  const handleLabelSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    labelForm.put(UpsertLabelController.update.url(label.id), {
      preserveScroll: true,
    });
  };

  const openAddKeywordDialog = () => {
    setEditingKeyword(null);
    keywordForm.reset();
    keywordForm.clearErrors();
    setKeywordDialogOpen(true);
  };

  const openEditKeywordDialog = (kw: Keyword) => {
    setEditingKeyword(kw);
    keywordForm.setData({
      code: kw.code,
    });
    keywordForm.clearErrors();
    setKeywordDialogOpen(true);
  };

  const handleKeywordSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingKeyword) {
      keywordForm.put(
        UpsertKeywordController.update.url({ label: label.id, keyword: editingKeyword.id }),
        {
          onSuccess: () => {
            setKeywordDialogOpen(false);
            keywordForm.reset();
          },
        }
      );
    } else {
      keywordForm.post(
        UpsertKeywordController.store.url(label.id),
        {
          onSuccess: () => {
            setKeywordDialogOpen(false);
            keywordForm.reset();
          },
        }
      );
    }
  };

  const handleDeleteKeyword = (keywordId: number) => {
    if (confirm('Are you sure you want to delete this keyword and detach it from all associated products?')) {
      router.delete(UpsertKeywordController.destroy.url({ label: label.id, keyword: keywordId }));
    }
  };

  return (
    <>
      <Head title={`Manage Keywords - Label ${label.code}`} />
      <div className="flex flex-col gap-6 p-4 lg:p-8 ">

        {/* Navigation Breadcrumbs */}
        <div>
          <Link
            href="/admin/labels"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <IconArrowLeft className="size-4" />
            <span>Back to Labels</span>
          </Link>
        </div>

        {/* Title Banner */}
        <div className="border-b pb-6 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
              Keywords Registry
            </h1>
            <p className="text-sm text-muted-foreground mt-1">
              Configure and associate keywords mapping to label feed: <strong className="text-foreground">{label.code}</strong>.
            </p>
          </div>
        </div>

        {/* Two-Column Workspace */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

          {/* Left Column: Label Editor form (lg:col-span-5) */}
          <div className="lg:col-span-5 flex flex-col gap-6">
            <form onSubmit={handleLabelSubmit} className="bg-card border rounded-2xl p-6 shadow-xs flex flex-col gap-5">
              <div className="flex items-center gap-2 border-b pb-4">
                <IconSettings className="size-5 text-primary" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Label Settings</h3>
              </div>

              {/* System Code */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">System Code</label>
                <Input
                  placeholder="LABEL_CODE"
                  value={labelForm.data.code}
                  onChange={(e) => labelForm.setData('code', e.target.value.toUpperCase())}
                  required
                  className="h-10 bg-background"
                />
                {labelForm.errors.code && <span className="text-xs text-rose-500 font-semibold">{labelForm.errors.code}</span>}
              </div>

              {/* English */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">English (EN)</label>
                <Input
                  placeholder="English translation"
                  value={labelForm.data.en}
                  onChange={(e) => labelForm.setData('en', e.target.value)}
                  required
                  className="h-10 bg-background"
                />
                {labelForm.errors.en && <span className="text-xs text-rose-500 font-semibold">{labelForm.errors.en}</span>}
              </div>

              {/* French */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">French (FR)</label>
                <Input
                  placeholder="French translation"
                  value={labelForm.data.fr}
                  onChange={(e) => labelForm.setData('fr', e.target.value)}
                  required
                  className="h-10 bg-background"
                />
                {labelForm.errors.fr && <span className="text-xs text-rose-500 font-semibold">{labelForm.errors.fr}</span>}
              </div>

              {/* Arabic */}
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider text-right">Arabic (AR)</label>
                <Input
                  placeholder="Arabic translation"
                  value={labelForm.data.ar}
                  onChange={(e) => labelForm.setData('ar', e.target.value)}
                  required
                  dir="rtl"
                  className="h-10 bg-background text-right"
                />
                {labelForm.errors.ar && <span className="text-xs text-rose-500 font-semibold text-right">{labelForm.errors.ar}</span>}
              </div>

              <Button type="submit" disabled={labelForm.processing} className="w-full h-10 mt-1">
                {labelForm.processing ? 'Syncing Label Settings...' : 'Update Label Information'}
              </Button>

              {labelForm.recentlySuccessful && (
                <div className="flex items-center gap-2 justify-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold py-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                  <IconCheck className="size-4 shrink-0" />
                  <span>Label translations synchronized successfully</span>
                </div>
              )}
            </form>
          </div>

          {/* Right Column: Keywords Manager Table (lg:col-span-7) */}
          <div className="lg:col-span-7 flex flex-col gap-6">
            <div className="bg-card border rounded-2xl shadow-xs overflow-hidden flex flex-col">

              {/* Header inside table */}
              <div className="p-5 border-b flex items-center justify-between bg-muted/15">
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-foreground">
                  Associated Keywords ({keywords.length})
                </h3>
                <Button size="sm" className="h-9 px-3 font-semibold gap-1" onClick={openAddKeywordDialog}>
                  <IconPlus className="size-4" />
                  <span>Add Keyword</span>
                </Button>
              </div>

              {/* Table */}
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader className="bg-muted/5 border-b">
                    <TableRow>
                      <TableHead className="pl-6 py-4">Keyword Code</TableHead>
                      <TableHead className="py-4 text-center">Mapped Products</TableHead>
                      <TableHead className="py-4 text-right pr-6 w-[140px]">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {keywords.length > 0 ? (
                      keywords.map((kw) => (
                        <TableRow key={kw.id} className="hover:bg-muted/5 group/row transition-colors">
                          <TableCell className="pl-6 py-4 font-bold text-foreground text-xs uppercase tracking-wider">
                            {kw.code}
                          </TableCell>
                          <TableCell className="py-4 text-center">
                            <Badge className="bg-cyan-50 text-cyan-700 hover:bg-cyan-100 border-cyan-500/20 border dark:bg-cyan-500/10 dark:text-cyan-400 dark:border-cyan-500/30">
                              {kw.products_count} Products
                            </Badge>
                          </TableCell>
                          <TableCell className="py-4 text-right pr-6">
                            <div className="flex items-center justify-end gap-1">
                              <Button
                                variant="ghost"
                                size="xs"
                                className="h-7 px-2 text-muted-foreground hover:text-foreground"
                                onClick={() => openEditKeywordDialog(kw)}
                              >
                                <IconEdit className="size-4" />
                              </Button>
                              <Button
                                variant="ghost"
                                size="xs"
                                className="h-7 px-2 text-muted-foreground hover:text-rose-600"
                                onClick={() => handleDeleteKeyword(kw.id)}
                              >
                                <IconTrash className="size-4" />
                              </Button>
                            </div>
                          </TableCell>
                        </TableRow>
                      ))
                    ) : (
                      <TableRow>
                        <TableCell colSpan={3} className="py-12 text-center text-muted-foreground">
                          <div className="flex flex-col items-center justify-center gap-3">
                            <IconKey className="size-8 text-muted-foreground/55 stroke-[1.5]" />
                            <div className="flex flex-col gap-0.5">
                              <p className="font-semibold text-sm text-foreground">No keywords mapped</p>
                              <p className="text-xs">No keywords are registered under this feed label yet.</p>
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

      {/* Add / Edit Keyword Dialog */}
      <Dialog open={keywordDialogOpen} onOpenChange={setKeywordDialogOpen}>
        <DialogContent className="sm:max-w-md bg-card">
          <form onSubmit={handleKeywordSubmit} className="flex flex-col gap-4">
            <DialogHeader>
              <DialogTitle className="text-lg font-extrabold text-foreground">
                {editingKeyword ? 'Edit Keyword' : 'Add Keyword'}
              </DialogTitle>
              <DialogDescription className="text-xs text-muted-foreground">
                Map tags to this label so buyers can filter product catalogs on the mobile application.
              </DialogDescription>
            </DialogHeader>

            <div className="grid grid-cols-1 gap-4 py-2">
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Keyword Code</label>
                <Input
                  placeholder="e.g. SUMMER_COLLECTION"
                  value={keywordForm.data.code}
                  onChange={(e) => keywordForm.setData('code', e.target.value.toUpperCase())}
                  required
                  autoFocus
                  className="h-10 bg-background"
                />
                {keywordForm.errors.code && (
                  <span className="text-xs text-rose-500 font-semibold">{keywordForm.errors.code}</span>
                )}
              </div>
            </div>

            <DialogFooter className="mt-2 border-t pt-4">
              <Button type="button" variant="outline" onClick={() => setKeywordDialogOpen(false)} disabled={keywordForm.processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={keywordForm.processing} className="font-semibold shadow-xs">
                {keywordForm.processing ? 'Saving...' : editingKeyword ? 'Save Changes' : 'Add Tag'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </>
  );
}
