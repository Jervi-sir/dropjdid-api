// @ts-nocheck
/**
 * Followed Creators API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\ListFollowedCreatorsController
 *
 * Endpoint:
 *   - GET /api/my-account/followed-creators
 */

import api from "@/utils/api";

export interface FriendType {
  /** Creator ID */
  id: number;
  /** Avatar / profile image URL */
  image_url: string;
  /** Full name or display name */
  text1: string;
  /** Username handle (e.g. "@creatorhandle") */
  text2: string;
}

export interface GetFollowedCreatorsParams {
  /** Optional user id if querying on behalf of user */
  user_id?: number;
  /** Search query to filter followed creators */
  search?: string;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetFollowedCreatorsResponse {
  data: FriendType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more items exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of creators followed by the current user.
 *
 * @param params - Optional query parameters (page, per_page, search, user_id)
 * @returns Promise<GetFollowedCreatorsResponse>
 *
 * @example
 * ```ts
 * const res = await getFollowedCreatorsApi({ page: 1, per_page: 20 });
 * console.log(res.data, res.total);
 * ```
 */
export const getFollowedCreatorsApi = async (
  params?: GetFollowedCreatorsParams
): Promise<GetFollowedCreatorsResponse> => {
  const response = await api.get<GetFollowedCreatorsResponse>(
    "/my-account/followed-creators",
    { params }
  );
  return response.data;
};

export default getFollowedCreatorsApi;
