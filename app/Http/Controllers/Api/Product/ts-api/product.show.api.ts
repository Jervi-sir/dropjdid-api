// @ts-nocheck
/**
 * Product Details API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Product\ShowController
 *
 * Endpoint:
 *   - GET /api/products/{id}
 */

import api from "@/utils/api";

/**
 * Product details item representation.
 */
export interface ProductType {
  /** Unique product ID */
  id: number;
  /** Full URL to primary product image */
  image_url: string;
  /** Formatted product prices and promotion */
  prices: {
    /** Current active price (e.g. "19 500 DZD") */
    price1: string;
    /** Original price before discount (e.g. "24 000 DZD") */
    price2: string;
    /** Percentage discount badge (e.g. "-19%") */
    promo_percentage: string;
  };
  /** Product title / display name */
  text: string;
  /** Save status and count */
  save: {
    /** Whether the authenticated user has saved this product */
    is_saved?: boolean;
    /** Total number of saves for this product */
    nb_save?: number;
  };
  /** Whether the authenticated user has saved this product */
  is_saved?: boolean;
  /** Total number of saves for this product */
  nb_saved?: number;
  /** Whether the authenticated user has liked / expressed interest in this product */
  is_liked?: boolean;
  /** Total number of likes / interested users for this product */
  nb_liked?: number;
  /** Total number of shares for this product */
  nb_shares?: number;
  /** Total number of drops featuring this product */
  nb_drops?: number;
  /** Product summary statistics */
  stats?: {
    nb_interested: number;
    nb_saved: number;
    nb_shares: number;
    nb_drops: number;
  };
  /** Structured like / interested info */
  like?: {
    is_liked?: boolean;
    nb_liked?: number;
  };
}

/**
 * Response returned by the single product details endpoint.
 */
export interface GetProductResponse {
  data: ProductType;
}

/**
 * Fetch a single product's details by ID.
 *
 * @param id - Product ID (numeric or string)
 * @returns Promise<GetProductResponse>
 *
 * @example
 * ```ts
 * const res = await getProductByIdApi(1);
 * console.log(res.data.stats.nb_drops, res.data.stats.nb_shares);
 * ```
 */
export const getProductByIdApi = async (
  id: number | string
): Promise<GetProductResponse> => {
  const response = await api.get<GetProductResponse>(`/products/${id}`);
  return response.data;
};

export default getProductByIdApi;
