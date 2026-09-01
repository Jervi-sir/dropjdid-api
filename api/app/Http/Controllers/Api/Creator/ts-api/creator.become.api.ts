// @ts-nocheck
/**
 * Become Creator API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\BecomeCreatorController
 *
 * Endpoints:
 *   - GET  /api/creators/become-creator (check if user already applied)
 *   - POST /api/creators/become-creator (submit application)
 */

import api from "@/utils/api";

export interface BecomeCreatorPayload {
  /** User's phone number */
  phone_number: string;
  /** Optional note or additional context */
  note?: string;
  /** Optional user ID if submitting on behalf of a specific user */
  user_id?: number;
}

export interface CreatorRequestRecord {
  id: number;
  user_id: number;
  phone_number: string;
  request_status: "pending" | "approved" | "rejected" | string;
  note?: string | null;
  created_at: string;
  updated_at?: string;
}

export interface BecomeCreatorStatusResponse {
  /** Whether the user has already sent a creator request */
  has_applied: boolean;
  /** Phone number previously submitted, if any */
  phone_number?: string | null;
  /** Request status (e.g. "pending", "approved", "rejected") */
  request_status?: string | null;
  /** Detailed request object if existing */
  request?: CreatorRequestRecord | null;
}

export interface BecomeCreatorResponse {
  message: string;
  has_applied?: boolean;
  phone_number?: string;
  request_status?: string;
  request: CreatorRequestRecord;
}

export interface GetBecomeCreatorStatusParams {
  /** Optional user id if checking for a specific user */
  user_id?: number;
}

/**
 * Check if current user has already applied to become a creator and retrieve their submitted phone number.
 *
 * @param params - Optional query parameters (user_id)
 * @returns Promise<BecomeCreatorStatusResponse>
 *
 * @example
 * ```ts
 * const status = await getBecomeCreatorStatusApi();
 * if (status.has_applied) {
 *   console.log("Already sent with phone:", status.phone_number, status.request_status);
 * }
 * ```
 */
export const getBecomeCreatorStatusApi = async (
  params?: GetBecomeCreatorStatusParams
): Promise<BecomeCreatorStatusResponse> => {
  const response = await api.get<BecomeCreatorStatusResponse>(
    "/creators/become-creator",
    { params }
  );
  return response.data;
};

/**
 * Submit an application to become a creator.
 *
 * @param payload - Object containing phone_number and optional note/user_id
 * @returns Promise<BecomeCreatorResponse>
 *
 * @example
 * ```ts
 * const res = await becomeCreatorApi({ phone_number: "0550123456" });
 * console.log(res.message, res.request.request_status);
 * ```
 */
export const becomeCreatorApi = async (
  payload: BecomeCreatorPayload
): Promise<BecomeCreatorResponse> => {
  const response = await api.post<BecomeCreatorResponse>(
    "/creators/become-creator",
    payload
  );
  return response.data;
};

export default becomeCreatorApi;
