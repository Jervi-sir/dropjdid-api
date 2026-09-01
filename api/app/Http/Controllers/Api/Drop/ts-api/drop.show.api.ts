// @ts-nocheck
/**
 * Drop API Client
 *
 * Backend Controllers:
 *   - App\Http\Controllers\Api\Drop\ShowController
 *   - App\Http\Controllers\Api\Drop\ShowProductsController
 *
 * Endpoints:
 *   - GET /api/drops/{id}
 *   - GET /api/drops/{id}/products
 */

import api from "@/utils/api";

export interface DropType {
  id: number;
  image_urls: string[];
  text1: string;
  text2: string;
  stats: {
    nb_liked: number;
    nb_saved: number;
    nb_products: number;
    nb_shares: number;
  };
  is_saved: boolean;
  is_liked: boolean;
}

export interface ProductType {
  id: number;
  image_url: string;
  prices: {
    price1: string;
    price2: string;
    promo_percentage: string;
  };
  text: string;
  save: {
    is_saved?: boolean;
    nb_save?: number;
  };
}

export interface GetDropResponse {
  data: DropType;
}

export interface GetDropProductsResponse {
  data: ProductType[];
}

/**
 * Fetch a single drop by its ID.
 *
 * @param id - Drop ID
 * @returns Promise<GetDropResponse>
 */
export const getDropByIdApi = async (id: number | string): Promise<GetDropResponse> => {
  const response = await api.get<GetDropResponse>(`/drops/${id}`);
  return response.data;
};

/**
 * Fetch list of products associated with a drop.
 *
 * @param id - Drop ID
 * @returns Promise<GetDropProductsResponse>
 */
export const getDropProductsApi = async (
  id: number | string
): Promise<GetDropProductsResponse> => {
  const response = await api.get<GetDropProductsResponse>(`/drops/${id}/products`);
  return response.data;
};

export default getDropByIdApi;
