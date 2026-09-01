// @ts-nocheck
/**
 * User Profile / Me API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Auth\MeController
 * Endpoint: GET /api/auth/me
 */

import api from '@/utils/api';
import { AuthUser } from './register.apis';

export interface MeResponse {
    user: AuthUser;
}

/**
 * Fetch the currently authenticated user profile including user_roles, is_active, and user_status.
 *
 * @returns Promise<MeResponse>
 */
export const getMeApi = async (): Promise<MeResponse> => {
    const response = await api.get<MeResponse>('/auth/me');
    return response.data;
};

export default getMeApi;
