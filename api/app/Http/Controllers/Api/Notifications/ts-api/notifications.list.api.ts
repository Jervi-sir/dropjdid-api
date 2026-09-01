// @ts-nocheck
/**
 * Notifications API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Notifications\ListController
 *
 * Endpoint:
 *   - GET /api/notifications
 */

import api from "@/utils/api";

export interface SaleMeta {
  target: "drop" | "product";
  text1: string;
  price: string;
  direction: "up" | "down";
}

export interface WithdrawMeta {
  target: "edahabia";
  text1: string;
  price: string;
  direction: "up" | "down";
}

export interface OrderMeta {
  text1: string;
  text2: string;
}

export interface FriendRequestMeta {
  target: "received" | "accepted" | "rejected" | "accepted-by-target";
  text1: string;
  text2: string;
}

export interface FollowerMeta {
  text1: string;
  text2: string;
}

export interface NotificationType {
  id: number;
  type: "sale" | "withdraw" | "order" | "friend-request" | "follower";
  created_at: string;
  image_url: string;
  sale_meta?: SaleMeta | null;
  withdraw_meta?: WithdrawMeta | null;
  order_meta?: OrderMeta | null;
  friend_request_meta?: FriendRequestMeta | null;
  follower_meta?: FollowerMeta | null;
}

export interface GetNotificationsParams {
  /** Filter tab: 'all', 'orders' (includes sales, orders, withdraws), or 'requests' (friend requests, followers) */
  tab?: "all" | "orders" | "requests";
  /** Target user id if querying on behalf of specific user */
  user_id?: number;
  /** Page number for pagination (default: 1) */
  page?: number;
  /** Number of items per page (default: 20) */
  per_page?: number;
}

export interface GetNotificationsResponse {
  data: NotificationType[];
  current_page?: number;
  per_page?: number;
  total?: number;
  /** Next page number if more items exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch list of notifications with next_page pagination support.
 *
 * @param params - Optional parameters (tab, user_id, page, per_page)
 * @returns Promise<GetNotificationsResponse>
 *
 * @example
 * ```ts
 * // 1. Initial page load
 * const res = await getNotificationsApi({ tab: 'all', page: 1 });
 * console.log(res.data, res.next_page);
 *
 * // 2. Load next page if available
 * if (res.next_page) {
 *   const nextPageRes = await getNotificationsApi({ tab: 'all', page: res.next_page });
 *   console.log(nextPageRes.data);
 * }
 * ```
 */
export const getNotificationsApi = async (
  params?: GetNotificationsParams
): Promise<GetNotificationsResponse> => {
  const response = await api.get<GetNotificationsResponse>("/notifications", {
    params,
  });
  return response.data;
};

export default getNotificationsApi;
