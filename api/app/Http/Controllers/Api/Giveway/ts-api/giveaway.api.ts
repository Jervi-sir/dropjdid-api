// @ts-nocheck
/**
 * Giveaway API Client & Types
 *
 * Backend Controllers:
 *   - App\Http\Controllers\Api\Giveway\GivewayPreviewController
 *   - App\Http\Controllers\Api\Giveway\GivewayDetailController
 *   - App\Http\Controllers\Api\Giveway\GivewayJoinController
 *
 * Endpoints:
 *   - GET /api/giveaway/preview
 *   - GET /api/giveaway/preview/{id}
 *   - GET /api/giveaway
 *   - GET /api/giveaway/{id}
 *   - POST /api/giveaway/join
 */

import api from "@/utils/api";

export interface PrizeType {
  id: number;
  title: string;
  image_url: string;
  date_range: string;
  has_joined?: boolean;
}

export interface DetailPrizeType {
  id: number;
  text1: string;
  date_range: string;
}

export interface ResponseType {
  prize: DetailPrizeType;
  time_left: number;
  is_eligible: boolean;
  orders_count: number;
  has_joined: boolean;
}

export interface JoinGiveawayPayload {
  phone_number: string;
  prize_id?: number;
}

export interface JoinGiveawayResponse {
  message: string;
  has_joined: boolean;
  phone_number: string;
  prize_id?: number;
  prize: DetailPrizeType;
  time_left: number;
}

/**
 * Fetch prize preview matching PrizeType (id, title, image_url, date_range, has_joined).
 *
 * @param prizeId - Optional prize ID (defaults to active/latest giveaway)
 * @returns Promise<PrizeType>
 */
export const getGiveawayPreviewApi = async (
  prizeId?: number | string
): Promise<PrizeType> => {
  const endpoint = prizeId ? `/giveaway/preview/${prizeId}` : "/giveaway/preview";
  const response = await api.get<PrizeType>(endpoint);
  return response.data;
};

/**
 * Fetch current or specific giveaway details including time remaining and eligibility.
 *
 * @param prizeId - Optional prize ID (defaults to active/latest giveaway)
 * @param params - Optional params (user_id, phone_number)
 * @returns Promise<ResponseType>
 */
export const getGiveawayDetailsApi = async (
  prizeId?: number | string,
  params?: { user_id?: number; phone_number?: string }
): Promise<ResponseType> => {
  const endpoint = prizeId ? `/giveaway/${prizeId}` : "/giveaway";
  const response = await api.get<ResponseType>(endpoint, { params });
  return response.data;
};

/**
 * Join giveaway by providing a phone number.
 *
 * @param payload - Object containing phone_number and optional prize_id
 * @returns Promise<JoinGiveawayResponse>
 */
export const joinGiveawayApi = async (
  payload: JoinGiveawayPayload
): Promise<JoinGiveawayResponse> => {
  const response = await api.post<JoinGiveawayResponse>("/giveaway/join", payload);
  return response.data;
};

export default getGiveawayPreviewApi;
