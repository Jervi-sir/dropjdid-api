// @ts-nocheck
/**
 * Search Feed & Keyword Suggestions API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Feeds\SearchController
 * Method: suggestKeywords
 * Endpoints:
 * - GET /api/feeds/search/suggestions
 * - GET /api/search/suggestions
 */

import api from "@/utils/api";

export type SuggestionType = "keyword" | "label" | "category";

export interface SuggestionItem {
  id: number;
  type: SuggestionType;
  text: string;
  code: string;
  label_id?: number | null;
  label?: string | null;
  category_id?: number | null;
  category?: string | null;
  products_count: number;
}

export interface DirectProfileItem {
  id: number;
  name: string;
  username: string;
  image_url: string;
  is_following?: boolean;
}

export interface DirectProductItem {
  id: number;
  title: string;
  image_url: string;
  price: string;
  store_name: string;
}

export interface DirectDropItem {
  id: number;
  title: string;
  creator: string;
  image_url: string;
}

export interface DirectResults {
  profiles: DirectProfileItem[];
  products: DirectProductItem[];
  drops: DirectDropItem[];
}

export interface SuggestKeywordsResponse {
  query: string;
  data: SuggestionItem[];
  suggestions: string[];
  direct_results?: DirectResults;
}

export interface SuggestKeywordsParams {
  query?: string;
  q?: string;
  keyword?: string;
  limit?: number;
}

/**
 * Fetch keyword, label, and category suggestions based on user input,
 * along with direct preview results for profiles, products, and drops.
 *
 * @param params - Query parameters (query / q / keyword, limit)
 * @returns Promise<SuggestKeywordsResponse>
 */
export const suggestKeywordsApi = async (
  params?: SuggestKeywordsParams
): Promise<SuggestKeywordsResponse> => {
  const response = await api.get<SuggestKeywordsResponse>(
    "/feeds/search/suggestions",
    { params }
  );
  return response.data;
};

export default suggestKeywordsApi;

