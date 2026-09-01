// @ts-nocheck
/**
 * My Account - List Saved Products API Client & Type Definitions
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\ListSavedProductsController
 *
 * Endpoint:
 *   - GET /api/my-account/saved-products
 */

import api from "@/utils/api";

/**
 * Product item representation in saved products list.
 */
export interface SavedProductItemType {
  /** Unique product ID */
  id: number;
  /** Full URL to product primary image */
  image_url: string;
  /** Formatted product price information */
  prices: {
    /** Current active price (e.g. "3 800 DZD") */
    price1: string;
    /** Original price before discount (e.g. "5 000 DZD") */
    price2: string;
    /** Percentage discount badge (e.g. "-24%") */
    promo_percentage: string;
  };
  /** Product title / display name */
  text: string;
  /** Save / bookmark status for current user */
  save: {
    /** Whether the authenticated user has saved this product */
    is_saved: boolean;
    /** Total number of saves for this product */
    nb_save: number;
  };
}

/**
 * Response returned by the list saved products endpoint.
 */
export interface ListSavedProductsResponse {
  /** Paginated list of saved products */
  data: SavedProductItemType[];
  /** Current page index (1-based) */
  current_page: number;
  /** Number of products per page */
  per_page: number;
  /** Total number of saved products for the user */
  total: number;
  /** Next page index, or null if on last page */
  next_page: number | null;
}

/**
 * Query parameters for fetching saved products list.
 */
export interface ListSavedProductsParams {
  /** Products page index (default: 1) */
  page?: number;
  /** Number of products per page (default: 20, max: 100) */
  per_page?: number;
  /** Optional search query filtering by product name or description */
  search?: string;
  /** Alias for search */
  query?: string;
  /** Alias for search */
  q?: string;
  /** Alias for search */
  keyword?: string;
  /** Minimum price in DZD */
  price_min?: number;
  min_price?: number;
  /** Maximum price in DZD */
  price_max?: number | null;
  max_price?: number | null;
}

/**
 * Fetch paginated saved products for the authenticated user.
 *
 * @param params - Optional pagination and filtering parameters
 * @returns Promise<ListSavedProductsResponse>
 *
 * @example
 * ```ts
 * const res = await listSavedProductsApi({ page: 1, per_page: 20 });
 * console.log(res.data); // Array of saved products
 * console.log(res.total); // Total saved products count
 * ```
 */
export const listSavedProductsApi = async (
  params?: ListSavedProductsParams
): Promise<ListSavedProductsResponse> => {
  const response = await api.get<ListSavedProductsResponse>(
    "/my-account/saved-products",
    { params }
  );
  return response.data;
};

export default listSavedProductsApi;
