// @ts-nocheck
/**
 * Creator Drop Stats API Client & Type Definitions
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\DropStatsController
 *
 * Endpoints:
 *   - GET /api/creators/drops/{drop}/liked-by
 *   - GET /api/creators/drops/{drop}/saved-by
 *   - GET /api/creators/drops/{drop}/shared-by
 *   - GET /api/creators/drops/{drop}/products
 */

import api from "@/utils/api";
import { FriendType } from "./creator-followers.api";

export interface DropProductType {
  id: number;
  image_url: string;
  text: string;
  prices: {
    price1: string;
    price2?: string;
    promo_percentage?: string;
  };
  save?: {
    is_saved?: boolean;
    nb_save?: number;
  };
}

export interface GetDropUsersResponse {
  data: FriendType[];
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
}

export interface GetDropProductsResponse {
  data: DropProductType[];
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
}

export interface GetDropStatsParams {
  page?: number;
  per_page?: number;
  search?: string;
}

export const getDropLikedByApi = async (
  dropId: number | string,
  params?: GetDropStatsParams
): Promise<GetDropUsersResponse> => {
  const response = await api.get<GetDropUsersResponse>(
    `/creators/drops/${dropId}/liked-by`,
    { params }
  );
  return response.data;
};

export const getDropSavedByApi = async (
  dropId: number | string,
  params?: GetDropStatsParams
): Promise<GetDropUsersResponse> => {
  const response = await api.get<GetDropUsersResponse>(
    `/creators/drops/${dropId}/saved-by`,
    { params }
  );
  return response.data;
};

export const getDropSharedByApi = async (
  dropId: number | string,
  params?: GetDropStatsParams
): Promise<GetDropUsersResponse> => {
  const response = await api.get<GetDropUsersResponse>(
    `/creators/drops/${dropId}/shared-by`,
    { params }
  );
  return response.data;
};

export const getDropProductsListApi = async (
  dropId: number | string,
  params?: GetDropStatsParams
): Promise<GetDropProductsResponse> => {
  const response = await api.get<GetDropProductsResponse>(
    `/creators/drops/${dropId}/products`,
    { params }
  );
  return response.data;
};
