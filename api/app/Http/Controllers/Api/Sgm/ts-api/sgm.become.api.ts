// @ts-nocheck
/**
 * Become SGM (Store General Manager) API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\BecomeSgmController
 *
 * Endpoints:
 *   - GET  /api/sgm/become-sgm (check if user already applied)
 *   - POST /api/sgm/become-sgm (submit application)
 */

import api from "@/utils/api";

export interface BecomeSgmPayload {
  /** User's phone number */
  phone_number: string;
  /** Optional note or additional context */
  note?: string;
  /** Optional user ID if submitting on behalf of a specific user */
  user_id?: number;
}

export interface SgmRequestRecord {
  id: number;
  user_id: number;
  contact: string;
  phone_number?: string;
  type?: string;
  target?: string;
  status: "pending" | "approved" | "rejected" | string;
  request_status?: "pending" | "approved" | "rejected" | string;
  note?: string | null;
  created_at: string;
  updated_at?: string;
}

export interface BecomeSgmStatusResponse {
  /** Whether the user has already sent an SGM application */
  has_applied: boolean;
  /** Phone number previously submitted, if any */
  phone_number?: string | null;
  /** Request status (e.g. "pending", "approved", "rejected") */
  request_status?: string | null;
  /** Detailed request object if existing */
  request?: SgmRequestRecord | null;
}

export interface BecomeSgmResponse {
  message: string;
  has_applied?: boolean;
  phone_number?: string;
  request_status?: string;
  request: SgmRequestRecord;
}

export interface GetBecomeSgmStatusParams {
  /** Optional user id if checking for a specific user */
  user_id?: number;
}

/**
 * Check if current user has already applied to become an SGM / open a store and retrieve their submitted phone number.
 *
 * @param params - Optional query parameters (user_id)
 * @returns Promise<BecomeSgmStatusResponse>
 *
 * @example
 * ```ts
 * const status = await getBecomeSgmStatusApi();
 * if (status.has_applied) {
 *   console.log("Already sent with phone:", status.phone_number, status.request_status);
 * }
 * ```
 */
export const getBecomeSgmStatusApi = async (
  params?: GetBecomeSgmStatusParams
): Promise<BecomeSgmStatusResponse> => {
  const response = await api.get<BecomeSgmStatusResponse>(
    "/sgm/become-sgm",
    { params }
  );
  return response.data;
};

/**
 * Submit an application to become a Store General Manager (SGM) / open a store.
 *
 * @param payload - Object containing phone_number and optional note/user_id
 * @returns Promise<BecomeSgmResponse>
 *
 * @example
 * ```ts
 * const res = await becomeSgmApi({ phone_number: "0550123456" });
 * console.log(res.message, res.request.request_status);
 * ```
 */
export const becomeSgmApi = async (
  payload: BecomeSgmPayload
): Promise<BecomeSgmResponse> => {
  const response = await api.post<BecomeSgmResponse>(
    "/sgm/become-sgm",
    payload
  );
  return response.data;
};

export default becomeSgmApi;
