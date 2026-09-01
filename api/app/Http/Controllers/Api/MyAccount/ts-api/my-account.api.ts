// @ts-nocheck
/**
 * My Account & Profile API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\MyAccountController
 *
 * Endpoints:
 *   - GET  /api/my-account
 *   - POST /api/my-account
 *   - PUT  /api/my-account
 */

import api from "@/utils/api";

export type AllowedSection =
  | "essentials:friends"
  | "essentials:followed-creator"
  | "essentials:saved"
  | "essentials:refund-wallet"
  | "creator:become-creator"
  | "creator:followers"
  | "creator:affiliate-library"
  | "creator:my-drops"
  | "creator:balance"
  | "sgm:stores"
  | "sgm:learning-updates";

export interface ProfileInfo {
  /** Avatar / Profile picture URL */
  image_url: string;
  /** Full name or display name */
  text1: string;
  /** Username handle (e.g. "@username") or email */
  text2: string;
}

export interface EssentialsStats {
  /** Total number of accepted friends */
  nb_friends: number;
  /** Total number of followed creators */
  nb_followed_creators: number;
  /** Total number of saved products, drops, and labels */
  nb_saved: number;
}

export interface CreatorLandStats {
  /** Number of products available in the affiliate promotion library */
  nb_affilite_library: number;
  /** Number of followers */
  nb_followers?: number;
}

export interface ResponseType {
  profile: ProfileInfo;
  essentials: EssentialsStats;
  creator_land: CreatorLandStats;
  /** Array of section keys accessible according to user roles */
  allowed_sections: AllowedSection[];
}

export interface ProfileEditType {
  /** Updated name */
  name?: string;
  /** Updated image / avatar URL */
  image_url?: string;
}

export interface UpdateProfileResponse {
  message: string;
  profile: {
    name: string;
    image_url: string;
  };
}

export interface GetMyAccountParams {
  /** Optional target user id if querying on behalf of user */
  user_id?: number;
}

/**
 * Fetch current user account overview (Profile, Essentials stats, Creator stats, and allowed sections).
 *
 * @param params - Optional query parameters (user_id)
 * @returns Promise<ResponseType>
 *
 * @example
 * ```ts
 * const account = await getMyAccountApi();
 * console.log(account.profile.text1, account.essentials.nb_friends, account.allowed_sections);
 * ```
 */
export const getMyAccountApi = async (
  params?: GetMyAccountParams
): Promise<ResponseType> => {
  const response = await api.get<ResponseType>("/my-account", { params });
  return response.data;
};

/**
 * Update current user profile details (name and/or image_url).
 *
 * @param payload - Object containing name and/or image_url
 * @returns Promise<UpdateProfileResponse>
 *
 * @example
 * ```ts
 * const updated = await updateProfileApi({ name: "Amine Bekheira", image_url: "https://..." });
 * console.log(updated.message);
 * ```
 */
export const updateProfileApi = async (
  payload: ProfileEditType
): Promise<UpdateProfileResponse> => {
  const response = await api.post<UpdateProfileResponse>("/my-account", payload);
  return response.data;
};

export default getMyAccountApi;
