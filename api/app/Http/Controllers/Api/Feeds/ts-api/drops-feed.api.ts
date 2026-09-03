// @ts-nocheck
/**
 * Drops Feed API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Feeds\DropsFeedController
 * Endpoint: GET /api/feeds/drops
 */

import api from "@/api/api";

export interface DropType {
  id: number;
  image_url: string;
  text1: string;
  text2: string;
}

export type DropsFeedTarget = "for-you" | "creator-i-follow" | "trending";

export interface ResponseType {
  data: DropType[];
  target: DropsFeedTarget;
  selected_filter: DropsFeedTarget;
  next_page?: number | null;
}

export interface GetDropsFeedParams {
  target?: DropsFeedTarget;
  filter?: DropsFeedTarget | string;
  drop_id?: number | string;
  search?: string;
  query?: string;
  q?: string;
  keyword?: string;
  page?: number;
  per_page?: number;
}

/**
 * Fetch drops feed according to selected target/filter, search query, and page number.
 *
 * @param params - Query parameters (target, filter, search / query / q / keyword, page, per_page)
 * @returns Promise<ResponseType>
 *
 * @example
 * ```ts
 * const feedData = await getDropsFeedApi({ target: "for-you", search: "nike", page: 1 });
 * console.log(feedData.data);
 * ```
 */
export const getDropsFeedApi = async (
  params?: GetDropsFeedParams
): Promise<ResponseType> => {
  const response = await api.get<ResponseType>("/feeds/drops", { params });
  return response.data;
};

export default getDropsFeedApi;
