// @ts-nocheck
/**
 * User Registration API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Auth\RegisterController
 * Endpoint: POST /api/auth/register
 */

import api, { setAuthToken } from "@/utils/api";

export interface RegisterPayload {
  username: string;
  name?: string;
  full_name?: string;
  email?: string;
  password: string;
}

export interface AuthRole {
  id: number;
  code: string;
  en?: string;
  fr?: string;
  ar?: string;
}

export interface AuthUser {
  id: number;
  name?: string;
  full_name?: string;
  username?: string;
  email?: string;
  phone_number?: string;
  phone_verified_at?: string | null;
  email_verified_at?: string | null;
  image_url?: string | null;
  is_active: boolean;
  user_status?: string | null;
  wilaya_id?: number | null;
  user_roles: AuthRole[];
  roles: string[];
  created_at: string;
  updated_at: string;
}

export interface RegisterResponse {
  message: string;
  user: AuthUser;
  token: string;
  token_type: "Bearer";
  errors?: Record<string, string[]>;
}

/**
 * Register a new user account and obtain Bearer token.
 *
 * @param payload - Registration data containing username, name/full_name, password (and optional email).
 * @param autoSaveToken - If true (default), automatically stores the returned Bearer token in AsyncStorage/memory.
 * @returns Promise<RegisterResponse>
 *
 * @example
 * ```ts
 * try {
 *   const response = await registerApi({
 *     username: "cooluser",
 *     name: "John Doe",
 *     password: "secretpassword",
 *   });
 *
 *   console.log("Registered user:", response.user);
 *   console.log("Bearer Token:", response.token);
 *   // Token is automatically stored, so subsequent api calls will carry the Authorization header!
 * } catch (error) {
 *   console.error("Registration error:", error.response?.data?.message || error.message);
 * }
 * ```
 */
export const registerApi = async (
  payload: RegisterPayload,
  autoSaveToken = true
): Promise<RegisterResponse> => {
  const response = await api.post<RegisterResponse>("/auth/register", {
    username: payload.username?.trim().toLowerCase(),
    name: payload.name || payload.full_name,
    full_name: payload.full_name || payload.name,
    email: payload.email,
    password: payload.password,
  });

  if (autoSaveToken && response.data?.token) {
    await setAuthToken(response.data.token);
  }

  return response.data;
};

export default registerApi;
