import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';
import { IconArrowLeft, IconPhoto, IconTrash } from '@tabler/icons-react';

interface PrizeData {
    id?: number;
    title: string;
    description: string | null;
    image_url: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: number;
}

interface UpsertPrizeProps {
    prize: PrizeData | null;
    statuses: { [key: number]: string };
}

export default function UpsertPrize({ prize, statuses }: UpsertPrizeProps) {
    const isEdit = !!prize;

    const { data, setData, post, processing, errors } = useForm({
        title: prize?.title || '',
        description: prize?.description || '',
        image: null as File | null,
        starts_at: prize?.starts_at || '',
        ends_at: prize?.ends_at || '',
        status: prize?.status?.toString() || '0',
        _method: isEdit ? 'PUT' : undefined,
    });

    const [imagePreview, setImagePreview] = React.useState<string | null>(
        prize?.image_url || null,
    );

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('image', file);
            setImagePreview(URL.createObjectURL(file));
        }
    };

    const handleRemoveImage = () => {
        setData('image', null);
        setImagePreview(null);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Standard multipart form submission for Inertia:
        // For edits, we send a POST with _method=PUT because PHP doesn't parse multipart PUT requests.
        if (isEdit) {
            post(`/admin/prizes/${prize.id}`, {
                forceFormData: true,
            });
        } else {
            post('/admin/prizes');
        }
    };

    return (
        <>
            <Head title={isEdit ? `Edit Prize: ${prize.title}` : 'Create New Prize'} />
            <div className="mx-auto max-w-3xl p-4 lg:p-8">
                {/* Back button and page title */}
                <div className="mb-6 flex flex-col gap-2">
                    <Link
                        href="/admin/prizes"
                        className="inline-flex w-fit items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground"
                    >
                        <IconArrowLeft className="size-3.5" />
                        <span>Back to prizes</span>
                    </Link>
                    <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                        {isEdit ? 'Edit Prize' : 'Create New Prize'}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {isEdit
                            ? 'Modify properties and active durations of your prize.'
                            : 'Set up details, banner image, and run periods for a new raffle prize.'}
                    </p>
                </div>

                <form
                    onSubmit={handleSubmit}
                    className="flex flex-col gap-6 rounded-xl border bg-card p-6 shadow-xs"
                >
                    {/* Title */}
                    <div className="grid gap-2">
                        <Label htmlFor="title">Prize Title</Label>
                        <Input
                            id="title"
                            placeholder="e.g. Summer Super Giveaway 2026"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            required
                            className="bg-background h-11"
                        />
                        {errors.title && (
                            <span className="text-xs font-semibold text-rose-500">
                                {errors.title}
                            </span>
                        )}
                    </div>

                    {/* Description */}
                    <div className="grid gap-2">
                        <Label htmlFor="description">Description / Rules</Label>
                        <textarea
                            id="description"
                            rows={4}
                            placeholder="Add eligibility details, rules, or details about the prize item..."
                            value={data.description || ''}
                            onChange={(e) => setData('description', e.target.value)}
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        {errors.description && (
                            <span className="text-xs font-semibold text-rose-500">
                                {errors.description}
                            </span>
                        )}
                    </div>

                    {/* Image Upload Block */}
                    <div className="grid gap-2">
                        <Label>Banner Image</Label>
                        <div className="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/20 bg-muted/5 p-6 text-center transition-all hover:bg-muted/10">
                            {imagePreview ? (
                                <div className="relative w-full max-w-sm rounded-lg overflow-hidden border">
                                    <img
                                        src={imagePreview}
                                        alt="Preview"
                                        className="h-44 w-full object-cover"
                                    />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="icon"
                                        onClick={handleRemoveImage}
                                        className="absolute top-2 right-2 size-8 shadow-md"
                                    >
                                        <IconTrash className="size-4" />
                                    </Button>
                                </div>
                            ) : (
                                <label className="flex cursor-pointer flex-col items-center justify-center gap-2 py-4">
                                    <div className="rounded-full bg-background p-3 shadow-inner">
                                        <IconPhoto className="size-6 text-muted-foreground" />
                                    </div>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-sm font-bold text-foreground">
                                            Upload banner image
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            Drag & drop or click to upload (PNG, JPG, max 10MB)
                                        </span>
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={handleImageChange}
                                        className="hidden"
                                    />
                                </label>
                            )}
                        </div>
                        {errors.image && (
                            <span className="text-xs font-semibold text-rose-500">
                                {errors.image}
                            </span>
                        )}
                    </div>

                    {/* Date limits */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="starts_at">Starts At</Label>
                            <Input
                                id="starts_at"
                                type="datetime-local"
                                value={data.starts_at || ''}
                                onChange={(e) => setData('starts_at', e.target.value)}
                                className="bg-background h-11"
                            />
                            {errors.starts_at && (
                                <span className="text-xs font-semibold text-rose-500">
                                    {errors.starts_at}
                                </span>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="ends_at">Ends At</Label>
                            <Input
                                id="ends_at"
                                type="datetime-local"
                                value={data.ends_at || ''}
                                onChange={(e) => setData('ends_at', e.target.value)}
                                className="bg-background h-11"
                            />
                            {errors.ends_at && (
                                <span className="text-xs font-semibold text-rose-500">
                                    {errors.ends_at}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Status Select */}
                    <div className="grid gap-2">
                        <Label htmlFor="status">Prize Status</Label>
                        <Select
                            value={data.status}
                            onValueChange={(val) => setData('status', val)}
                        >
                            <SelectTrigger className="h-11 bg-background">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(statuses).map(([key, value]) => (
                                    <SelectItem key={key} value={key}>
                                        <span className="capitalize">{value}</span>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.status && (
                            <span className="text-xs font-semibold text-rose-500">
                                {errors.status}
                            </span>
                        )}
                    </div>

                    {/* Submit Actions */}
                    <div className="mt-4 flex items-center justify-end gap-3 border-t pt-6">
                        <Button
                            type="button"
                            variant="outline"
                            asChild
                        >
                            <Link href="/admin/prizes">Cancel</Link>
                        </Button>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold h-11 px-6 shadow-sm"
                        >
                            {processing ? 'Saving...' : isEdit ? 'Save Changes' : 'Create Prize'}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
