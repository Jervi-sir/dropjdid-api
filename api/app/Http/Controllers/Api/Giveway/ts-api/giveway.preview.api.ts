// @ts-nocheck
/**
 * Giveaway Preview API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Giveway\GivewayPreviewController
 *
 * Endpoints:
 *   - GET /api/giveaway/preview
 *   - GET /api/giveaway/preview/{id}
 */

import api from "@/utils/api";

export interface PrizeType {
  /** Unique Prize / Giveaway ID */
  id: number;
  /** Title / name of the prize (e.g. "Giveaway on iPhone 17 pro max") */
  title: string;
  /** Full URL to the prize banner/image */
  image_url: string;
  /** Formatted active date range (e.g. "Apr 1 - Apr 30") */
  date_range: string;
  /** Whether the authenticated user has already joined this giveaway */
  has_joined?: boolean;
}

export interface GetGiveawayPreviewParams {
  /** Optional target user id to check joining status */
  user_id?: number;
}

/**
 * Fetch giveaway preview metadata matching the PrizeType interface.
 *
 * @param prizeId - Optional prize ID (defaults to currently active/latest prize)
 * @param params - Optional query parameters (e.g. user_id)
 * @returns Promise<PrizeType>
 *
 * @example
 * ```ts
 * // 1. Fetch current active giveaway preview
 * const prize = await getGiveawayPreviewApi();
 * console.log(prize.title, prize.date_range, prize.image_url, prize.has_joined);
 *
 * // 2. Fetch specific giveaway preview by ID
 * const specificPrize = await getGiveawayPreviewApi(5);
 * console.log(specificPrize.title);
 * ```
 */
export const getGiveawayPreviewApi = async (
  prizeId?: number | string,
  params?: GetGiveawayPreviewParams
): Promise<PrizeType> => {
  const endpoint = prizeId ? `/giveaway/preview/${prizeId}` : "/giveaway/preview";
  const response = await api.get<PrizeType>(endpoint, { params });
  return response.data;
};

export default getGiveawayPreviewApi;
