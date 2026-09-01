// @ts-nocheck
/**
 * Conversations & Messaging API Client
 *
 * Backend Controllers:
 *   - App\Http\Controllers\Api\Conversations\ListController
 *   - App\Http\Controllers\Api\Conversations\ShowController
 *   - App\Http\Controllers\Api\Conversations\ActionsController
 *   - App\Http\Controllers\Api\Conversations\SettingController
 *
 * Endpoints:
 *   - GET /api/conversations
 *   - POST /api/conversations
 *   - GET /api/conversations/{id}
 *   - POST /api/conversations/{id}/messages
 *   - POST /api/conversations/{id}/clear
 *   - DELETE /api/conversations/{id}
 */

import api from "@/utils/api";

export interface ConversationType {
  id: number;
  text1: string;
  text2: string;
  image_url: string;
}

export interface MessageType {
  id: number;
  sender_id: number;
  type: "text" | "image" | "product" | "drop" | "ad" | "profile";
  message?: string | null;
  image_url?: string | null;
  profile?: {
    id: number;
    image_url: string;
    text1: string;
    text2: string;
  } | null;
  product?: {
    id: string;
    image_url: string;
  } | null;
  drop?: {
    id: string;
    image_url: string;
  } | null;
  ad?: {
    id: string;
    image_url: string;
  } | null;
  created_at?: string;
}

export interface ConversationsListResponse {
  data: ConversationType[];
  next_page?: number | null;
}

export interface ConversationShowResponse {
  conversation: ConversationType;
  messages: MessageType[];
  next_page?: number | null;
}

export interface SendMessagePayload {
  type: "text" | "image" | "product" | "drop" | "ad" | "profile";
  message?: string;
  image_url?: string;
  product_id?: number | string;
  drop_id?: number | string;
  ad_id?: number | string;
  profile_id?: number | string;
}

/**
 * Fetch all conversations for the authenticated user.
 */
export const getConversationsApi = async (page = 1): Promise<ConversationsListResponse> => {
  const response = await api.get<ConversationsListResponse>("/conversations", {
    params: { page },
  });
  return response.data;
};

/**
 * Fetch single conversation details with messages.
 */
export const getConversationDetailsApi = async (
  conversationId: number | string,
  page = 1
): Promise<ConversationShowResponse> => {
  const response = await api.get<ConversationShowResponse>(`/conversations/${conversationId}`, {
    params: { page },
  });
  return response.data;
};

/**
 * Send a message inside a conversation.
 */
export const sendMessageApi = async (
  conversationId: number | string,
  payload: SendMessagePayload
): Promise<{ message: string; data: MessageType }> => {
  const response = await api.post(`/conversations/${conversationId}/messages`, payload);
  return response.data;
};

/**
 * Start a conversation with a specific user.
 */
export const startConversationApi = async (
  userId: number | string
): Promise<{ data: ConversationType }> => {
  const response = await api.post("/conversations", { user_id: userId });
  return response.data;
};

/**
 * Clear conversation history.
 */
export const clearConversationApi = async (
  conversationId: number | string
): Promise<{ message: string }> => {
  const response = await api.post(`/conversations/${conversationId}/clear`);
  return response.data;
};

/**
 * Delete a conversation.
 */
export const deleteConversationApi = async (
  conversationId: number | string
): Promise<{ message: string }> => {
  const response = await api.delete(`/conversations/${conversationId}`);
  return response.data;
};

export default getConversationsApi;
