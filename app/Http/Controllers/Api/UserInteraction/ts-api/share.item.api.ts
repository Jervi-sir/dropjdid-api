// @ts-nocheck
/**
 * Share Interaction API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\UserInteraction\ShareController
 *
 * Endpoints:
 *   - POST /api/interactions/ads/{id}/share
 *   - POST /api/interactions/drops/{id}/share
 *   - POST /api/interactions/products/{id}/share
 */

import api from "@/utils/api";

export type InteractionTargetType = "advertisement" | "drop" | "product" | "profile" | "user";

export interface RecordShareParams {
  /** Optional target user ID whom this item was shared to */
  shared_to_user_id?: number | string;
  /** Optional channel/medium used (e.g. 'app', 'whatsapp', 'facebook', 'link_copied') */
  channel?: string;
}

export interface RecordShareResponse {
  /** Target resource unique ID */
  id: number;
  /** Target resource type */
  target_type?: InteractionTargetType;
  /** Total count of shares recorded for this item */
  nb_shares: number;
  /** Alias for total shares */
  nb_shared?: number;
  /** Action result feedback message */
  message: string;
}

/**
 * Record a share on an Advertisement.
 *
 * @param id - Advertisement ID
 * @param params - Optional share metadata (e.g. recipient user ID, channel)
 * @returns Promise<RecordShareResponse>
 *
 * @example
 * ```ts
 * const res = await recordShareAdApi(1, { shared_to_user_id: 5 });
 * console.log(res.nb_shares);
 * ```
 */
export const recordShareAdApi = async (
  id: number | string,
  params?: RecordShareParams
): Promise<RecordShareResponse> => {
  const response = await api.post<RecordShareResponse>(`/interactions/ads/${id}/share`, params);
  return response.data;
};

/**
 * Record a share on a Drop.
 *
 * @param id - Drop ID
 * @param params - Optional share metadata
 * @returns Promise<RecordShareResponse>
 *
 * @example
 * ```ts
 * const res = await recordShareDropApi(1);
 * console.log(res.nb_shares);
 * ```
 */
export const recordShareDropApi = async (
  id: number | string,
  params?: RecordShareParams
): Promise<RecordShareResponse> => {
  const response = await api.post<RecordShareResponse>(`/interactions/drops/${id}/share`, params);
  return response.data;
};

/**
 * Record a share on a Product.
 *
 * @param id - Product ID
 * @param params - Optional share metadata
 * @returns Promise<RecordShareResponse>
 *
 * @example
 * ```ts
 * const res = await recordShareProductApi(1);
 * console.log(res.nb_shares);
 * ```
 */
export const recordShareProductApi = async (
  id: number | string,
  params?: RecordShareParams
): Promise<RecordShareResponse> => {
  const response = await api.post<RecordShareResponse>(`/interactions/products/${id}/share`, params);
  return response.data;
};

/**
 * Record a share on a Profile / User.
 *
 * @param id - User/Profile ID
 * @param params - Optional share metadata
 * @returns Promise<RecordShareResponse>
 *
 * @example
 * ```ts
 * const res = await recordShareProfileApi(1);
 * console.log(res.nb_shares);
 * ```
 */
export const recordShareProfileApi = async (
  id: number | string,
  params?: RecordShareParams
): Promise<RecordShareResponse> => {
  const response = await api.post<RecordShareResponse>(`/interactions/profiles/${id}/share`, params);
  return response.data;
};

/**
 * Generic share recording helper for any target type ('ads' | 'drops' | 'products' | 'profiles').
 *
 * @param targetType - Target type identifier ('ads' | 'drops' | 'products' | 'profiles' | 'profile' | 'advertisement' | 'drop' | 'product')
 * @param id - Target item ID
 * @param params - Optional share metadata
 * @returns Promise<RecordShareResponse>
 */
export const recordShareItemApi = async (
  targetType: "ads" | "drops" | "products" | "profiles" | InteractionTargetType,
  id: number | string,
  params?: RecordShareParams
): Promise<RecordShareResponse> => {
  const endpoint =
    targetType === "advertisement"
      ? "ads"
      : targetType === "drop"
      ? "drops"
      : targetType === "product"
      ? "products"
      : targetType === "profile" || targetType === "user"
      ? "profiles"
      : targetType;
  const response = await api.post<RecordShareResponse>(`/interactions/${endpoint}/${id}/share`, params);
  return response.data;
};

export default {
  recordShareAdApi,
  recordShareDropApi,
  recordShareProductApi,
  recordShareProfileApi,
  recordShareItemApi,
};
