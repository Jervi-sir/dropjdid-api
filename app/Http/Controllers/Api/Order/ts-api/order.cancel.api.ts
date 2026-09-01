// @ts-nocheck
/**
 * Cancel Order API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Order\CancelController
 *
 * Endpoint:
 *   - POST /api/orders/{id}/cancel
 */

import api from "@/utils/api";

export interface CancelOrderResponse {
  success: boolean;
  message: string;
  data?: {
    id: number;
    order_number: string;
    order_status_code: string;
    status_label: string;
    refundable_amount: number;
  };
}

/**
 * Cancel an order if it is in pending/confirmed status.
 *
 * @param idOrOrderNumber - Order ID or order number (e.g. 123, "ORD-2026-ABC123")
 * @returns Promise<CancelOrderResponse>
 *
 * @example
 * ```ts
 * const res = await cancelOrderApi(123);
 * if (res.success) {
 *   console.log("Order cancelled successfully!");
 * }
 * ```
 */
export const cancelOrderApi = async (
  idOrOrderNumber: string | number
): Promise<CancelOrderResponse> => {
  const res = await api.post<CancelOrderResponse>(
    `/orders/${idOrOrderNumber}/cancel`
  );
  return res.data;
};

export default cancelOrderApi;
