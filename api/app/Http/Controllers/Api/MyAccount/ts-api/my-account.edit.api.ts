// @ts-nocheck
/**
 * Edit My Account API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\EditMyAccountController
 *
 * Endpoints:
 *   - GET  /api/my-account/edit-profile
 *   - POST /api/my-account/edit-profile
 */

import api from "@/utils/api";

export interface AccountType {
  /** User identifier */
  id: number;
  /** Avatar / Profile picture URL */
  image_url: string;
  /** Full name / Display name */
  name: string;
  /** Number of registered contacts (phone, email, socials) */
  nb_contacts: number;
}

export interface UpdateAccountPayload {
  /** Updated name */
  name?: string;
  /** Updated avatar / image URL */
  image_url?: string;
  /** Image file to upload */
  image?: { uri: string; name?: string; type?: string };
  /** Optional user ID if updating on behalf of specific user */
  user_id?: number;
}

export interface UpdateAccountResponse {
  message: string;
  data: AccountType;
}

export interface GetEditAccountParams {
  /** Optional user id if querying on behalf of user */
  user_id?: number;
}

/**
 * Fetch account profile info for the edit profile screen matching AccountType.
 *
 * @param params - Optional query parameters (user_id)
 * @returns Promise<AccountType>
 *
 * @example
 * ```ts
 * const account = await getEditAccountApi();
 * console.log(account.name, account.image_url, account.nb_contacts);
 * ```
 */
export const getEditAccountApi = async (
  params?: GetEditAccountParams
): Promise<AccountType> => {
  const response = await api.get<AccountType>("/my-account/edit-profile", {
    params,
  });
  return response.data;
};

/**
 * Update account profile info (name, image_url, or uploaded image file).
 *
 * @param payload - Object containing name, image_url or image file, or FormData
 * @returns Promise<UpdateAccountResponse>
 */
export const updateAccountApi = async (
  payload: UpdateAccountPayload | FormData
): Promise<UpdateAccountResponse> => {
  let body: any = payload;
  let headers: Record<string, string> | undefined = undefined;

  if (typeof FormData !== "undefined" && payload instanceof FormData) {
    body = payload;
    headers = { "Content-Type": "multipart/form-data" };
  } else if (payload && (payload as UpdateAccountPayload).image) {
    const fd = new FormData();
    const p = payload as UpdateAccountPayload;
    if (p.name !== undefined) fd.append("name", p.name);
    if (p.user_id !== undefined) fd.append("user_id", String(p.user_id));
    if (p.image) {
      const filename = p.image.name || p.image.uri.split("/").pop() || "avatar.jpg";
      const match = /\.(\w+)$/.exec(filename);
      const mimeType = p.image.type || (match ? `image/${match[1]}` : "image/jpeg");
      fd.append("image", {
        uri: p.image.uri,
        name: filename,
        type: mimeType,
      } as any);
    }
    body = fd;
    headers = { "Content-Type": "multipart/form-data" };
  }

  const response = await api.post<UpdateAccountResponse>(
    "/my-account/edit-profile",
    body,
    headers ? { headers } : undefined
  );
  return response.data;
};

export default getEditAccountApi;
