// @ts-nocheck
/**
 * User Login API Client
 *
 * Backend Controller: App\Http\Controllers\Api\Auth\LoginController
 * Endpoint: POST /api/auth/login
 */

import api, { setAuthToken, clearAuthToken } from '@/utils/api';
import { AuthUser } from './register.apis';

export interface LoginPayload {
    username?: string;
    email?: string;
    login?: string;
    password: string;
}

export interface LoginResponse {
    message: string;
    user: AuthUser;
    token: string;
    token_type: 'Bearer';
    errors?: Record<string, string[]>;
}

/**
 * Log in a user with username/email and password.
 *
 * @param payload - Credentials containing username/email and password.
 * @param autoSaveToken - If true (default), saves the token automatically into AsyncStorage and memory.
 * @returns Promise<LoginResponse>
 */
export const loginApi = async (
    payload: LoginPayload,
    autoSaveToken = true,
): Promise<LoginResponse> => {
    const response = await api.post<LoginResponse>('/auth/login', payload);

    if (autoSaveToken && response.data?.token) {
        await setAuthToken(response.data.token);
    }

    return response.data;
};

/**
 * Logout the currently authenticated user and revoke current bearer token.
 */
export const logoutApi = async (): Promise<{ message: string }> => {
    try {
        const response = await api.post<{ message: string }>('/auth/logout');
        return response.data;
    } finally {
        await clearAuthToken();
    }
};

export default loginApi;
