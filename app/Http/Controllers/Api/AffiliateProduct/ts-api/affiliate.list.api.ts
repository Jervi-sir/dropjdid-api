// @ts-nocheck
/**
 * Affiliate Products API Client & Type Definitions
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\AffiliateProduct\ListController
 *
 * Endpoints:
 *   - GET /api/affiliate-products
 *   - GET /api/affiliate-products/labels/{label}/products
 */

import api from "@/utils/api";

/**
 * Product item representation in affiliate product feeds and sections.
 */
export interface AffiliateProductType {
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
    is_saved?: boolean;
    /** Total number of saves for this product */
    nb_save?: number;
  };
}

/**
 * Products section grouped by a specific Label containing affiliate products.
 */
export interface AffiliateProductsSectionType {
  id: number;
  label_id: number;
  label_code: string;
  label: string;
  category_name?: string;
  section_type: "products";
  products: AffiliateProductType[];
}

/**
 * Response returned by the affiliate products list endpoint.
 */
export interface AffiliateProductsListResponseType {
  /** Array of label sections containing affiliate products */
  data: AffiliateProductsSectionType[];
  /** Current page index (1-based) */
  current_page: number;
  /** Next page index, or null if no further pages */
  next_page?: number | null;
  /** Total number of matching label sections available */
  total_labels?: number;
}

/**
 * Query parameters for fetching affiliate products list with label pagination and filtering options.
 */
export interface GetAffiliateProductsParams {
  /** Section page index (default: 1) */
  page?: number;
  /** Number of label sections to return per page (default: 5, max: 50) */
  per_page?: number;
  /** Maximum number of products to return inside each label section (default: 20, max: 50) */
  products_per_section?: number;
  /** Filter sections by label category code or ID */
  label_category?: string | number;
  /** Search term matching labels, categories, and products */
  search?: string;
  /** Alias for search */
  query?: string;
  /** Alias for search */
  q?: string;
  /** Alias for search */
  keyword?: string;

  /** Minimum price in DZD (e.g. 500) */
  price_min?: number;
  min_price?: number;
  /** Maximum price in DZD (e.g. 50000, or omitted/null for unlimited) */
  price_max?: number | null;
  max_price?: number | null;

  /** Quality filter: 'original' | 'premium_copy' | 'copy' (single or comma-separated / array) */
  quality?: string | string[];
  qualities?: string | string[];

  /** Gender / For filter: 'men' | 'women' | 'kids' (single, comma-separated, or 'man' / 'woman') */
  gender?: string | string[];
  genders?: string | string[];
  for?: string | string[];

  /** Type / Category filter (e.g. "Upper body", "Bottom body", "Bags", "Accessories", "Outfits", "Wears in head") */
  type?: string | string[];
  types?: string | string[];
  category?: string | string[];

  /** Size filter (e.g. "XS", "S", "M", "L", "XL", "42", "43") */
  size?: string | string[];
  sizes?: string | string[];
}

/**
 * Response returned by the affiliate label products pagination endpoint.
 */
export interface AffiliateLabelProductsResponseType {
  /** Metadata of the requested label */
  label: {
    id: number;
    code: string;
    name: string;
    category?: {
      id: number;
      code: string;
      name: string;
    } | null;
  };
  /** Paginated list of affiliate products under this label */
  data: AffiliateProductType[];
  /** Current page number */
  current_page: number;
  /** Next page number, or null if on last page */
  next_page?: number | null;
  /** Total number of products under this label */
  total: number;
}

/**
 * Query parameters for fetching paginated affiliate products of a specific label.
 */
export interface GetAffiliateLabelProductsParams {
  /** Label identifier: numeric ID (e.g. 1) or string code (e.g. "hoodies") */
  label: string | number;
  /** Products page index (default: 1) */
  page?: number;
  /** Number of products per page (default: 20, max: 100) */
  per_page?: number;
  /** Search filter within this label's products */
  search?: string;
  /** Alias for search */
  query?: string;
  /** Alias for search */
  q?: string;
  /** Alias for search */
  keyword?: string;

  /** Minimum price in DZD */
  price_min?: number;
  min_price?: number;
  /** Maximum price in DZD */
  price_max?: number | null;
  max_price?: number | null;
  /** Quality filter */
  quality?: string | string[];
  /** Gender filter */
  gender?: string | string[];
  for?: string | string[];
  /** Type filter */
  type?: string | string[];
  /** Size filter */
  size?: string | string[];
}

/**
 * Fetch affiliate products feed with paginated Label sections (each returning up to 20 products),
 * with optional search, category, price, quality, gender, and type/size filters.
 *
 * @param params - Query parameters
 * @returns Promise<AffiliateProductsListResponseType>
 *
 * @example
 * ```ts
 * const res = await getAffiliateProductsApi({
 *   page: 1,
 *   per_page: 5,
 *   products_per_section: 20,
 *   search: "shoes",
 * });
 * console.log(res.data); // Array of label sections with affiliate products
 * ```
 */
export const getAffiliateProductsApi = async (
  params?: GetAffiliateProductsParams
): Promise<AffiliateProductsListResponseType> => {
  const response = await api.get<AffiliateProductsListResponseType>(
    "/affiliate-products",
    { params }
  );
  return response.data;
};

/**
 * Fetch paginated affiliate products for a specific Label section.
 *
 * @param params - Query parameters including label identifier
 * @returns Promise<AffiliateLabelProductsResponseType>
 *
 * @example
 * ```ts
 * const res = await getAffiliateLabelProductsApi({
 *   label: "hoodies", // or label ID e.g. 1
 *   page: 1,
 *   per_page: 20,
 * });
 * console.log(res.data); // Paginated affiliate products for this label
 * ```
 */
export const getAffiliateLabelProductsApi = async ({
  label,
  page = 1,
  per_page = 20,
  ...restParams
}: GetAffiliateLabelProductsParams): Promise<AffiliateLabelProductsResponseType> => {
  const response = await api.get<AffiliateLabelProductsResponseType>(
    `/affiliate-products/labels/${label}/products`,
    {
      params: { page, per_page, ...restParams },
    }
  );
  return response.data;
};

export default getAffiliateProductsApi;
