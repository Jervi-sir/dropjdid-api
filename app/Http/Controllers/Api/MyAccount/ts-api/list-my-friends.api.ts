// @ts-nocheck
/**
 * My Friends API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\ListMyFriendsController
 *
 * Endpoint:
 *   - GET /api/my-account/friends
 */

import api from "@/utils/api";

export interface FriendType {
  /** User ID */
  id: number;
  /** Avatar / profile image URL */
  image_url: string;
  /** Full name or display name */
  text1: string;
  /** Username handle (e.g. "@johndoe") */
  text2: string;
}

export interface GetMyFriendsParams {
  /** Optional user id if querying on behalf of user */
  user_id?: number;
  /** Search query to filter friends by name or username */
  search?: string;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetMyFriendsResponse {
  data: FriendType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more friends exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of current user's accepted friends.
 *
 * @param params - Optional query parameters (page, per_page, search, user_id)
 * @returns Promise<GetMyFriendsResponse>
 *
 * @example
 * ```ts
 * const res = await getMyFriendsApi({ page: 1, per_page: 20 });
 * console.log(res.data, res.total, res.next_page);
 * ```
 */
export const getMyFriendsApi = async (
  params?: GetMyFriendsParams
): Promise<GetMyFriendsResponse> => {
  const response = await api.get<GetMyFriendsResponse>("/my-account/friends", {
    params,
  });
  return response.data;
};

export default getMyFriendsApi;
