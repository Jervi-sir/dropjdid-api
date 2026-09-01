/**
 * My Account Contacts API Client & Types
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\MyAccount\UserContactController
 *
 * Endpoints:
 *   - GET    /api/my-account/contacts
 *   - POST   /api/my-account/contacts
 *   - PUT    /api/my-account/contacts/{id}
 *   - DELETE /api/my-account/contacts/{id}
 */

export interface UserContactType {
  id: number | string;
  platform: string;
  type?: string;
  value: string;
  url?: string;
  created_at?: string;
}

export interface CreateContactPayload {
  platform: string;
  value: string;
  url?: string;
  type?: string;
  user_id?: number;
}

export interface UpdateContactPayload {
  platform?: string;
  value?: string;
  url?: string;
  type?: string;
  user_id?: number;
}

export interface ContactListResponse {
  data: UserContactType[];
  total: number;
}

export interface ContactResponse {
  message: string;
  data: UserContactType;
}

export interface DeleteContactResponse {
  message: string;
  id: number | string;
}
