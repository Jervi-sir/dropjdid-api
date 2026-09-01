// @ts-nocheck
/**
 * Drop Title Availability API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Creator\UpsertDropController
 *
 * Endpoint:
 *   - GET /api/creators/drops/check-title
 */

import api from "@/utils/api";

export interface CheckDropTitleParams {
  /** The title of the drop to check */
  title: string;
  /** Optional drop ID to ignore when editing an existing drop */
  drop_id?: number | null;
}

export interface CheckDropTitleResponse {
  available: boolean;
  message: string;
  errors?: Record<string, string[]>;
}

/**
 * Check if a drop title is available.
 *
 * @param title - The title string to check
 * @param dropId - Optional current drop ID (to exclude from collision check during edit)
 * @returns Promise<CheckDropTitleResponse>
 *
 * @example
 * ```ts
 * try {
 *   const { available, message } = await checkDropTitleAvailabilityApi("Summer Collection 2026");
 *   if (available) {
 *     console.log("Title is available!");
 *   } else {
 *     console.log(message); // "Drop title is already taken."
 *   }
 * } catch (error) {
 *   console.error("Failed to check drop title availability", error);
 * }
 * ```
 */
export const checkDropTitleAvailabilityApi = async (
  title: string,
  dropId?: number | null
): Promise<CheckDropTitleResponse> => {
  const response = await api.get<CheckDropTitleResponse>("/creators/drops/check-title", {
    params: {
      title: title.trim(),
      ...(dropId ? { drop_id: dropId } : {}),
    },
  });
  return response.data;
};

export default checkDropTitleAvailabilityApi;
