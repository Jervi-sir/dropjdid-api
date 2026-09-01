// @ts-nocheck
/**
 * Product Drops API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Product\DropsController
 *
 * Endpoint:
 *   - GET /api/products/{id}/drops
 */

import api from "@/utils/api";

export interface ProductDropItemType {
  /** Drop unique ID */
  id: number;
  /** Primary drop cover image URL */
  image_url: string;
  /** Drop title / heading */
  text1: string;
  /** Creator handle (e.g. "@creator") or description */
  text2: string;
}

export interface GetProductDropsParams {
  /** Page number for pagination (defaults to 1) */
  page?: number;
  /** Number of items per page (defaults to 20) */
  per_page?: number;
}

export interface GetProductDropsResponse {
  data: ProductDropItemType[];
  total?: number;
  page?: number;
  per_page?: number;
  next_page?: number | null;
}

/**
 * Fetch paginated list of drops featuring the specified product.
 *
 * @param productId - Product ID (numeric or string)
 * @param params - Pagination parameters
 * @returns Promise<GetProductDropsResponse>
 *
 * @example
 * ```ts
 * const res = await getProductDropsApi(1, { page: 1, per_page: 20 });
 * console.log(res.data, res.next_page);
 * ```
 */
export const getProductDropsApi = async (
  productId: number | string,
  params?: GetProductDropsParams
): Promise<GetProductDropsResponse> => {
  const response = await api.get<GetProductDropsResponse>(
    `/products/${productId}/drops`,
    { params }
  );
  return response.data;
};

export default getProductDropsApi;
