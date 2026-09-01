// @ts-nocheck
/**
 * Creator Followers API Client & Types
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\FollowersController
 *
 * Endpoint:
 *   - GET /api/creators/followers
 */

import api from "@/utils/api";

export interface FriendType {
  /** Follower ID */
  id: number;
  /** Avatar / profile image URL */
  image_url: string;
  /** Full name or display name */
  text1: string;
  /** Username handle (e.g. "@username") */
  text2: string;
}

export interface GetCreatorFollowersParams {
  /** Optional creator user id */
  creator_id?: number;
  /** Optional user id fallback */
  user_id?: number;
  /** Search query to filter followers */
  search?: string;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetCreatorFollowersResponse {
  data: FriendType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more items exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of followers for the creator.
 *
 * @param params - Optional query parameters (page, per_page, search, creator_id)
 * @returns Promise<GetCreatorFollowersResponse>
 */
export const getCreatorFollowersApi = async (
  params?: GetCreatorFollowersParams
): Promise<GetCreatorFollowersResponse> => {
  const response = await api.get<GetCreatorFollowersResponse>(
    "/creators/followers",
    { params }
  );
  return response.data;
};

export default getCreatorFollowersApi;
