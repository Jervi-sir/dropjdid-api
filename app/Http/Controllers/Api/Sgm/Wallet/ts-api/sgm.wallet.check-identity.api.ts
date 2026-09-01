// @ts-nocheck
/**
 * SGM Wallet Check Identity API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\Wallet\CheckIdentityController
 *
 * Endpoint:
 *   - POST /api/sgm/wallet/check-identity
 */

import api from "@/utils/api";

export interface CheckIdentityPayload {
  /** The password of the authenticated user to verify */
  password?: string;
  /** Alias for password */
  current_password?: string;
  /** Optional store ID for store-specific wallet access */
  store_id?: number;
  /** Optional user ID fallback if not using Sanctum token header */
  user_id?: number;
}

export interface CheckIdentityResponse {
  /** Indicates whether the request succeeded */
  success: boolean;
  /** True if password matched, false otherwise */
  valid: boolean;
  /** The verified store ID if applicable */
  store_id?: number | null;
  /** Wallet level: user or store */
  level?: "user" | "store";
  /** Response status or informative message */
  message: string;
  /** Validation error messages if any */
  errors?: Record<string, string[]>;
}

/**
 * Verify the store owner's identity / password before accessing the store wallet.
 *
 * @param payload - Check identity payload containing the user's password and optional store_id
 * @returns Promise<CheckIdentityResponse>
 */
export const checkIdentityApi = async (
  payload: CheckIdentityPayload | string
): Promise<CheckIdentityResponse> => {
  const data = typeof payload === "string" ? { password: payload } : payload;
  const response = await api.post<CheckIdentityResponse>(
    "/sgm/wallet/check-identity",
    data
  );
  return response.data;
};

export default checkIdentityApi;
