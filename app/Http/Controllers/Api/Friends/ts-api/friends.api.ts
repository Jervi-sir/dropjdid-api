// @ts-nocheck
/**
 * Friends & Share-to-Friends API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Friends\ShareToFriendsController
 *
 * Endpoint:
 *   - GET /api/friends
 *   - GET /api/friends/share
 */

import api from '@/api/api';

export interface FriendType {
    /** User unique ID */
    id: number;
    /** Full URL to friend's avatar/profile image */
    image_url: string;
    /** Primary display text (Full name or display name) */
    text1: string;
    /** Secondary display text (Username handle e.g. "@username" or email) */
    text2: string;
}

export interface GetFriendsParams {
    /** Search keyword to filter friends by name, username, or email */
    search?: string;
    query?: string;
    q?: string;
    keyword?: string;
    /** Page number for pagination (default: 1) */
    page?: number;
    /** Number of items per page (default: 20) */
    per_page?: number;
}

export interface GetFriendsResponse {
    data: FriendType[];
    total?: number;
    page?: number;
    per_page?: number;
    next_page?: number | null;
}

/**
 * Fetch friends list for sharing or listing.
 *
 * @param params - Search and pagination parameters
 * @returns Promise<GetFriendsResponse>
 *
 * @example
 * ```ts
 * const res = await getFriendsApi({ search: 'amine', page: 1 });
 * console.log(res.data);
 * ```
 */
export const getFriendsApi = async (
    params?: GetFriendsParams,
): Promise<GetFriendsResponse> => {
    const response = await api.get<GetFriendsResponse>('/friends', { params });
    return response.data;
};

export default getFriendsApi;
