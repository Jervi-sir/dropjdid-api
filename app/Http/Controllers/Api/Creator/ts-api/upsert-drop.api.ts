// @ts-nocheck
/**
 * Creator Drop Upsert API Client & Type Definitions
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\UpsertDropController
 *
 * Endpoints:
 *   - GET    /api/creators/drops/check-title
 *   - GET    /api/creators/drops/{drop}
 *   - POST   /api/creators/drops
 *   - DELETE /api/creators/drops/{drop}
 */

import api from "@/utils/api";

export interface SelectedDropProductItem {
  id: number;
  image_url: string;
  name: string;
  prices: {
    price1: string;
    price2?: string;
  };
}

export interface DropImageItem {
  id?: number;
  url: string;
  is_main?: boolean;
  sort_order?: number;
}

export interface CheckDropTitleParams {
  title: string;
  drop_id?: number | null;
}

export interface CheckDropTitleResponse {
  available: boolean;
  message: string;
}

export interface DropDetailsData {
  id: number;
  drop_name: string;
  title: string;
  description: string;
  drop_status: "draft" | "published" | string;
  is_draft: boolean;
  images: DropImageItem[];
  product_ids: number[];
  products: SelectedDropProductItem[];
}

export interface GetDropDetailsResponse {
  data: DropDetailsData;
}

export interface UpsertDropPayload {
  drop_id?: number | null;
  id?: number | null;
  drop_name?: string;
  title?: string;
  description?: string;
  is_draft?: boolean;
  images?: (string | { uri?: string; url?: string; isMain?: boolean })[];
  product_ids?: number[];
}

export interface UpsertDropResponse {
  success: boolean;
  message: string;
  drop_id: number;
  data?: {
    id: number;
    title: string;
    description: string;
    drop_status: string;
    is_draft: boolean;
    product_ids: number[];
  };
}

export const checkDropTitleAvailabilityApi = async (
  params: CheckDropTitleParams
): Promise<CheckDropTitleResponse> => {
  const response = await api.get<CheckDropTitleResponse>(
    "/creators/drops/check-title",
    { params }
  );
  return response.data;
};

export const getDropDetailsApi = async (
  dropId: number | string
): Promise<GetDropDetailsResponse> => {
  const response = await api.get<GetDropDetailsResponse>(
    `/creators/drops/${dropId}`
  );
  return response.data;
};

export const upsertDropApi = async (
  payload: UpsertDropPayload
): Promise<UpsertDropResponse> => {
  const response = await api.post<UpsertDropResponse>(
    "/creators/drops",
    payload
  );
  return response.data;
};

export const deleteDropApi = async (
  dropId: number | string
): Promise<{ success: boolean; message: string }> => {
  const response = await api.delete<{ success: boolean; message: string }>(
    `/creators/drops/${dropId}`
  );
  return response.data;
};

export default upsertDropApi;
