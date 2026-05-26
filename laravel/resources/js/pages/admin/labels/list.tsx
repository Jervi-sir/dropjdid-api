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
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from '@/components/ui/sheet';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';
import UpsertLabelCategoryController from '@/actions/App/Http/Controllers/Admin/Labels/UpsertLabelCategoryController';
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

interface LabelCategory {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
}

interface Label {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
    label_category_id: number;
    label_category: LabelCategory | null;
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
    labelCategories: LabelCategory[];
    filters: {
        search: string;
        label_category_id?: string;
        per_page: number;
    };
}

export default function LabelList({ labels, labelCategories, filters }: LabelListProps) {
    const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
    const [categoryFilter, setCategoryFilter] = React.useState(filters.label_category_id || 'all');

    // Dialog state
    const [dialogOpen, setDialogOpen] = React.useState(false);
    const [editingLabel, setEditingLabel] = React.useState<Label | null>(null);

    // Label Category modal & form state
    const [categoryDialogOpen, setCategoryDialogOpen] = React.useState(false);
    const [editingCategory, setEditingCategory] = React.useState<LabelCategory | null>(null);

    // Form hooks
    const {
        data,
        setData,
        post,
        put,
        delete: destroy,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        label_category_id: '',
        code: '',
        en: '',
        fr: '',
        ar: '',
    });

    const categoryForm = useForm({
        code: '',
        en: '',
        fr: '',
        ar: '',
    });

    const openAddDialog = () => {
        setEditingLabel(null);
        clearErrors();
        setData({
            label_category_id: categoryFilter && categoryFilter !== 'all' ? categoryFilter : '',
            code: '',
            en: '',
            fr: '',
            ar: '',
        });
        setDialogOpen(true);
    };

    const openEditDialog = (label: Label) => {
        setEditingLabel(label);
        setData({
            label_category_id: label.label_category_id?.toString() || '',
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

    const handleCategorySubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingCategory) {
            categoryForm.put(UpsertLabelCategoryController.update.url(editingCategory.id), {
                onSuccess: () => {
                    setEditingCategory(null);
                    categoryForm.reset();
                },
            });
        } else {
            categoryForm.post(UpsertLabelCategoryController.store.url(), {
                onSuccess: () => {
                    categoryForm.reset();
                },
            });
        }
    };

    const startEditCategory = (cat: LabelCategory) => {
        setEditingCategory(cat);
        categoryForm.setData({
            code: cat.code,
            en: cat.en,
            fr: cat.fr,
            ar: cat.ar,
        });
    };

    const cancelEditCategory = () => {
        setEditingCategory(null);
        categoryForm.reset();
    };

    const handleDeleteCategory = (catId: number) => {
        if (confirm('Are you sure you want to delete this category? Associated labels will have their category cleared.')) {
            router.delete(UpsertLabelCategoryController.destroy.url(catId), {
                onSuccess: () => {
                    if (editingCategory?.id === catId) {
                        cancelEditCategory();
                    }
                }
            });
        }
    };

    const handleCategoryDialogOpenChange = (open: boolean) => {
        setCategoryDialogOpen(open);
        if (!open) {
            setEditingCategory(null);
            categoryForm.reset();
        }
    };

    const handleDeleteLabel = (labelId: number) => {
        if (
            confirm(
                'Are you sure you want to delete this label and all its associated keywords?',
            )
        ) {
            router.delete(UpsertLabelController.destroy.url(labelId));
        }
    };

    const applySearch = (search = searchTerm, catFilter = categoryFilter) => {
        router.get(
            '/admin/labels',
            {
                search: search || undefined,
                label_category_id: catFilter === 'all' ? undefined : catFilter,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    React.useEffect(() => {
        const timer = setTimeout(() => {
            if (searchTerm !== (filters.search || '')) {
                applySearch(searchTerm, categoryFilter);
            }
        }, 450);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    const handleCategoryFilterChange = (value: string) => {
        setCategoryFilter(value);
        applySearch(searchTerm, value);
    };

    return (
        <>
            <Head title="Labels Management" />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Page Title & Add Button */}
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Catalog Labels
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Group and catalog feeds using dynamic localized
                            translation fields and searchable tags.
                        </p>
                    </div>
                    <div>
                        <Button
                            className="h-10 shrink-0 gap-1.5 px-4 font-bold shadow-md"
                            variant="outline"
                            size="sm"
                            onClick={() => setCategoryDialogOpen(true)}
                        >
                            <IconPlus className="size-4" />
                            <span>New Category</span>
                        </Button>
                    </div>
                </div>

                {/* Filters and Labels Table */}
                <div className="flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    {/* Search bar */}
                    <div className="flex flex-col items-center justify-between gap-4 border-b bg-muted/20 p-4 sm:flex-row">
                        <div className="flex w-full flex-1 flex-col items-center gap-2 sm:flex-row">
                            <div className="relative w-full sm:max-w-xs">
                                <IconSearch className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search code or translation..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="h-10 w-full bg-background pl-9"
                                />
                            </div>

                            <Select
                                value={categoryFilter}
                                onValueChange={handleCategoryFilterChange}
                            >
                                <SelectTrigger className="h-10 w-full bg-background sm:w-56">
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Categories</SelectItem>
                                    {labelCategories.map((cat) => (
                                        <SelectItem key={cat.id} value={cat.id.toString()}>
                                            {cat.en} ({cat.code})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            {(searchTerm || categoryFilter !== 'all') && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                        setSearchTerm('');
                                        setCategoryFilter('all');
                                        router.get('/admin/labels', {}, { preserveState: true, replace: true });
                                    }}
                                    className="h-10 px-3 text-xs font-semibold hover:bg-muted"
                                >
                                    Clear Filters
                                </Button>
                            )}
                        </div>

                        <div className="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => applySearch(searchTerm, categoryFilter)}
                            >
                                <IconRefresh className="size-4" />
                                <span>Refresh</span>
                            </Button>

                            <Button
                                className="h-10 border-indigo-500/30 text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/20 dark:text-indigo-400 dark:hover:bg-indigo-950/20"
                                variant="outline"
                                size="sm"
                                onClick={openAddDialog}
                            >
                                <IconPlus className="size-4 stroke-[2.5]" />
                                <span>Add New Label</span>
                            </Button>
                        </div>
                    </div>

                    {/* Table Element */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="border-b bg-muted/15">
                                <TableRow>
                                    <TableHead className="py-4 pl-6">
                                        System Code
                                    </TableHead>
                                    <TableHead className="py-4">
                                        Category
                                    </TableHead>
                                    <TableHead className="py-4">
                                        <div className="flex items-center gap-1">
                                            <IconGlobe className="size-3.5" />
                                            <span>English</span>
                                        </div>
                                    </TableHead>
                                    <TableHead className="py-4">
                                        French
                                    </TableHead>
                                    <TableHead className="py-4 pr-12 text-right">
                                        Arabic
                                    </TableHead>
                                    <TableHead className="py-4 text-center">
                                        Associated Keywords
                                    </TableHead>
                                    <TableHead className="w-[240px] py-4 pr-6 text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {labels.data.length > 0 ? (
                                    labels.data.map((lbl) => (
                                        <TableRow
                                            key={lbl.id}
                                            className="group/row transition-colors hover:bg-muted/5"
                                        >
                                            <TableCell className="py-4 pl-6 text-xs font-extrabold tracking-wider text-foreground uppercase">
                                                {lbl.code}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-medium text-foreground">
                                                {lbl.label_category ? (
                                                    <Badge
                                                        variant="outline"
                                                        className="border-indigo-500/20 bg-indigo-500/5 px-2.5 py-0.5 text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                                    >
                                                        {lbl.label_category.en}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs font-semibold text-foreground">
                                                {lbl.en}
                                            </TableCell>
                                            <TableCell className="py-4 text-xs text-muted-foreground">
                                                {lbl.fr}
                                            </TableCell>
                                            <TableCell
                                                className="py-4 pr-12 text-right text-xs font-medium text-muted-foreground"
                                                dir="rtl"
                                            >
                                                {lbl.ar}
                                            </TableCell>
                                            <TableCell className="py-4 text-center">
                                                <Badge
                                                    variant="outline"
                                                    className="bg-muted/10 px-2.5 py-0.5 text-xs font-bold"
                                                >
                                                    {lbl.keywords_count}{' '}
                                                    Keywords
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="py-4 pr-6 text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2.5"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={ShowLabelController.url(
                                                                lbl.id,
                                                            )}
                                                        >
                                                            Keywords
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="xs"
                                                        className="h-7 px-2 text-muted-foreground hover:text-foreground"
                                                        onClick={() =>
                                                            openEditDialog(lbl)
                                                        }
                                                    >
                                                        <IconEdit className="size-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="xs"
                                                        className="h-7 px-2 text-muted-foreground hover:text-rose-600"
                                                        onClick={() =>
                                                            handleDeleteLabel(
                                                                lbl.id,
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
                                            colSpan={7}
                                            className="py-12 text-center text-muted-foreground"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <IconTags className="size-10 stroke-[1.5] text-muted-foreground/55" />
                                                <div className="flex flex-col gap-0.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        No labels found
                                                    </p>
                                                    <p className="text-xs">
                                                        Create a new label or
                                                        adjust your filter
                                                        options.
                                                    </p>
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
                        <div className="flex flex-col items-center justify-between gap-4 border-t bg-muted/10 p-4 sm:flex-row">
                            <span className="text-xs font-medium text-muted-foreground">
                                Showing {labels.from} to {labels.to} of{' '}
                                {labels.total} labels
                            </span>

                            <div className="flex items-center gap-1.5">
                                {labels.links.map((link, idx) => {
                                    const isPrev =
                                        link.label.includes('Previous');
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
                                            className={`inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'} ${link.active ? 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90' : 'border border-border bg-card text-foreground'} `}
                                        >
                                            {isPrev && (
                                                <IconChevronLeft className="-ml-0.5 size-3.5" />
                                            )}
                                            <span>{label}</span>
                                            {isNext && (
                                                <IconChevronRight className="-mr-0.5 size-3.5" />
                                            )}
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
                <DialogContent className="bg-card sm:max-w-lg">
                    <form
                        onSubmit={handleFormSubmit}
                        className="flex flex-col gap-4"
                    >
                        <DialogHeader>
                            <DialogTitle className="text-lg font-extrabold text-foreground">
                                {editingLabel ? 'Edit Label' : 'Add New Label'}
                            </DialogTitle>
                            <DialogDescription className="text-xs text-muted-foreground">
                                Define the code and translations across
                                languages to classify feed assets dynamically.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="grid grid-cols-1 gap-4 py-2">
                            {/* Label Category */}
                            <div className="flex flex-col gap-1.5">
                                <div className="flex items-center justify-between">
                                    <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        Label Category
                                    </label>
                                    <button
                                        type="button"
                                        onClick={() => setCategoryDialogOpen(true)}
                                        className="text-xs font-semibold text-primary hover:underline"
                                    >
                                        + New Category
                                    </button>
                                </div>
                                <Select
                                    value={data.label_category_id}
                                    onValueChange={(val) => setData('label_category_id', val)}
                                >
                                    <SelectTrigger className="h-10 w-full bg-background">
                                        <SelectValue placeholder="Select Category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {labelCategories.map((cat) => (
                                            <SelectItem key={cat.id} value={cat.id.toString()}>
                                                {cat.en} ({cat.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.label_category_id && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {errors.label_category_id}
                                    </span>
                                )}
                            </div>

                            {/* System Code */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    System Code
                                </label>
                                <Input
                                    placeholder="e.g. SEASON_DEAL"
                                    value={data.code}
                                    onChange={(e) =>
                                        setData(
                                            'code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    disabled={!!editingLabel}
                                    required
                                    className="h-10 bg-background"
                                />
                                {errors.code && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {errors.code}
                                    </span>
                                )}
                            </div>

                            {/* English */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    English (EN)
                                </label>
                                <Input
                                    placeholder="e.g. Season Deals"
                                    value={data.en}
                                    onChange={(e) =>
                                        setData('en', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {errors.en && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {errors.en}
                                    </span>
                                )}
                            </div>

                            {/* French */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    French (FR)
                                </label>
                                <Input
                                    placeholder="e.g. Offres de saison"
                                    value={data.fr}
                                    onChange={(e) =>
                                        setData('fr', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {errors.fr && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {errors.fr}
                                    </span>
                                )}
                            </div>

                            {/* Arabic */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-right text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Arabic (AR)
                                </label>
                                <Input
                                    placeholder="مثال: عروض الموسم"
                                    value={data.ar}
                                    onChange={(e) =>
                                        setData('ar', e.target.value)
                                    }
                                    required
                                    dir="rtl"
                                    className="h-10 bg-background text-right"
                                />
                                {errors.ar && (
                                    <span className="text-right text-xs font-semibold text-rose-500">
                                        {errors.ar}
                                    </span>
                                )}
                            </div>
                        </div>

                        <DialogFooter className="mt-2 border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setDialogOpen(false)}
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="font-semibold shadow-xs"
                            >
                                {processing
                                    ? 'Saving...'
                                    : editingLabel
                                        ? 'Save Changes'
                                        : 'Create Label'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Add Label Category Sheet */}
            <Sheet open={categoryDialogOpen} onOpenChange={handleCategoryDialogOpenChange}>
                <SheetContent side="right" className="bg-card w-full sm:max-w-md flex flex-col h-full overflow-y-auto border-l shadow-xl p-6">
                    <form
                        onSubmit={handleCategorySubmit}
                        className="flex flex-col gap-4"
                    >
                        <SheetHeader>
                            <SheetTitle className="text-lg font-extrabold text-foreground">
                                {editingCategory ? 'Edit Category' : 'Manage Categories'}
                            </SheetTitle>
                            <SheetDescription className="text-xs text-muted-foreground">
                                {editingCategory 
                                    ? 'Modify category properties.' 
                                    : 'Create and manage categories to group labels.'}
                            </SheetDescription>
                        </SheetHeader>

                        <div className="grid grid-cols-1 gap-4 py-2 border-b pb-4">
                            <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                {editingCategory ? 'Edit Category Details' : 'Add New Category'}
                            </div>

                            {/* System Code */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    System Code
                                </label>
                                <Input
                                    placeholder="e.g. MARKETING"
                                    value={categoryForm.data.code}
                                    onChange={(e) =>
                                        categoryForm.setData(
                                            'code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {categoryForm.errors.code && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {categoryForm.errors.code}
                                    </span>
                                )}
                            </div>

                            {/* English */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    English (EN)
                                </label>
                                <Input
                                    placeholder="e.g. Marketing"
                                    value={categoryForm.data.en}
                                    onChange={(e) =>
                                        categoryForm.setData('en', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {categoryForm.errors.en && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {categoryForm.errors.en}
                                    </span>
                                )}
                            </div>

                            {/* French */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    French (FR)
                                </label>
                                <Input
                                    placeholder="e.g. Marketing"
                                    value={categoryForm.data.fr}
                                    onChange={(e) =>
                                        categoryForm.setData('fr', e.target.value)
                                    }
                                    required
                                    className="h-10 bg-background"
                                />
                                {categoryForm.errors.fr && (
                                    <span className="text-xs font-semibold text-rose-500">
                                        {categoryForm.errors.fr}
                                    </span>
                                )}
                            </div>

                            {/* Arabic */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-right text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Arabic (AR)
                                </label>
                                <Input
                                    placeholder="مثال: تسويق"
                                    value={categoryForm.data.ar}
                                    onChange={(e) =>
                                        categoryForm.setData('ar', e.target.value)
                                    }
                                    required
                                    dir="rtl"
                                    className="h-10 bg-background text-right"
                                />
                                {categoryForm.errors.ar && (
                                    <span className="text-right text-xs font-semibold text-rose-500">
                                        {categoryForm.errors.ar}
                                    </span>
                                )}
                            </div>

                            <div className="flex justify-end gap-2 mt-2">
                                {editingCategory && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={cancelEditCategory}
                                    >
                                        Cancel Edit
                                    </Button>
                                )}
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={categoryForm.processing}
                                    className="font-semibold shadow-xs"
                                >
                                    {categoryForm.processing ? 'Saving...' : editingCategory ? 'Save Changes' : 'Create Category'}
                                </Button>
                            </div>
                        </div>

                        <div className="flex flex-col gap-2 pt-2">
                            <div className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Existing Categories ({labelCategories.length})
                            </div>
                            {labelCategories.length > 0 ? (
                                <div className="flex flex-col gap-1.5 max-h-48 overflow-y-auto">
                                    {labelCategories.map((cat) => (
                                        <div key={cat.id} className="flex items-center justify-between rounded-lg border bg-muted/15 p-2 text-sm transition-colors hover:bg-muted/30">
                                            <div className="flex flex-col">
                                                <span className="font-bold text-foreground">{cat.en}</span>
                                                <span className="text-[10px] font-mono text-muted-foreground">{cat.code}</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="xs"
                                                    className="h-7 w-7 p-0 text-muted-foreground hover:text-foreground"
                                                    onClick={() => startEditCategory(cat)}
                                                >
                                                    <IconEdit className="size-3.5" />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="xs"
                                                    className="h-7 w-7 p-0 text-muted-foreground hover:text-rose-600"
                                                    onClick={() => handleDeleteCategory(cat.id)}
                                                >
                                                    <IconTrash className="size-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <span className="text-xs text-muted-foreground">No categories defined yet.</span>
                            )}
                        </div>

                        <SheetFooter className="mt-2 border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => handleCategoryDialogOpenChange(false)}
                                disabled={categoryForm.processing}
                            >
                                Close
                            </Button>
                        </SheetFooter>
                    </form>
                </SheetContent>
            </Sheet>
        </>
    );
}
