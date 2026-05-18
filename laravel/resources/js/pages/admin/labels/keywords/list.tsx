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
    const [editingKeyword, setEditingKeyword] = React.useState<Keyword | null>(
        null,
    );

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
                UpsertKeywordController.update.url({
                    label: label.id,
                    keyword: editingKeyword.id,
                }),
                {
                    onSuccess: () => {
                        setKeywordDialogOpen(false);
                        keywordForm.reset();
                    },
                },
            );
        } else {
            keywordForm.post(UpsertKeywordController.store.url(label.id), {
                onSuccess: () => {
                    setKeywordDialogOpen(false);
                    keywordForm.reset();
                },
            });
        }
    };

    const handleDeleteKeyword = (keywordId: number) => {
        if (
            confirm(
                'Are you sure you want to delete this keyword and detach it from all associated products?',
            )
        ) {
            router.delete(
                UpsertKeywordController.destroy.url({
                    label: label.id,
                    keyword: keywordId,
                }),
            );
        }
    };

    return (
        <>
            <Head title={`Manage Keywords - Label ${label.code}`} />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Navigation Breadcrumbs */}
                <div>
                    <Link
                        href="/admin/labels"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <IconArrowLeft className="size-4" />
                        <span>Back to Labels</span>
                    </Link>
                </div>

                {/* Title Banner */}
                <div className="flex items-center justify-between border-b pb-6">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Keywords Registry
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Configure and associate keywords mapping to label
                            feed:{' '}
                            <strong className="text-foreground">
                                {label.code}
                            </strong>
                            .
                        </p>
                    </div>
                </div>

                {/* Two-Column Workspace */}
                <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    {/* Left Column: Label Editor form (lg:col-span-5) */}
                    <div className="flex flex-col gap-6 lg:col-span-5">
                        <form
                            onSubmit={handleLabelSubmit}
                            className="flex flex-col gap-5 rounded-2xl border bg-card p-6 shadow-xs"
                        >
                            <div className="flex items-center gap-2 border-b pb-4">
                                <IconSettings className="size-5 text-primary" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Label Settings
                                </h3>
                            </div>

                            {/* System Code */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    System Code
                                </label>
                                <Input
                                    placeholder="LABEL_CODE"
                                    value={labelForm.data.code}
                                    onChange={(e) =>
                                        labelForm.setData(
                                            'code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {labelForm.errors.code && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {labelForm.errors.code}
                                    </span>
                                )}
                            </div>

                            {/* English */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    English (EN)
                                </label>
                                <Input
                                    placeholder="English translation"
                                    value={labelForm.data.en}
                                    onChange={(e) =>
                                        labelForm.setData('en', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {labelForm.errors.en && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {labelForm.errors.en}
                                    </span>
                                )}
                            </div>

                            {/* French */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    French (FR)
                                </label>
                                <Input
                                    placeholder="French translation"
                                    value={labelForm.data.fr}
                                    onChange={(e) =>
                                        labelForm.setData('fr', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {labelForm.errors.fr && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {labelForm.errors.fr}
                                    </span>
                                )}
                            </div>

                            {/* Arabic */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-right text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Arabic (AR)
                                </label>
                                <Input
                                    placeholder="Arabic translation"
                                    value={labelForm.data.ar}
                                    onChange={(e) =>
                                        labelForm.setData('ar', e.target.value)
                                    }
                                    required
                                    dir="rtl"
                                    className="h-10 bg-background text-right"
                                />
                                {labelForm.errors.ar && (
                                    <span className="text-right text-xs font-semibold text-rose-500">
                                        {labelForm.errors.ar}
                                    </span>
                                )}
                            </div>

                            <Button
                                type="submit"
                                disabled={labelForm.processing}
                                className="mt-1 h-10 w-full"
                            >
                                {labelForm.processing
                                    ? 'Syncing Label Settings...'
                                    : 'Update Label Information'}
                            </Button>

                            {labelForm.recentlySuccessful && (
                                <div className="flex items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <IconCheck className="size-4 shrink-0" />
                                    <span>
                                        Label translations synchronized
                                        successfully
                                    </span>
                                </div>
                            )}
                        </form>
                    </div>

                    {/* Right Column: Keywords Manager Table (lg:col-span-7) */}
                    <div className="flex flex-col gap-6 lg:col-span-7">
                        <div className="flex flex-col overflow-hidden rounded-2xl border bg-card shadow-xs">
                            {/* Header inside table */}
                            <div className="flex items-center justify-between border-b bg-muted/15 p-5">
                                <h3 className="text-sm font-extrabold tracking-wider text-foreground uppercase">
                                    Associated Keywords ({keywords.length})
                                </h3>
                                <Button
                                    size="sm"
                                    className="h-9 gap-1 px-3 font-semibold"
                                    onClick={openAddKeywordDialog}
                                >
                                    <IconPlus className="size-4" />
                                    <span>Add Keyword</span>
                                </Button>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader className="border-b bg-muted/5">
                                        <TableRow>
                                            <TableHead className="py-4 pl-6">
                                                Keyword Code
                                            </TableHead>
                                            <TableHead className="py-4 text-center">
                                                Mapped Products
                                            </TableHead>
                                            <TableHead className="w-[140px] py-4 pr-6 text-right">
                                                Actions
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {keywords.length > 0 ? (
                                            keywords.map((kw) => (
                                                <TableRow
                                                    key={kw.id}
                                                    className="group/row transition-colors hover:bg-muted/5"
                                                >
                                                    <TableCell className="py-4 pl-6 text-xs font-bold tracking-wider text-foreground uppercase">
                                                        {kw.code}
                                                    </TableCell>
                                                    <TableCell className="py-4 text-center">
                                                        <Badge className="border border-cyan-500/20 bg-cyan-50 text-cyan-700 hover:bg-cyan-100 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-400">
                                                            {kw.products_count}{' '}
                                                            Products
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="py-4 pr-6 text-right">
                                                        <div className="flex items-center justify-end gap-1">
                                                            <Button
                                                                variant="ghost"
                                                                size="xs"
                                                                className="h-7 px-2 text-muted-foreground hover:text-foreground"
                                                                onClick={() =>
                                                                    openEditKeywordDialog(
                                                                        kw,
                                                                    )
                                                                }
                                                            >
                                                                <IconEdit className="size-4" />
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="xs"
                                                                className="h-7 px-2 text-muted-foreground hover:text-rose-600"
                                                                onClick={() =>
                                                                    handleDeleteKeyword(
                                                                        kw.id,
                                                                    )
                                                                }
                                                            >
                                                                <IconTrash className="size-4" />
                                                            </Button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={3}
                                                    className="py-12 text-center text-muted-foreground"
                                                >
                                                    <div className="flex flex-col items-center justify-center gap-3">
                                                        <IconKey className="size-8 stroke-[1.5] text-muted-foreground/55" />
                                                        <div className="flex flex-col gap-0.5">
                                                            <p className="text-sm font-semibold text-foreground">
                                                                No keywords
                                                                mapped
                                                            </p>
                                                            <p className="text-xs">
                                                                No keywords are
                                                                registered under
                                                                this feed label
                                                                yet.
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

            {/* Add / Edit Keyword Dialog */}
            <Dialog
                open={keywordDialogOpen}
                onOpenChange={setKeywordDialogOpen}
            >
                <DialogContent className="bg-card sm:max-w-md">
                    <form
                        onSubmit={handleKeywordSubmit}
                        className="flex flex-col gap-4"
                    >
                        <DialogHeader>
                            <DialogTitle className="text-lg font-extrabold text-foreground">
                                {editingKeyword
                                    ? 'Edit Keyword'
                                    : 'Add Keyword'}
                            </DialogTitle>
                            <DialogDescription className="text-xs text-muted-foreground">
                                Map tags to this label so buyers can filter
                                product catalogs on the mobile application.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="grid grid-cols-1 gap-4 py-2">
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Keyword Code
                                </label>
                                <Input
                                    placeholder="e.g. SUMMER_COLLECTION"
                                    value={keywordForm.data.code}
                                    onChange={(e) =>
                                        keywordForm.setData(
                                            'code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    required
                                    autoFocus
                                    className="h-10 bg-background"
                                />
                                {keywordForm.errors.code && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {keywordForm.errors.code}
                                    </span>
                                )}
                            </div>
                        </div>

                        <DialogFooter className="mt-2 border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setKeywordDialogOpen(false)}
                                disabled={keywordForm.processing}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={keywordForm.processing}
                                className="font-semibold shadow-xs"
                            >
                                {keywordForm.processing
                                    ? 'Saving...'
                                    : editingKeyword
                                      ? 'Save Changes'
                                      : 'Add Tag'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
