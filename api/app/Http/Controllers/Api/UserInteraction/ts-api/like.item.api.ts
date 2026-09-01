// @ts-nocheck
/**
 * Like Interaction API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\UserInteraction\LikeController
 *
 * Endpoints:
 *   - POST /api/interactions/ads/{id}/like
 *   - POST /api/interactions/drops/{id}/like
 *   - POST /api/interactions/products/{id}/like
 */

import api from "@/utils/api";

export type InteractionTargetType = "advertisement" | "drop" | "product";

export interface ToggleLikeResponse {
  /** Target resource unique ID */
  id: number;
  /** Target resource type */
  target_type?: InteractionTargetType;
  /** Whether the item is liked by the current user after toggle */
  is_liked: boolean;
  /** Total count of likes for this item */
  nb_liked: number;
  /** Action result feedback message */
  message: string;
}

/**
 * Toggle like for an Advertisement.
 *
 * @param id - Advertisement ID
 * @returns Promise<ToggleLikeResponse>
 *
 * @example
 * ```ts
 * const res = await toggleLikeAdApi(1);
 * console.log(res.is_liked, res.nb_liked);
 * ```
 */
export const toggleLikeAdApi = async (
  id: number | string
): Promise<ToggleLikeResponse> => {
  const response = await api.post<ToggleLikeResponse>(`/interactions/ads/${id}/like`);
  return response.data;
};

/**
 * Toggle like for a Drop.
 *
 * @param id - Drop ID
 * @returns Promise<ToggleLikeResponse>
 *
 * @example
 * ```ts
 * const res = await toggleLikeDropApi(1);
 * console.log(res.is_liked, res.nb_liked);
 * ```
 */
export const toggleLikeDropApi = async (
  id: number | string
): Promise<ToggleLikeResponse> => {
  const response = await api.post<ToggleLikeResponse>(`/interactions/drops/${id}/like`);
  return response.data;
};

/**
 * Toggle like/interested for a Product.
 *
 * @param id - Product ID
 * @returns Promise<ToggleLikeResponse>
 *
 * @example
 * ```ts
 * const res = await toggleLikeProductApi(1);
 * console.log(res.is_liked, res.nb_liked);
 * ```
 */
export const toggleLikeProductApi = async (
  id: number | string
): Promise<ToggleLikeResponse> => {
  const response = await api.post<ToggleLikeResponse>(`/interactions/products/${id}/like`);
  return response.data;
};

/**
 * Generic toggle like interaction helper for any target type ('ads' | 'drops' | 'products').
 *
 * @param targetType - Target type identifier ('ads' | 'drops' | 'products')
 * @param id - Target item ID
 * @returns Promise<ToggleLikeResponse>
 */
export const toggleLikeItemApi = async (
  targetType: "ads" | "drops" | "products" | InteractionTargetType,
  id: number | string
): Promise<ToggleLikeResponse> => {
  const endpoint = targetType === "advertisement" ? "ads" : targetType === "drop" ? "drops" : targetType === "product" ? "products" : targetType;
  const response = await api.post<ToggleLikeResponse>(`/interactions/${endpoint}/${id}/like`);
  return response.data;
};

export default {
  toggleLikeAdApi,
  toggleLikeDropApi,
  toggleLikeProductApi,
  toggleLikeItemApi,
};
