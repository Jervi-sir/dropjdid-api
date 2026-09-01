// @ts-nocheck
/**
 * My Account Password API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\PasswordController
 *
 * Endpoints:
 *   - POST /api/my-account/change-password
 *   - POST /api/my-account/password
 *   - PUT  /api/my-account/password
 */

import api from "@/utils/api";

export interface ChangePasswordPayload {
  /** Current / old password */
  old_password?: string;
  current_password?: string;
  /** New password */
  new_password?: string;
  password?: string;
  /** Optional new password confirmation */
  new_password_confirmation?: string;
  password_confirmation?: string;
  /** Optional user ID if updating on behalf of specific user */
  user_id?: number;
}

export interface ChangePasswordResponse {
  message: string;
}

/**
 * Change current user's password.
 *
 * @param payload - Object containing old/current password, new password, and optional confirmation
 * @returns Promise<ChangePasswordResponse>
 *
 * @example
 * ```ts
 * const res = await changePasswordApi({
 *   old_password: "oldSecretPassword",
 *   new_password: "newSecretPassword123",
 * });
 * console.log(res.message);
 * ```
 */
export const changePasswordApi = async (
  payload: ChangePasswordPayload
): Promise<ChangePasswordResponse> => {
  const response = await api.post<ChangePasswordResponse>(
    "/my-account/change-password",
    payload
  );
  return response.data;
};

export default changePasswordApi;
