// @ts-nocheck
/**
 * SGM Wallet Transactions & Balance API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Sgm\Wallet\TransactionsController
 *
 * Endpoints:
 *   - GET /api/sgm/wallet/preview
 *   - GET /api/sgm/wallet/transactions
 */

import api from "@/utils/api";

export interface TransactionType {
  id: number;
  type: string;
  text1: string;
  text2: string;
  image_url?: string | null;
  price: {
    amount: number;
    direction: "plus" | "minus";
  };
  status?: string | null;
  balance_before?: number;
  balance_after?: number;
  created_at?: string | null;
}

export interface BalanceType {
  total_balance: number;
  pending_balance?: number;
  currency?: string;
  store_id?: number | null;
  level?: "user" | "store";
  wallet_id?: number;
  data: TransactionType[];
}

export interface GetTransactionsParams {
  /** Target Store ID */
  store_id?: number;
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
  /** Optional user id fallback */
  user_id?: number;
}

export interface GetTransactionsResponse {
  data: TransactionType[];
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
  total_balance?: number;
  pending_balance?: number;
  currency?: string;
  store_id?: number | null;
  level?: "user" | "store";
  wallet_id?: number;
}

/**
 * Get store wallet total balance and preview of latest transactions.
 *
 * @param storeId - Store ID to retrieve wallet for
 * @param limit - Optional number of latest transactions (default: 4)
 * @returns Promise<BalanceType>
 */
export const getWalletPreviewApi = async (
  storeId?: number,
  limit = 4
): Promise<BalanceType> => {
  const response = await api.get<BalanceType>("/sgm/wallet/preview", {
    params: {
      store_id: storeId,
      limit,
    },
  });
  return response.data;
};

/**
 * Get paginated list of all transactions for a specific store.
 *
 * @param params - Pagination & Store parameters (store_id, page, per_page)
 * @returns Promise<GetTransactionsResponse>
 */
export const getWalletTransactionsApi = async (
  params?: GetTransactionsParams
): Promise<GetTransactionsResponse> => {
  const response = await api.get<GetTransactionsResponse>(
    "/sgm/wallet/transactions",
    { params }
  );
  return response.data;
};

export default {
  getWalletPreviewApi,
  getWalletTransactionsApi,
};
