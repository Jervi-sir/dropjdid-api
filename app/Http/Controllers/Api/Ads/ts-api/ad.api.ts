// @ts-nocheck
/**
 * Advertisement API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Ads\AdController
 * Endpoints:
 *   - GET /api/ads
 *   - GET /api/ads/{id}
 */

import api from "@/utils/api";

export interface AdType {
  id: number;
  text1: string;
  text2: string;
  image_url: string[];
  url: string;
  stats: {
    nb_liked: number;
    nb_saved: number;
    nb_shared: number;
  };
}

export interface GetAdResponse {
  data: AdType;
}

export interface GetAdsListResponse {
  data: AdType[];
}

/**
 * Fetch a single advertisement by ID.
 *
 * @param id - Advertisement ID
 * @returns Promise<GetAdResponse>
 */
export const getAdByIdApi = async (id: number | string): Promise<GetAdResponse> => {
  const response = await api.get<GetAdResponse>(`/ads/${id}`);
  return response.data;
};

/**
 * Fetch all active advertisements.
 *
 * @returns Promise<GetAdsListResponse>
 */
export const getAdsListApi = async (): Promise<GetAdsListResponse> => {
  const response = await api.get<GetAdsListResponse>("/ads");
  return response.data;
};

export default getAdByIdApi;
