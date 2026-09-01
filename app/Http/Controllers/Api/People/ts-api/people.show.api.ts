//@ts-nocheck
/**
 * People Profile Show API Client & Type Definitions
 *
 * Backend Controllers:
 *   - App\Http\Controllers\Api\People\ShowController
 *   - App\Http\Controllers\Api\People\CreatorDropsController
 *
 * Endpoints:
 *   - GET /api/people/{id}
 *   - GET /api/people/{id}/contacts
 *   - GET /api/people/{id}/drops
 */

import api from '@/api/api';

export interface ProfileContactType {
    id: string | number;
    platform: string;
    type: string;
    value: string;
    url: string;
    image_url?: string | null;
}

export interface ProfileDropType {
    id: number;
    image_url: string;
    text1: string;
    text2: string;
    drop_status?: string;
    created_at?: string;
}

export interface ProfileType {
    id: number;
    profile_type: 'mine' | 'creator' | 'sgm' | 'user';
    text1: string;
    text2: string;
    image_url?: string | null;
    friend_status: 'pending' | 'accepted' | 'declined' | 'blocked' | null;
    creator_follow_status: 'followed' | null;
    can_message?: boolean;
    has_contacts?: boolean;
    nb_contacts?: number;
    contacts?: ProfileContactType[];

    friend_request?: {
        id: number;
        user: {
            id: number;
            text1: string;
            text2: string;
        };
    } | null;
}

/**
 * Get profile details by user ID.
 *
 * @param id User ID
 * @returns Promise<ProfileType>
 */
export const getProfileShowApi = async (
    id: number | string,
): Promise<ProfileType> => {
    const response = await api.get<ProfileType>(`/people/${id}`);
    return response.data;
};

/**
 * Get contacts list for a user profile.
 *
 * @param id User ID
 * @returns Promise<ProfileContactType[]>
 */
export const getProfileContactsApi = async (
    id: number | string,
): Promise<ProfileContactType[]> => {
    const response = await api.get<{ data: ProfileContactType[] }>(`/people/${id}/contacts`);
    return response.data?.data || [];
};

/**
 * Get drops list for a creator profile.
 *
 * @param id Creator/User ID
 * @returns Promise<ProfileDropType[]>
 */
export const getProfileDropsApi = async (
    id: number | string,
): Promise<ProfileDropType[]> => {
    const response = await api.get<{ data: ProfileDropType[] }>(`/people/${id}/drops`);
    return response.data?.data || [];
};

export default getProfileShowApi;
