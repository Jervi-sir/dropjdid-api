// @ts-nocheck
/**
 * Explore Feed API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Feeds\ExploreController
 *
 * Endpoints:
 *   - GET /api/feeds/explore
 *   - GET /api/feeds/explore/labels/{label}/products
 *   - GET /api/labels/{label}/products
 */

import api from "@/utils/api";

/**
 * Product item representation in feeds and sections.
 */
export interface ProductType {
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
 * Category info attached to a label section.
 */
export interface LabelCategoryInfo {
  id: number;
  code: string;
  name: string;
}

/**
 * Products section grouped by a specific Label (e.g. "Hoodies", "Sportswear").
 */
export interface ProductsSectionType {
  id: string;
  label_id: number;
  label_code: string;
  label: string;
  category?: LabelCategoryInfo | null;
  section_type: "products";
  products: ProductType[];
}

/**
 * Single advertisement item in ads banner section.
 */
export interface AdType {
  id: number;
  image_url: string;
  text1: string;
  text2: string;
  url?: string;
}

/**
 * Sponsored / Advertisements section banner.
 */
export interface AdsSectionType {
  id: string;
  label: string;
  section_type: "ads";
  ads: AdType[];
}

/**
 * Discriminated union of explore feed sections.
 */
export type SectionType = ProductsSectionType | AdsSectionType;

/**
 * Response returned by the explore feed endpoint.
 */
export interface ExploreFeedResponseType {
  /** Array of product label sections and optional ads banners */
  data: SectionType[];
  /** Current page index (1-based) */
  current_page: number;
  /** Next page index, or null if no further pages */
  next_page?: number | null;
  /** Total number of matching label sections available */
  total_labels?: number;
}

/**
 * Query parameters for fetching explore feed sections with filtering options.
 */
export interface GetExploreFeedParams {
  /** Section page index (default: 1) */
  page?: number;
  /** Number of label sections to return per page (default: 5, max: 50) */
  per_page?: number;
  /** Maximum number of products to return inside each label section (default: 20, max: 50) */
  products_per_section?: number;
  /** Filter sections by label category code or ID (e.g. 'streetwear', 'men', 1) */
  label_category?: string | number;
  /** Filter suggestions related to a specific drop ID */
  drop_id?: number | string;
  /** Alias for drop_id */
  dropId?: number | string;
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
 * Response returned by the label products pagination endpoint.
 */
export interface LabelProductsResponseType {
  /** Metadata of the requested label */
  label: {
    id: number;
    code: string;
    name: string;
    category?: LabelCategoryInfo | null;
  };
  /** Paginated list of products under this label */
  data: ProductType[];
  /** Current page number */
  current_page: number;
  /** Next page number, or null if on last page */
  next_page?: number | null;
  /** Total number of products under this label */
  total: number;
}

/**
 * Query parameters for fetching paginated products of a specific label.
 */
export interface GetLabelProductsParams {
  /** Label identifier: either numeric ID (e.g. 1) or string code (e.g. "hoodies") */
  label: string | number;
  /** Products page index (default: 1) */
  page?: number;
  /** Number of products per page (default: 20, max: 100) */
  per_page?: number;
  /** Optional drop ID filter */
  drop_id?: number | string;
  /** Alias for drop_id */
  dropId?: number | string;
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
 * Fetch explore feed with paginated Label sections (each returning up to 20 products),
 * with optional search, category filter, price, quality, gender, and type/size filters.
 *
 * @param params - Query parameters
 * @returns Promise<ExploreFeedResponseType>
 */
export const getExploreFeedApi = async (
  params?: GetExploreFeedParams
): Promise<ExploreFeedResponseType> => {
  const response = await api.get<ExploreFeedResponseType>("/feeds/explore", {
    params,
  });
  return response.data;
};

/**
 * Fetch paginated products for a specific Label section, with optional filters.
 *
 * @param params - Query parameters
 * @returns Promise<LabelProductsResponseType>
 */
export const getLabelProductsApi = async ({
  label,
  page = 1,
  per_page = 20,
  ...restParams
}: GetLabelProductsParams): Promise<LabelProductsResponseType> => {
  const response = await api.get<LabelProductsResponseType>(
    `/feeds/explore/labels/${label}/products`,
    {
      params: { page, per_page, ...restParams },
    }
  );
  return response.data;
};

export default getExploreFeedApi;
