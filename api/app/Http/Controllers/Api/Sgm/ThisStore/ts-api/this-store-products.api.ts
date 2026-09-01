// @ts-nocheck
/**
 * This Store Products API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\ThisStore\ThisStoreProductController
 *
 * Endpoint:
 *   - GET /api/sgm/this-store/products
 */

import api from "@/utils/api";

export interface ProductStatusType {
  code: "draft" | "published" | "archived" | "rejected" | string;
  en: string;
  fr: string;
  ar: string;
}

export interface StoreProductItemType {
  id: number;
  store_id: number | null;
  name: string;
  text: string;
  image_url: string;
  imageUrl: string;
  price_original: number | null;
  price_shown: number | null;
  price_store: number | null;
  price1: string;
  price2: string;
  promo_percentage: string;
  promoPercentage: string;
  product_status: ProductStatusType | null;
  status_raw?: string | null;
  rejection_reason?: any;
  created_at?: string;
  updated_at?: string;
}

export interface GetThisStoreProductsParams {
  /** Target store ID */
  store_id?: number;
  /** Filter by product status (e.g. "draft", "published", "archived", "rejected", or "all") */
  product_status?: "draft" | "published" | "archived" | "rejected" | "all" | string;
  /** Search keyword in product name/description */
  search?: string;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetThisStoreProductsResponse {
  data: StoreProductItemType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more items exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch products of a specific store with status and keyword filters.
 *
 * @param params - Query parameters (store_id, product_status, search, page, per_page)
 * @returns Promise<GetThisStoreProductsResponse>
 *
 * @example
 * ```ts
 * const res = await getThisStoreProductsApi({ store_id: 1, product_status: "published" });
 * console.log(res.data, res.total);
 * ```
 */
export const getThisStoreProductsApi = async (
  params?: GetThisStoreProductsParams
): Promise<GetThisStoreProductsResponse> => {
  const response = await api.get<GetThisStoreProductsResponse>(
    "/sgm/this-store/products",
    { params }
  );
  return response.data;
};

export default getThisStoreProductsApi;
