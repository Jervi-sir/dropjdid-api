// @ts-nocheck
/**
 * Order Details & Progress API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Order\ShowController
 *
 * Endpoint:
 *   - GET /api/orders/{id}
 */

import api from "@/utils/api";

export interface OrderTimelineItem {
  /** Sequential step number (1: pending, 2: shipped, 3: delivered) */
  step: number;
  /** Status code identifier (e.g. "pending", "shipped", "delivered") */
  code: string;
  /** Display title for this timeline step */
  title: string;
  /** Whether this timeline stage is marked as completed */
  is_completed: boolean;
  /** Whether this timeline stage is the active current status */
  is_current: boolean;
}

export interface OrderDetailType {
  /** Numeric ID of the order */
  id: number;
  /** Order reference code (e.g. "ORD-2026-X8Y1Z2") */
  order_number: string;
  /** Display identifier formatted with hashtag (e.g. "#ORD-2026-X8Y1Z2") */
  order_id: string;
  /** Primary product or drop image URL */
  image_url: string;
  /** Machine-readable status code */
  status_code:
    | "pending"
    | "confirmed"
    | "processing"
    | "shipped"
    | "delivered"
    | "cancelled"
    | "returned"
    | string;
  /** Human-readable status label */
  status_label: string;
  /** Hex color for the order status badge/timeline */
  status_color: string;
  /** Step-by-step progress timeline list */
  timeline: OrderTimelineItem[];

  /** Customer full name */
  full_name: string;
  /** Customer contact phone number */
  phone_number: string;
  /** Destination wilaya name */
  wilaya: string;
  /** Destination baladiya / commune name */
  baladiya: string;
  /** Full street home address or office pickup location */
  home_address: string;
  /** Delivery method code ("home" | "desk") */
  delivery_method: string;
  /** Delivery method display label (e.g. "Home delivery" or "To delivery office (Stop Desk)") */
  delivery_label: string;

  /** Summarized item size and quantity (e.g. "2 (S), 1 (M)") */
  sizes_summary: string;
  /** Subtotal amount in DZD */
  subtotal: number;
  /** Delivery fees in DZD */
  delivery_fees: number;
  /** Transaction fees in DZD */
  transaction_fee: number;
  /** Grand total amount paid in DZD */
  total: number;
  /** Total refundable amount if cancelled in DZD */
  refundable_amount: number;

  /** Whether the customer reported a claim issue */
  has_claim_issue: boolean;
  /** Description of claim issue if any */
  claim_issue?: string | null;
  /** ISO timestamp of order creation */
  created_at: string | null;
}

export interface GetOrderDetailResponse {
  success: boolean;
  data: OrderDetailType;
}

/**
 * Fetch full order info and timeline status details.
 *
 * @param idOrOrderNumber - Order ID or order number (e.g. 123, "ORD-2026-ABC123", "#ORD-2026-ABC123")
 * @returns Promise<OrderDetailType>
 *
 * @example
 * ```ts
 * const order = await getOrderDetailApi(1);
 * console.log(order.status_code, order.sizes_summary, order.total);
 * ```
 */
export const getOrderDetailApi = async (
  idOrOrderNumber: string | number
): Promise<OrderDetailType> => {
  const res = await api.get<GetOrderDetailResponse>(`/orders/${idOrOrderNumber}`);
  return res.data.data;
};

export default getOrderDetailApi;
