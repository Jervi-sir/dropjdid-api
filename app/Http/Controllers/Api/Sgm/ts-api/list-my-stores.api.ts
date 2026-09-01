// @ts-nocheck
/**
 * List My Stores API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\ListMyStoresController
 *
 * Endpoint:
 *   - GET /api/sgm/my-stores
 */

import api from "@/utils/api";

export interface StoreStatusType {
  code: "pending" | "active" | "suspended" | string;
  en: string;
  fr: string;
  ar: string;
}

export interface StoreType {
  /** Store ID */
  id: number;
  /** Store title / display name */
  text1: string;
  /** Structured store status JSON object, or null */
  store_status: StoreStatusType | null;
  /** Whether the store is approved */
  is_approved?: boolean;
}

export interface GetMyStoresParams {
  /** Optional user id if querying on behalf of user */
  user_id?: number;
  /** Search query to filter stores by name or phone */
  search?: string;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetMyStoresResponse {
  data: StoreType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more stores exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of current user's stores.
 *
 * @param params - Optional query parameters (page, per_page, search, user_id)
 * @returns Promise<GetMyStoresResponse>
 *
 * @example
 * ```ts
 * const res = await getMyStoresApi({ page: 1, per_page: 20 });
 * console.log(res.data, res.total, res.next_page);
 * ```
 */
export const getMyStoresApi = async (
  params?: GetMyStoresParams
): Promise<GetMyStoresResponse> => {
  const response = await api.get<GetMyStoresResponse>("/sgm/my-stores", {
    params,
  });
  return response.data;
};

export default getMyStoresApi;
