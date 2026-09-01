// @ts-nocheck
/**
 * Creator Wallet Transactions & Balance API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\Wallet\TransactionsController
 *
 * Endpoints:
 *   - GET /api/creators/wallet/preview
 *   - GET /api/creators/wallet/transactions
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

export interface CreatorBalanceType {
  total_balance: number;
  pending_balance?: number;
  currency?: string;
  user_id?: number | null;
  data: TransactionType[];
}

export interface GetCreatorTransactionsParams {
  /** Page number (default: 1) */
  page?: number;
  /** Items per page (default: 20, max: 100) */
  per_page?: number;
  /** Optional transaction type filter */
  type?: string;
  /** Optional status filter */
  status?: string;
  /** Optional creator user ID fallback */
  creator_id?: number;
  user_id?: number;
}

export interface GetCreatorTransactionsResponse {
  data: TransactionType[];
  current_page: number;
  per_page: number;
  total: number;
  next_page: number | null;
  total_balance: number;
  pending_balance?: number;
  currency?: string;
  user_id?: number | null;
}

/**
 * Get creator wallet total balance and preview of latest transactions.
 *
 * @param limit - Optional number of latest transactions (default: 4)
 * @param creatorId - Optional creator user ID override
 * @returns Promise<CreatorBalanceType>
 */
export const getCreatorWalletPreviewApi = async (
  limit = 4,
  creatorId?: number
): Promise<CreatorBalanceType> => {
  const response = await api.get<CreatorBalanceType>(
    "/creators/wallet/preview",
    {
      params: {
        limit,
        creator_id: creatorId,
        user_id: creatorId,
      },
    }
  );
  return response.data;
};

/**
 * Get paginated list of all transactions for the creator.
 *
 * @param params - Pagination & filter parameters
 * @returns Promise<GetCreatorTransactionsResponse>
 */
export const getCreatorWalletTransactionsApi = async (
  params?: GetCreatorTransactionsParams
): Promise<GetCreatorTransactionsResponse> => {
  const response = await api.get<GetCreatorTransactionsResponse>(
    "/creators/wallet/transactions",
    { params }
  );
  return response.data;
};

export default {
  getCreatorWalletPreviewApi,
  getCreatorWalletTransactionsApi,
};
