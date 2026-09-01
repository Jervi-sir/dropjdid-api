// @ts-nocheck
/**
 * My Drops API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\MyDropsController
 *
 * Endpoints:
 *   - GET /api/creators/my-drops
 *   - GET /api/drops/my-drops
 */

import api from "@/utils/api";

export interface DropsType {
  /** Drop identifier */
  id: number;
  /** Primary image URL */
  image_url: string;
  /** Drop title */
  text1: string;
  /** Creator username handle (e.g. "@username") */
  text2: string;
}

export interface ResponseType {
  data: DropsType[];
  next_page?: number | null;
  current_page?: number;
  per_page?: number;
  total?: number;
  last_page?: number;
}

export interface GetMyDropsParams {
  /** Filter by draft status ("true", "false", true, false) */
  is_draft?: boolean | string;
  /** Filter by named preset: "draft" | "published" | "my-drops" | "all" */
  filter?: "draft" | "published" | "my-drops" | "all" | string;
  /** Drop status: "draft" | "published" | "ended" | "all" */
  status?: string;
  /** Page number for pagination (starts at 1) */
  page?: number;
  /** Number of items per page (default: 10) */
  per_page?: number;
  /** Target creator user ID (optional override for testing) */
  creator_id?: number;
  user_id?: number;
}

/**
 * Fetch drops for the current creator with filtering (e.g. published vs drafts) and pagination.
 *
 * @param params - Optional filter and pagination parameters
 * @returns Promise<ResponseType>
 *
 * @example
 * ```ts
 * // Fetch published drops
 * const publishedDrops = await getMyDropsApi({ filter: "my-drops", page: 1 });
 * console.log(publishedDrops.data, publishedDrops.next_page);
 *
 * // Fetch draft drops
 * const draftDrops = await getMyDropsApi({ filter: "draft", page: 1 });
 * console.log(draftDrops.data);
 * ```
 */
export const getMyDropsApi = async (
  params?: GetMyDropsParams
): Promise<ResponseType> => {
  const response = await api.get<ResponseType>("/creators/my-drops", {
    params,
  });
  return response.data;
};

export default getMyDropsApi;
