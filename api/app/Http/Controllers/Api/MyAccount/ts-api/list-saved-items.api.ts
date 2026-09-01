/**
 * My Account - List Saved Items (Drops & Products) API Client & Type Definitions
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\ListSavedItemsController
 *
 * Endpoint:
 *   - GET /api/my-account/saved-items
 */

import api from "@/api/api";

/**
 * Drop item representation in saved items list.
 */
export interface SavedDropItemType {
  /** Unique drop ID */
  id: number;
  /** Full URL to drop primary image */
  image_url: string;
  /** Drop title */
  text1: string;
  /** Creator username or description */
  text2: string;
}

/**
 * Product item representation in saved items list.
 */
export interface SavedProductItemType {
  /** Unique product ID */
  id: number;
  /** Full URL to product primary image */
  image_url: string;
  /** Formatted product price information */
  prices: {
    /** Current active price (e.g. "3 800 DZD") */
    price1: string;
    /** Original price before discount (e.g. "5 000 DZD") */
    price2: string;
    /** Percentage discount badge (e.g. "-24%") */
    promo_percentage: string;
  };
  /** Product title / display name */
  text: string;
  /** Save / bookmark status for current user */
  save: {
    /** Whether the authenticated user has saved this product */
    is_saved: boolean;
    /** Total number of saves for this product */
    nb_save: number;
  };
}

/**
 * Paginated response for saved drops.
 */
export interface ListSavedDropsResponse {
  data: SavedDropItemType[];
  tab: "drops";
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
}

/**
 * Paginated response for saved products.
 */
export interface ListSavedProductsResponse {
  data: SavedProductItemType[];
  tab: "products";
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
}

export type ListSavedItemsResponse<T extends "drops" | "products" = "drops"> =
  T extends "drops" ? ListSavedDropsResponse : ListSavedProductsResponse;

/**
 * Query parameters for fetching saved items.
 */
export interface ListSavedItemsParams {
  /** Target tab: "drops" (default) or "products" */
  tab?: "drops" | "products";
  /** Page index (default: 1) */
  page?: number;
  /** Number of items per page (default: 20, max: 100) */
  per_page?: number;
  /** Optional search query */
  search?: string;
  query?: string;
  q?: string;
  keyword?: string;
  /** Price filters (products tab) */
  price_min?: number;
  min_price?: number;
  price_max?: number | null;
  max_price?: number | null;
}

/**
 * Fetch paginated saved items for the authenticated user.
 */
export const listSavedItemsApi = async <T extends "drops" | "products" = "drops">(
  params?: ListSavedItemsParams & { tab?: T },
): Promise<ListSavedItemsResponse<T>> => {
  const response = await api.get<ListSavedItemsResponse<T>>(
    "/my-account/saved-items",
    { params },
  );
  return response.data;
};

export default listSavedItemsApi;
