// @ts-nocheck
/**
 * Event API Client
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Events\EventController
 *
 * Endpoints:
 *   - GET /api/events
 *   - GET /api/events/{id}
 *   - POST /api/events
 */

import api from '@/api/api';

export interface EventType {
    id: number;
    image_url: string;
    text1: string;
    text2: string;
    url?: string;
    user_id?: number | null;
    status?: 'draft' | 'active' | 'inactive' | 'completed';
    starts_at?: string | null;
    ends_at?: string | null;
    meta?: {
        location?: string;
        city?: string;
        badge?: string;
        cta_text?: string;
        organizer?: string;
        capacity?: number;
        highlights?: string[];
        [key: string]: any;
    } | null;
    created_at?: string;
    updated_at?: string;
}

export interface GetEventsResponse {
    current_page: number;
    data: EventType[];
    total: number;
    per_page: number;
    last_page: number;
    next_page?: number | null;
}

export interface GetEventResponse {
    data: EventType;
}

/**
 * Fetch list of events with period/status filtering.
 *
 * @param params - Optional query params (filter, page, per_page, user_id)
 * @returns Promise<GetEventsResponse>
 */
export const getEventsApi = async (params?: {
    filter?: 'active' | 'upcoming' | 'past' | 'all';
    page?: number;
    per_page?: number;
    user_id?: number | string;
}): Promise<GetEventsResponse> => {
    const response = await api.get<GetEventsResponse>('/events', {
        params,
    });
    return response.data;
};

/**
 * Fetch a single event by ID.
 *
 * @param id - Event ID
 * @returns Promise<GetEventResponse>
 */
export const getEventByIdApi = async (
    id: number | string,
): Promise<GetEventResponse> => {
    const response = await api.get<GetEventResponse>(`/events/${id}`);
    return response.data;
};

export default {
    getEventsApi,
    getEventByIdApi,
};
