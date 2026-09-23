// @ts-nocheck
/**
 * My Account Username API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\UsernameController
 *
 * Endpoints:
 *   - GET  /api/my-account/username
 *   - POST /api/my-account/change-username
 *   - POST /api/my-account/username
 *   - PUT  /api/my-account/username
 *   - PATCH /api/my-account/username
 */

import api from "@/utils/api";

export interface GetUsernameResponse {
  id: number;
  username: string;
  formatted_username: string;
}

export interface UpdateUsernamePayload {
  /** Desired username (can include leading '@' or not) */
  username?: string;
  new_username?: string;
  /** Optional user ID if updating on behalf of specific user */
  user_id?: number;
}

export interface UpdateUsernameResponse {
  message: string;
  username: string;
  formatted_username: string;
  data: {
    id: number;
    username: string;
    formatted_username: string;
    name: string;
  };
}

/**
 * Fetch current user's username.
 *
 * @param params - Optional query parameters (user_id)
 * @returns Promise<GetUsernameResponse>
 */
export const getUsernameApi = async (
  params?: { user_id?: number }
): Promise<GetUsernameResponse> => {
  const response = await api.get<GetUsernameResponse>("/my-account/username", {
    params,
  });
  return response.data;
};

/**
 * Update current user's username.
 *
 * @param payload - Object containing username or new_username
 * @returns Promise<UpdateUsernameResponse>
 *
 * @example
 * ```ts
 * const res = await updateUsernameApi({
 *   username: "new_cool_handle",
 * });
 * console.log(res.message, res.formatted_username);
 * ```
 */
export const updateUsernameApi = async (
  payload: UpdateUsernamePayload
): Promise<UpdateUsernameResponse> => {
  const response = await api.post<UpdateUsernameResponse>(
    "/my-account/change-username",
    payload
  );
  return response.data;
};

export default updateUsernameApi;
