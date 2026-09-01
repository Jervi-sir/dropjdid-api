// @ts-nocheck
/**
 * People Feed & User Search API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Feeds\PeopleController
 * Endpoints:
 * - GET /api/feeds/people
 * - GET /api/people
 */

import api from "@/utils/api";

export interface PersonItemType {
  id: number;
  image_url: string;
  text1: string; // Full Name
  text2: string; // @username
  username: string;
  full_name: string;
  is_following?: boolean;
}

export interface PeopleSearchResponseType {
  data: PersonItemType[];
  current_page: number;
  next_page?: number | null;
  total: number;
}

export interface GetPeopleSearchParams {
  search?: string;
  query?: string;
  q?: string;
  keyword?: string;
  role?: string;
  page?: number;
  per_page?: number;
}

/**
 * Search and paginate users/creators.
 *
 * @param params - Query parameters (search / query / q / keyword, role, page, per_page)
 * @returns Promise<PeopleSearchResponseType>
 *
 * @example
 * ```ts
 * const res = await getPeopleSearchApi({ search: "karim", page: 1 });
 * console.log(res.data);
 * ```
 */
export const getPeopleSearchApi = async (
  params?: GetPeopleSearchParams
): Promise<PeopleSearchResponseType> => {
  const response = await api.get<PeopleSearchResponseType>("/feeds/people", {
    params,
  });
  return response.data;
};

export default getPeopleSearchApi;
