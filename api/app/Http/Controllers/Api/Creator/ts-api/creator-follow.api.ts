// @ts-nocheck
/**
 * Send Creator Follow / Unfollow API Client & Types
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\SendFollowController
 *
 * Endpoints:
 *   - POST /api/creators/follow
 *   - POST /api/creators/{id}/follow
 */

import api from "@/utils/api";

export interface SendFollowParams {
  /** Target creator ID */
  creator_id?: number;
  /** Action: 'follow', 'unfollow', or 'toggle' (default) */
  action?: "follow" | "unfollow" | "toggle";
  /** Optional authenticated user ID fallback */
  user_id?: number;
}

export interface SendFollowResponse {
  success: boolean;
  is_following: boolean;
  creator_follow_status: "followed" | null;
  message: string;
  followers_count?: number;
}

/**
 * Follow or unfollow a creator.
 *
 * @param creatorId - Target creator ID
 * @param action - Action to perform ('follow', 'unfollow', or 'toggle')
 * @returns Promise<SendFollowResponse>
 */
export const sendCreatorFollowApi = async (
  creatorId: number,
  action: "follow" | "unfollow" | "toggle" = "toggle"
): Promise<SendFollowResponse> => {
  const response = await api.post<SendFollowResponse>(
    `/creators/${creatorId}/follow`,
    { action }
  );
  return response.data;
};

export default sendCreatorFollowApi;
