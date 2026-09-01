// @ts-nocheck
/**
 * Upsert Product Info & Catalog Options API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\ThisStore\UpsertProductController
 *
 * Endpoints:
 *   - GET /api/sgm/this-store/types-and-sizes
 *   - GET /api/sgm/this-store/labels
 *   - GET /api/sgm/this-store/keywords/{labelId}?search={search}
 *   - GET /api/sgm/this-store/upsert-product?id={id}
 *   - GET /api/sgm/this-store/upsert-product/{id}
 */

import api from '@/utils/api';

export interface QualityOption {
    id: number;
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
}

export interface GenderOption {
    id: number;
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
}

export interface SizeOption {
    id: number;
    category_id: number;
    code: string;
    type?: string | null;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
}

export interface CategoryOption {
    id: number;
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
    sizes: SizeOption[];
}

export interface TypesAndSizesResponse {
    qualities: QualityOption[];
    genders: GenderOption[];
    categories: CategoryOption[];
}

export interface LabelOption {
    id: number;
    label_category_id: number;
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
    image_url?: string | null;
}

export interface LabelCategoryOption {
    id: number;
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
    icon?: string | null;
    labels: LabelOption[];
}

export interface KeywordOption {
    id: number;
    label_id: number;
    code: string;
}

export interface ProductType {
    admin_feedback: {
        message: string;
        type: 'warning' | 'tips' | 'rejection-reason' | string;
    };
    name?: string;
    description?: string;
    expires_at?: Date | string | null;
    id?: number | null;
    images: string[];
    prices: {
        price1: string;
        price2: string;
        price3?: string;
    };
    selected_catalog: {
        quality: {
            code: string;
        }[];
        genders: {
            code: string;
        }[];
        type_sizes: {
            code: string;
            sizes: {
                code: string;
                quantity: number;
            }[];
        }[];
    };
    labels: {
        category_code: string;
        label_code?: string;
        labels_codes: string[];
        labels_count: number;
    }[];
}

export interface UploadProductImage {
    uri: string;
    width?: number;
    height?: number;
    isMain: boolean;
}

export interface UploadProductPayload {
    is_draft: boolean;
    product_id?: number | string | null;
    store_id?: number | string | null;
    name?: string;
    description?: string;
    prices: {
        price1: string;
        price2: string;
        price3?: string;
    };
    selected_catalog: {
        quality: {
            code: string;
        }[];
        genders: {
            code: string;
        }[];
        type_sizes: {
            code: string;
            sizes: {
                code: string;
                quantity: number;
            }[];
        }[];
    };
    labels: {
        category_code: string;
        label_code?: string;
        labels_codes: string[];
        labels_count: number;
    }[];
    expires_at?: Date | string | null;
    images_count: number;
    images: UploadProductImage[];
}

export interface GetUpsertProductParams {
    id?: number | string;
    product_id?: number | string;
}

export interface GetLabelKeywordsParams {
    labelId?: number | string;
    label_id?: number | string;
    search?: string;
    q?: string;
}

/**
 * Fetch available types and sizes catalog options (qualities, genders, categories with sizes).
 *
 * @returns Promise<TypesAndSizesResponse>
 */
export const getProductTypesAndSizesApi =
    async (): Promise<TypesAndSizesResponse> => {
        const response = await api.get<TypesAndSizesResponse>(
            '/sgm/this-store/types-and-sizes',
        );
        return response.data;
    };

/**
 * Fetch all labels grouped by label_categories.
 *
 * @returns Promise<LabelCategoryOption[]>
 */
export const getProductLabelsApi = async (): Promise<LabelCategoryOption[]> => {
    const response = await api.get<{ data: LabelCategoryOption[] }>(
        '/sgm/this-store/labels',
    );
    return response.data?.data ?? response.data;
};

/**
 * Fetch keywords belonging to a specific label with optional search filter.
 *
 * @param params - Label ID (or params object with search)
 * @param search - Optional search query if label ID is passed as first argument
 * @returns Promise<KeywordOption[]>
 */
export const getLabelKeywordsApi = async (
    params: number | string | GetLabelKeywordsParams,
    search?: string,
): Promise<KeywordOption[]> => {
    let labelId: number | string | undefined;
    let searchQuery: string | undefined = search;

    if (typeof params === 'object') {
        labelId = params.labelId ?? params.label_id;
        searchQuery = params.search ?? params.q ?? search;
    } else {
        labelId = params;
    }

    const response = await api.get<{ data: KeywordOption[] }>(
        `/sgm/this-store/keywords/${labelId}`,
        {
            params: searchQuery ? { search: searchQuery } : undefined,
        },
    );
    return response.data?.data ?? response.data;
};

/**
 * Fetch product info formatted for the upsert product form.
 *
 * @param productId - Product ID (or params object)
 * @returns Promise<ProductType>
 */
export const getUpsertProductInfoApi = async (
    productId: number | string | GetUpsertProductParams,
): Promise<ProductType> => {
    const id =
        typeof productId === 'object'
            ? (productId.id ?? productId.product_id)
            : productId;
    const response = await api.get<ProductType>(
        `/sgm/this-store/upsert-product/${id}`,
    );
    return response.data;
};

/**
 * Upsert (create or update) product with the full payload using FormData for file uploads.
 *
 * @param payload - UploadProductPayload
 * @returns Promise<{ message: string; product_id: number; data: any }>
 */
export const upsertProductApi = async (
    payload: UploadProductPayload,
): Promise<{ message: string; product_id: number; data: any }> => {
    const id = payload.product_id;
    const formData = new FormData();

    formData.append('payload', JSON.stringify(payload));
    formData.append('is_draft', String(payload.is_draft));
    if (payload.store_id) formData.append('store_id', String(payload.store_id));
    if (payload.name) formData.append('name', payload.name);
    if (payload.description) formData.append('description', payload.description);

    payload.images.forEach((img, index) => {
        if (img.uri && (img.uri.startsWith('file://') || img.uri.startsWith('/'))) {
            const filename = img.uri.split('/').pop() || `product_image_${index}.jpg`;
            const match = /\.(\w+)$/.exec(filename);
            const type = match ? `image/${match[1]}` : `image/jpeg`;

            formData.append(`image_${index}`, {
                uri: img.uri,
                name: filename,
                type: type,
            } as any);
        }
    });

    const config = {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    };

    if (id) {
        const response = await api.post(
            `/sgm/this-store/upsert-product/${id}`,
            formData,
            config,
        );
        return response.data;
    }

    const response = await api.post(
        '/sgm/this-store/upsert-product',
        formData,
        config,
    );
    return response.data;
};

/**
 * Refresh product expiration date (+15 days).
 *
 * @param productId - Product ID
 * @returns Promise<{ message: string; data: { id: number; refreshed_at: string; expires_at: string } }>
 */
export const refreshProductApi = async (
    productId: number | string,
): Promise<{ message: string; data: { id: number; refreshed_at: string; expires_at: string } }> => {
    const response = await api.post(
        `/sgm/this-store/upsert-product/${productId}/refresh`,
    );
    return response.data;
};

/**
 * Soft delete product.
 *
 * @param productId - Product ID
 * @returns Promise<{ message: string; product_id: number | string }>
 */
export const deleteProductApi = async (
    productId: number | string,
): Promise<{ message: string; product_id: number | string }> => {
    const response = await api.delete(
        `/sgm/this-store/upsert-product/${productId}`,
    );
    return response.data;
};

export default getUpsertProductInfoApi;


