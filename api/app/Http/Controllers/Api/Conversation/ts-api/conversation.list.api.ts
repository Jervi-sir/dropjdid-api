// @ts-nocheck
/**
 * Conversations List API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Conversations\ListController
 *   - App\Http\Controllers\Api\Conversation\ListController
 *
 * Endpoint:
 *   - GET /api/conversations
 */

import api from "@/utils/api";

export interface ConversationType {
  /** Unique conversation identifier */
  id: number;
  /** Avatar / image URL of the other participant */
  image_url: string;
  /** Primary text (Name / username of other participant) */
  text1: string;
  /** Secondary text (Snippet of latest message sent) */
  text2: string;
  /** Whether there are unread messages for the current user */
  has_unread_messages?: boolean | null;
}

export interface GetConversationsParams {
  /** Optional target user id if querying on behalf of user */
  user_id?: number;
  /** Page number for pagination (default: 1) */
  page?: number;
  /** Number of items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetConversationsResponse {
  data: ConversationType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more conversations exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of conversations for current user.
 *
 * @param params - Optional query parameters (page, per_page, user_id)
 * @returns Promise<GetConversationsResponse>
 *
 * @example
 * ```ts
 * // 1. Initial conversations load
 * const res = await getConversationsApi({ page: 1, per_page: 20 });
 * console.log(res.data, res.next_page);
 *
 * // 2. Load next page if available
 * if (res.next_page) {
 *   const nextRes = await getConversationsApi({ page: res.next_page });
 *   console.log(nextRes.data);
 * }
 * ```
 */
export const getConversationsApi = async (
  params?: GetConversationsParams
): Promise<GetConversationsResponse> => {
  const response = await api.get<GetConversationsResponse>("/conversations", {
    params,
  });
  return response.data;
};

export default getConversationsApi;
