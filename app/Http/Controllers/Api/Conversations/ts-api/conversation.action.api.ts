// @ts-nocheck
/**
 * Conversation Actions & Details API Client & Documentation
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Conversations\ActionsController
 *
 * Endpoints:
 *   - GET  /api/conversations/{id}           (Show Conversation Details & Messages)
 *   - POST /api/conversations                (Start / Retrieve Conversation)
 *   - POST /api/conversations/{id}/messages  (Send Message)
 */

import api from "@/utils/api";

export type MessageContentType = "text" | "image" | "product" | "drop" | "ad" | "profile";

export interface ConversationType {
  /** Unique conversation identifier */
  id: number;
  /** Recipient User / Profile ID */
  user_id?: number | null;
  /** Avatar / Image URL of the recipient */
  image_url: string;
  /** Recipient display title (e.g., Name / Username) */
  text1: string;
  /** Snippet / summary of the latest message or status */
  text2: string;
  /** Whether the current user has unread messages in this conversation */
  has_unread_messages?: boolean | null;
  /** Whether this conversation was recently restored / reset */
  was_reset?: boolean;
}

export interface AttachedProfileType {
  id: number;
  image_url: string;
  text1: string;
  text2: string;
}

export interface AttachedItemType {
  id: number | string;
  image_url: string;
  title?: string;
  price?: number | string;
}

export interface MessageType {
  /** Unique message identifier */
  id: number;
  /** Sender user ID */
  sender_id: number;
  /** Message content type */
  type: MessageContentType;
  /** Text content of the message */
  message?: string | null;
  /** Image URL when type is 'image' */
  image_url?: string | null;
  /** Attached profile data when type is 'profile' */
  profile?: AttachedProfileType | null;
  /** Attached product data when type is 'product' */
  product?: AttachedItemType | null;
  /** Attached drop data when type is 'drop' */
  drop?: AttachedItemType | null;
  /** Attached ad data when type is 'ad' */
  ad?: AttachedItemType | null;
  /** Message creation timestamp */
  created_at?: string;
}

export interface ShowConversationResponse {
  /** Conversation meta info */
  conversation: ConversationType;
  /** Chronological messages in conversation */
  messages: MessageType[];
  /** Next page number if more historical messages exist */
  next_page?: number | null;
  /** Whether this conversation was recently restored / reset */
  was_reset?: boolean;
}

export interface StartConversationParams {
  /** Target user ID to start conversation with */
  user_id: number | string;
}

export interface StartConversationResponse {
  /** The created or existing conversation instance */
  data: ConversationType;
  conversation?: ConversationType;
  /** Whether this conversation was recently restored / reset */
  was_reset?: boolean;
}

export interface SendMessagePayload {
  /** Message content type */
  type: MessageContentType;
  /** Optional text message content */
  message?: string;
  /** Optional image URL when type is 'image' */
  image_url?: string;
  /** Product ID when type is 'product' */
  product_id?: number | string;
  /** Drop ID when type is 'drop' */
  drop_id?: number | string;
  /** Advertisement ID when type is 'ad' */
  ad_id?: number | string;
  /** Profile/User ID when type is 'profile' */
  profile_id?: number | string;
}

export interface SendMessageResponse {
  /** Success feedback message */
  message: string;
  /** The newly created message */
  data: MessageType;
}

/**
 * Fetch details of a single conversation along with its paginated message history.
 *
 * @param conversationId - Conversation unique ID
 * @param page - Page number for message history (default: 1)
 * @param perPage - Number of messages per page (default: 30)
 * @returns Promise<ShowConversationResponse>
 *
 * @example
 * ```ts
 * const res = await getConversationDetailsApi(12);
 * console.log(res.conversation.text1);
 * console.log(res.messages);
 * ```
 */
export const getConversationDetailsApi = async (
  conversationId: number | string,
  page = 1,
  perPage = 30
): Promise<ShowConversationResponse> => {
  const response = await api.get<ShowConversationResponse>(`/conversations/${conversationId}`, {
    params: { page, per_page: perPage },
  });
  return response.data;
};

