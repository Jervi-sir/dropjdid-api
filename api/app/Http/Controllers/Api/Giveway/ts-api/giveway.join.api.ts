// @ts-nocheck
/**
 * Giveaway Join & Status Check API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Giveway\GivewayJoinController
 *
 * Endpoints:
 *   - POST /api/giveaway/join : Join the active (or specified) giveaway using a phone number
 *   - GET  /api/giveaway/join : Check if the user/phone has already joined and get the registered phone number & eligibility
 */

import api from "@/utils/api";

export interface GiveawayPrizeSummary {
  /** Prize ID */
  id: number;
  /** Title of the giveaway prize (e.g. "Giveaway on iPhone 17 pro max") */
  text1: string;
  /** Date range label (e.g. "Apr 1 - Apr 30" or "Active this month") */
  date_range: string;
}

export interface JoinGiveawayPayload {
  /** Participant phone number (required, 8-20 characters) */
  phone_number: string;
  /** Optional specific prize ID (defaults to active/latest giveaway) */
  prize_id?: number;
}

export interface JoinGiveawayResponse {
  /** Success message */
  message: string;
  /** True when successfully joined */
  has_joined: boolean;
  /** Phone number used for joining */
  phone_number: string;
  /** Target giveaway prize ID */
  prize_id?: number;
  /** Whether the user has at least one order placed during the prize active dates */
  is_eligible: boolean;
  /** Number of qualifying orders placed by the user */
  orders_count: number;
  /** Giveaway summary metadata */
  prize: GiveawayPrizeSummary;
  /** Remaining time in seconds before giveaway closes */
  time_left: number;
}

export interface CheckGiveawayJoinStatusParams {
  /** Optional prize ID (defaults to active/latest giveaway) */
  prize_id?: number;
  /** Optional phone number to check registration */
  phone_number?: string;
  /** Optional user ID if querying for specific participant */
  user_id?: number;
}

export interface CheckGiveawayJoinStatusResponse {
  /** Whether the user/phone number has already joined this giveaway */
  has_joined: boolean;
  /** The registered phone number if already joined, or null */
  phone_number: string | null;
  /** Target giveaway prize ID */
  prize_id: number;
  /** ISO timestamp when the user joined, or null */
  joined_at?: string | null;
  /** Whether the user has at least one order placed during the prize active dates */
  is_eligible: boolean;
  /** Number of qualifying orders placed by the user */
  orders_count: number;
  /** Giveaway summary metadata */
  prize: GiveawayPrizeSummary;
  /** Remaining time in seconds before giveaway closes */
  time_left: number;
  /** Informational message (e.g. if no active prize exists) */
  message?: string;
}

/**
 * Join the current (or specified) giveaway with a phone number.
 *
 * @param payload - Join payload containing `phone_number` and optional `prize_id`
 * @returns Promise<JoinGiveawayResponse>
 *
 * @example
 * ```ts
 * const res = await joinGiveawayApi({
 *   phone_number: "0555123456",
 *   prize_id: 1,
 * });
 * console.log(res.message); // "You have joined the giveaway successfully!"
 * console.log(res.is_eligible); // true
 * console.log(res.orders_count); // 2
 * ```
 */
export const joinGiveawayApi = async (
  payload: JoinGiveawayPayload
): Promise<JoinGiveawayResponse> => {
  const response = await api.post<JoinGiveawayResponse>(
    "/giveaway/join",
    payload
  );
  return response.data;
};

/**
 * Check if the user or phone number has already joined the giveaway,
 * and retrieve the registered phone number, eligibility, and details.
 *
 * @param params - Optional query parameters (`prize_id`, `phone_number`, `user_id`)
 * @returns Promise<CheckGiveawayJoinStatusResponse>
 *
 * @example
 * ```ts
 * const status = await checkGiveawayJoinStatusApi({ prize_id: 1 });
 * if (status.has_joined) {
 *   console.log("Already joined with phone:", status.phone_number);
 *   console.log("Eligible (orders made):", status.is_eligible);
 * }
 * ```
 */
export const checkGiveawayJoinStatusApi = async (
  params?: CheckGiveawayJoinStatusParams
): Promise<CheckGiveawayJoinStatusResponse> => {
  const response = await api.get<CheckGiveawayJoinStatusResponse>(
    "/giveaway/join",
    { params }
  );
  return response.data;
};

export default {
  joinGiveawayApi,
  checkGiveawayJoinStatusApi,
};
