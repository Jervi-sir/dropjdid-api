// @ts-nocheck
/**
 * Delivery Catalog API Client & Types
 *
 * Backend Controller:
 *   - App\Http\Controllers\Api\Catalogs\DeliveryCatalogController
 *
 * Endpoints:
 *   - GET /api/catalogs/delivery/wilayas
 *   - GET /api/catalogs/delivery/options/{wilayaId?}
 *   - GET /api/catalogs/delivery/communes/{wilayaId?}
 *   - GET /api/catalogs/delivery/companies
 */

import api from "@/utils/api";

export interface CommuneCatalogItem {
  id: number;
  wilaya_id: number;
  wilaya_number?: string;
  wilaya_name?: string;
  code: string;
  post_code?: string;
  name: string;
  en: string;
  fr: string;
  ar: string;
  has_stopdesk?: boolean;
  stopdesk_address?: string | null;
}

export interface WilayaCatalogItem {
  id: number;
  number: string;
  code: string;
  name: string;
  en: string;
  fr: string;
  ar: string;
  communes?: CommuneCatalogItem[];
}

export interface DeliveryCompanyCatalogItem {
  id: number | null;
  code: string;
  name: string;
  logo_url: string | null;
  phone?: string | null;
  website?: string | null;
  is_active?: boolean;
}

export interface DeliveryMethodItem {
  code: "domicile" | "stopdesk" | string;
  name: string;
  en: string;
  fr: string;
  ar: string;
  description: string;
}

export interface DeliveryOptionItem {
  id?: number | null;
  store_id?: number | null;
  wilaya_id: number | null;
  wilaya_number: string;
  wilaya_name: string;
  delivery_company: DeliveryCompanyCatalogItem;
  has_home_delivery: boolean;
  has_stopdesk_delivery: boolean;
  cost_domicile: number;
  cost_stopdesk: number;
  cost_cancel: number;
  currency: string;
}

export interface WilayasResponse {
  success: boolean;
  count: number;
  data: WilayaCatalogItem[];
}

export interface DeliveryOptionsResponse {
  success: boolean;
  wilaya: WilayaCatalogItem | null;
  delivery_methods: DeliveryMethodItem[];
  options: DeliveryOptionItem[];
}

export interface CommunesResponse {
  success: boolean;
  wilaya: WilayaCatalogItem | null;
  is_stopdesk: boolean;
  count: number;
  data: CommuneCatalogItem[];
}

export interface DeliveryCompaniesResponse {
  success: boolean;
  count: number;
  data: DeliveryCompanyCatalogItem[];
}

/**
 * 1. Fetch all Wilayas list (optionally with nested communes)
 *
 * @param params { with_communes?: boolean }
 * @returns Promise<WilayasResponse>
 *
 * @example
 * ```ts
 * const res = await getWilayasApi({ with_communes: true });
 * console.log(res.data); // [{ id: 16, number: "16", en: "Algiers", ... }]
 * ```
 */
export const getWilayasApi = async (params?: {
  with_communes?: boolean;
}): Promise<WilayasResponse> => {
  const response = await api.get<WilayasResponse>("/catalogs/delivery/wilayas", {
    params,
  });
  return response.data;
};

/**
 * 2. Fetch available delivery providers and pricing (home & stopdesk) for a wilaya
 *
 * @param params { wilaya_id?: number | string; store_id?: number; product_id?: number }
 * @returns Promise<DeliveryOptionsResponse>
 *
 * @example
 * ```ts
 * const res = await getDeliveryOptionsApi({ wilaya_id: 16, store_id: 1 });
 * console.log(res.options[0].cost_domicile, res.options[0].cost_stopdesk);
 * ```
 */
export const getDeliveryOptionsApi = async (params?: {
  wilaya_id?: number | string;
  wilaya?: number | string;
  store_id?: number;
  product_id?: number;
}): Promise<DeliveryOptionsResponse> => {
  const wilayaId = params?.wilaya_id ?? params?.wilaya;
  const endpoint = wilayaId
    ? `/catalogs/delivery/options/${wilayaId}`
    : "/catalogs/delivery/options";

  const response = await api.get<DeliveryOptionsResponse>(endpoint, {
    params: {
      store_id: params?.store_id,
      product_id: params?.product_id,
    },
  });
  return response.data;
};

/**
 * 3. Fetch Communes (Baladiyas) for a Wilaya / Stopdesk pickup agencies
 *
 * @param params { wilaya_id?: number | string; is_stopdesk?: boolean; search?: string }
 * @returns Promise<CommunesResponse>
 *
 * @example
 * ```ts
 * const res = await getDeliveryCommunesApi({ wilaya_id: 16, is_stopdesk: true });
 * console.log(res.data); // [{ id: 254, name: "Alger Centre", stopdesk_address: "..." }]
 * ```
 */
export const getDeliveryCommunesApi = async (params?: {
  wilaya_id?: number | string;
  wilaya?: number | string;
  is_stopdesk?: boolean;
  type?: "home" | "stopdesk" | string;
  search?: string;
  q?: string;
}): Promise<CommunesResponse> => {
  const wilayaId = params?.wilaya_id ?? params?.wilaya;
  const endpoint = wilayaId
    ? `/catalogs/delivery/communes/${wilayaId}`
    : "/catalogs/delivery/communes";

  const response = await api.get<CommunesResponse>(endpoint, {
    params: {
      is_stopdesk: params?.is_stopdesk,
      type: params?.type,
      search: params?.search ?? params?.q,
    },
  });
  return response.data;
};

/**
 * 4. Fetch all active delivery companies (Swift Express, Yalidine, ZR Express, etc.)
 *
 * @returns Promise<DeliveryCompaniesResponse>
 *
 * @example
 * ```ts
 * const res = await getDeliveryCompaniesApi();
 * console.log(res.data); // [{ code: "swift_express", name: "Swift Express", ... }]
 * ```
 */
export const getDeliveryCompaniesApi = async (): Promise<DeliveryCompaniesResponse> => {
  const response = await api.get<DeliveryCompaniesResponse>(
    "/catalogs/delivery/companies"
  );
  return response.data;
};

export default {
  getWilayasApi,
  getDeliveryOptionsApi,
  getDeliveryCommunesApi,
  getDeliveryCompaniesApi,
};