/**
 * Start a new conversation or retrieve an existing private conversation with a user.
 *
 * @param params - Parameters containing the target `user_id` or user ID directly
 * @returns Promise<StartConversationResponse>
 *
 * @example
 * ```ts
 * const res = await startConversationApi({ user_id: profile.id });
 * console.log(res.data.id); // Conversation ID
 * ```
 */
export const startConversationApi = async (
  params: StartConversationParams | number | string
): Promise<StartConversationResponse> => {
  const payload = typeof params === "object" ? params : { user_id: params };
  const response = await api.post<StartConversationResponse>("/conversations", payload);
  return response.data;
};

export interface ShareToUserPayload {
  /** Target user / friend ID */
  user_id: number | string;
  /** Message content type */
  type: MessageContentType;
  /** Optional text message content */
  message?: string;
  /** Optional image URL when type is 'image' */
  image_url?: string;
  /** Optional image file when uploading an image */
  image?: { uri: string; name?: string; type?: string };
  /** Product ID when type is 'product' */
  product_id?: number | string;
  /** Drop ID when type is 'drop' */
  drop_id?: number | string;
  /** Advertisement ID when type is 'ad' */
  ad_id?: number | string;
  /** Profile/User ID when type is 'profile' */
  profile_id?: number | string;
  /** Optional channel (e.g., 'app') */
  channel?: string;
}

export interface ShareToUserResponse {
  message: string;
  conversation_id: number;
  data: MessageType;
  conversation: ConversationType;
}

/**
 * Send a message inside an existing conversation.
 *
 * Supports text, images, and attachments (products, drops, ads, profiles).
 *
 * @param conversationId - Conversation unique ID
 * @param payload - Message payload
 * @returns Promise<SendMessageResponse>
 */
export const sendMessageApi = async (
  conversationId: number | string,
  payload: SendMessagePayload
): Promise<SendMessageResponse> => {
  const response = await api.post<SendMessageResponse>(
    `/conversations/${conversationId}/messages`,
    payload
  );
  return response.data;
};

/**
 * Share an item (product, drop, ad, profile) or message directly to a target user.
 * Automatically creates/finds the conversation and records the share interaction.
 *
 * @param payload - Share payload containing user_id and resource info
 * @returns Promise<ShareToUserResponse>
 *
 * @example
 * ```ts
 * // Share a product to friend
 * const res = await shareToUserApi({
 *   user_id: friend.id,
 *   type: "product",
 *   product_id: 12,
 *   message: "Check this out!",
 * });
 * ```
 */
export const shareToUserApi = async (
  payload: ShareToUserPayload
): Promise<ShareToUserResponse> => {
  if (payload.image) {
    const formData = new FormData();
    formData.append("user_id", String(payload.user_id));
    formData.append("type", payload.type);
    if (payload.message) formData.append("message", payload.message);
    if (payload.channel) formData.append("channel", payload.channel);

    const uri = payload.image.uri;
    const uriParts = uri.split(".");
    const fileType = uriParts[uriParts.length - 1];

    formData.append("image", {
      uri,
      name: payload.image.name || `photo_${Date.now()}.${fileType || "jpg"}`,
      type:
        payload.image.type ||
        `image/${fileType === "png" ? "png" : "jpeg"}`,
    } as any);

    const response = await api.post<ShareToUserResponse>(
      "/conversations/share",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      }
    );
    return response.data;
  }

  const response = await api.post<ShareToUserResponse>(
    "/conversations/share",
    payload
  );
  return response.data;
};

/**
 * Clear all messages in a conversation.
 *
 * @param id - Conversation ID
 * @returns Promise<{ message: string }>
 */
export const clearConversationApi = async (
  id: number | string
): Promise<{ message: string }> => {
  const response = await api.post<{ message: string }>(`/conversations/${id}/clear`);
  return response.data;
};

/**
 * Delete a conversation.
 *
 * @param id - Conversation ID
 * @returns Promise<{ message: string }>
 */
export const deleteConversationApi = async (
  id: number | string
): Promise<{ message: string }> => {
  const response = await api.delete<{ message: string }>(`/conversations/${id}`);
  return response.data;
};

export default {
  getConversationDetailsApi,
  startConversationApi,
  sendMessageApi,
  shareToUserApi,
  clearConversationApi,
  deleteConversationApi,
};
