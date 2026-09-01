// @ts-nocheck
/**
 * Orders API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Order\ListController
 *
 * Endpoint:
 *   - GET /api/orders
 */

import api from "@/utils/api";

export interface OrderStatus {
  /** Machine-readable status code (e.g. "pending", "confirmed", "processing", "shipped", "delivered", "cancelled", "returned") */
  code: string;
  /** Human-readable status label (e.g. "Shipping started", "Delivered", "Order Confirmed") */
  label: string;
}

export interface OrderType {
  /** Numeric ID of the order */
  id: number;
  /** Formatted order display identifier (e.g. "#478197") */
  order_id: string;
  /** Full URL of the primary product/drop image in the order */
  image_url: string;
  /** Status object with code and label */
  status: OrderStatus;
}

export interface GetOrdersParams {
  /** Optional user id if querying for a specific user */
  user_id?: number;
  /** Page number for pagination (default: 1) */
  page?: number;
  /** Number of items per page (default: 20, max: 100) */
  per_page?: number;
}

export interface GetOrdersResponse {
  data: OrderType[];
  current_page: number;
  per_page: number;
  total: number;
  /** Next page number if more pages exist, otherwise null */
  next_page: number | null;
}

/**
 * Fetch paginated list of user orders.
 *
 * @param params - Optional query parameters (page, per_page, user_id)
 * @returns Promise<GetOrdersResponse>
 *
 * @example
 * ```ts
 * // 1. Fetch first page of orders
 * const res = await getOrdersApi({ page: 1, per_page: 10 });
 * console.log(res.data, res.next_page);
 *
 * // 2. Load next page if available
 * if (res.next_page) {
 *   const nextPageRes = await getOrdersApi({ page: res.next_page });
 *   console.log(nextPageRes.data);
 * }
 * ```
 */
export const getOrdersApi = async (
  params?: GetOrdersParams
): Promise<GetOrdersResponse> => {
  const response = await api.get<GetOrdersResponse>("/orders", { params });
  return response.data;
};

export default getOrdersApi;
