// @ts-nocheck
/**
 * Filter Catalog API Client & Types
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Catalogs\FilterCatalogController
 *
 * Endpoint:
 *   - GET /api/catalogs/filters
 */

import api from "@/utils/api";

export interface PriceFilterCatalog {
  min: number;
  max: number;
  default_min: number;
  default_max: number | null;
  is_unlimited: boolean;
  currency: string;
}

export interface QualityFilterItem {
  id: number;
  code: string;
  name: string;
  en: string;
  fr: string;
  ar: string;
}

export interface GenderFilterItem {
  id: number;
  code: string;
  name: string;
  en: string;
  fr: string;
  ar: string;
}

export interface SizeFilterItem {
  id: number;
  code: string;
  name: string;
  type: string;
  en: string;
  fr: string;
  ar: string;
}

export interface TypeFilterItem {
  id: number;
  code: string;
  name: string;
  en: string;
  fr: string;
  ar: string;
  sizes: SizeFilterItem[];
}

export interface FilterCatalogResponse {
  price: PriceFilterCatalog;
  qualities: QualityFilterItem[];
  genders: GenderFilterItem[];
  types: TypeFilterItem[];
}

/**
 * Fetch available catalog metadata for filters (Price min/max, qualities, genders, types & sizes).
 *
 * @returns Promise<FilterCatalogResponse>
 *
 * @example
 * ```ts
 * const catalog = await getFilterCatalogApi();
 * console.log(catalog.price.min, catalog.qualities, catalog.types);
 * ```
 */
export const getFilterCatalogApi = async (): Promise<FilterCatalogResponse> => {
  const response = await api.get<FilterCatalogResponse>("/catalogs/filters");
  return response.data;
};

export default getFilterCatalogApi;
