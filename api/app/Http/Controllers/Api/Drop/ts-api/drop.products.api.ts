// @ts-nocheck
/**
 * Drop Products API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Drop\ShowProductsController
 *
 * Endpoint:
 *   - GET /api/drops/{id}/products
 */

import api from "@/utils/api";

/**
 * Product item schema inside a drop.
 */
export interface ProductType {
  /** Unique product ID */
  id: number;
  /** Primary product image URL */
  image_url: string;
  /** Formatted product prices and promotion info */
  prices: {
    /** Current active price (e.g. "19 500 DZD") */
    price1: string;
    /** Original price before discount (e.g. "24 000 DZD") */
    price2: string;
    /** Percentage discount badge (e.g. "-19%") */
    promo_percentage: string;
  };
  /** Product title / name */
  text: string;
  /** Save / bookmark status */
  save: {
    /** Whether the authenticated user has saved this product */
    is_saved?: boolean;
    /** Total number of saves for this product */
    nb_save?: number;
  };
}

/**
 * Query parameters for fetching products belonging to a drop.
 */
export interface GetDropProductsParams {
  /** Drop identifier (numeric ID or string) */
  id: number | string;
  /** Optional page number for pagination */
  page?: number;
  /** Optional number of products per page (default: 20, max: 100) */
  per_page?: number;
}

/**
 * Response returned by the drop products endpoint.
 */
export interface GetDropProductsResponse {
  /** Array of products belonging to the drop */
  data: ProductType[];
  /** Current page index */
  current_page?: number;
  /** Next page index, or null if no more pages */
  next_page?: number | null;
  /** Total count of products in this drop */
  total?: number;
}

/**
 * Fetch all products associated with a specific drop, with optional pagination.
 *
 * @param idOrParams - Drop ID (number | string) or parameter object with pagination options
 * @returns Promise<GetDropProductsResponse>
 *
 * @example
 * ```ts
 * // 1. Fetch all products of drop #1
 * const res = await getDropProductsApi(1);
 * console.log(`Found ${res.data.length} products:`, res.data);
 *
 * // 2. Fetch paginated products of drop #1
 * const paginated = await getDropProductsApi({
 *   id: 1,
 *   page: 2,
 *   per_page: 10,
 * });
 * console.log(`Page ${paginated.current_page} of ${paginated.total} total items`);
 * ```
 */
export const getDropProductsApi = async (
  idOrParams: number | string | GetDropProductsParams
): Promise<GetDropProductsResponse> => {
  const isObject = typeof idOrParams === "object" && idOrParams !== null;
  const id = isObject ? idOrParams.id : idOrParams;
  const params = isObject
    ? { page: idOrParams.page, per_page: idOrParams.per_page }
    : undefined;

  const response = await api.get<GetDropProductsResponse>(
    `/drops/${id}/products`,
    { params }
  );
  return response.data;
};

export default getDropProductsApi;
