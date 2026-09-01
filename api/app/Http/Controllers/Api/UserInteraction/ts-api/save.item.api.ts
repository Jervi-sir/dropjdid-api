// @ts-nocheck
/**
 * Save Interaction API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\UserInteraction\SaveController
 *
 * Endpoints:
 *   - POST /api/interactions/ads/{id}/save
 *   - POST /api/interactions/drops/{id}/save
 *   - POST /api/interactions/products/{id}/save
 */

import api from "@/utils/api";

export type InteractionTargetType = "advertisement" | "drop" | "product";

export interface ToggleSaveResponse {
  /** Target resource unique ID */
  id: number;
  /** Target resource type */
  target_type?: InteractionTargetType;
  /** Whether the item is saved/bookmarked by the current user after toggle */
  is_saved: boolean;
  /** Total count of saves for this item */
  nb_saved: number;
  /** Action result feedback message */
  message: string;
}

/**
 * Toggle save/bookmark for an Advertisement.
 *
 * @param id - Advertisement ID
 * @returns Promise<ToggleSaveResponse>
 *
 * @example
 * ```ts
 * const res = await toggleSaveAdApi(1);
 * console.log(res.is_saved, res.nb_saved);
 * ```
 */
export const toggleSaveAdApi = async (
  id: number | string
): Promise<ToggleSaveResponse> => {
  const response = await api.post<ToggleSaveResponse>(`/interactions/ads/${id}/save`);
  return response.data;
};

/**
 * Toggle save/bookmark for a Drop.
 *
 * @param id - Drop ID
 * @returns Promise<ToggleSaveResponse>
 *
 * @example
 * ```ts
 * const res = await toggleSaveDropApi(1);
 * console.log(res.is_saved, res.nb_saved);
 * ```
 */
export const toggleSaveDropApi = async (
  id: number | string
): Promise<ToggleSaveResponse> => {
  const response = await api.post<ToggleSaveResponse>(`/interactions/drops/${id}/save`);
  return response.data;
};

/**
 * Toggle save/bookmark for a Product.
 *
 * @param id - Product ID
 * @returns Promise<ToggleSaveResponse>
 *
 * @example
 * ```ts
 * const res = await toggleSaveProductApi(1);
 * console.log(res.is_saved, res.nb_saved);
 * ```
 */
export const toggleSaveProductApi = async (
  id: number | string
): Promise<ToggleSaveResponse> => {
  const response = await api.post<ToggleSaveResponse>(`/interactions/products/${id}/save`);
  return response.data;
};

/**
 * Generic toggle save interaction helper for any target type ('ads' | 'drops' | 'products').
 *
 * @param targetType - Target type identifier ('ads' | 'drops' | 'products')
 * @param id - Target item ID
 * @returns Promise<ToggleSaveResponse>
 */
export const toggleSaveItemApi = async (
  targetType: "ads" | "drops" | "products" | InteractionTargetType,
  id: number | string
): Promise<ToggleSaveResponse> => {
  const endpoint = targetType === "advertisement" ? "ads" : targetType === "drop" ? "drops" : targetType === "product" ? "products" : targetType;
  const response = await api.post<ToggleSaveResponse>(`/interactions/${endpoint}/${id}/save`);
  return response.data;
};

export default {
  toggleSaveAdApi,
  toggleSaveDropApi,
  toggleSaveProductApi,
  toggleSaveItemApi,
};
