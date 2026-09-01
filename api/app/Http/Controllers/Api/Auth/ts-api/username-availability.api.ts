// @ts-nocheck
/**
 * Username Availability API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Auth\UsernameAvailabilityController
 * Endpoint: GET /api/auth/check-username (or POST /api/auth/check-username)
 */

import api from "@/utils/api";

export interface CheckUsernameParams {
  username: string;
}

export interface CheckUsernameResponse {
  available: boolean;
  message: string;
  errors?: Record<string, string[]>;
}

/**
 * Check if a username is available.
 *
 * @param username - The username string to check (min 3 chars, letters/numbers/dots/underscores).
 * @returns Promise<CheckUsernameResponse>
 *
 * @example
 * ```ts
 * try {
 *   const { available, message } = await checkUsernameAvailabilityApi("cooluser");
 *   if (available) {
 *     console.log("Username is free to use!");
 *   } else {
 *     console.log(message); // "Username is already taken."
 *   }
 * } catch (error) {
 *   console.error("Failed to check username availability", error);
 * }
 * ```
 */
export const checkUsernameAvailabilityApi = async (
  username: string
): Promise<CheckUsernameResponse> => {
  const response = await api.get<CheckUsernameResponse>("/auth/check-username", {
    params: { username: username.trim().toLowerCase() },
  });
  return response.data;
};

export default checkUsernameAvailabilityApi;
