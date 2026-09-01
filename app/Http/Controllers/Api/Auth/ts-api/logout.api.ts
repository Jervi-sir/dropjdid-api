// @ts-nocheck
/**
 * User Logout API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Auth\LogoutController
 * Endpoint: POST /api/auth/logout
 * Middleware: auth:sanctum
 */

import api, { clearAuthToken } from "@/utils/api";

export interface LogoutResponse {
  message: string;
}

/**
 * Logout the currently authenticated user, revoke Bearer token on backend,
 * and clear token from local storage and memory cache.
 *
 * @returns Promise<LogoutResponse>
 *
 * @example
 * ```ts
 * try {
 *   const response = await logoutApi();
 *   console.log(response.message); // "Logged out successfully."
 *   // Token is automatically cleared from storage
 * } catch (error) {
 *   console.error("Logout error:", error);
 * }
 * ```
 */
export const logoutApi = async (): Promise<LogoutResponse> => {
  try {
    const response = await api.post<LogoutResponse>("/auth/logout");
    return response.data;
  } finally {
    await clearAuthToken();
  }
};

export default logoutApi;
