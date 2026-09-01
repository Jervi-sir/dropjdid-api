// @ts-nocheck
/**
 * Creator Wallet Check Identity API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\Wallet\CheckIdentityController
 *
 * Endpoint:
 *   - POST /api/creators/wallet/check-identity
 */

import api from "@/utils/api";

export interface CreatorCheckIdentityPayload {
  /** The password of the authenticated user to verify */
  password?: string;
  /** Alias for password */
  current_password?: string;
  /** Optional user ID fallback if not using Sanctum token header */
  user_id?: number;
}

export interface CreatorCheckIdentityResponse {
  /** Indicates whether the request succeeded */
  success: boolean;
  /** True if password matched, false otherwise */
  valid: boolean;
  /** The verified user ID */
  user_id?: number | null;
  /** Response status or informative message */
  message: string;
  /** Validation error messages if any */
  errors?: Record<string, string[]>;
}

/**
 * Verify the creator's identity / password before accessing the creator wallet.
 *
 * @param payload - Check identity payload containing the user's password
 * @returns Promise<CreatorCheckIdentityResponse>
 */
export const checkCreatorIdentityApi = async (
  payload: CreatorCheckIdentityPayload | string
): Promise<CreatorCheckIdentityResponse> => {
  const data = typeof payload === "string" ? { password: payload } : payload;
  const response = await api.post<CreatorCheckIdentityResponse>(
    "/creators/wallet/check-identity",
    data
  );
  return response.data;
};

export default checkCreatorIdentityApi;
