import { Request, Response, NextFunction } from 'express';
import { getShopByDomain } from '../services/shopService';

export interface AuthenticatedRequest extends Request {
  shop?: {
    id: number;
    shop_domain: string;
    store_name?: string;
    access_token: string;
    scope: string;
  };
}

/**
 * Middleware to extract shop domain from request and attach shop data
 */
export async function attachShopData(req: AuthenticatedRequest, res: Response, next: NextFunction) {
  try {
    // Get shop domain from query, headers, or session
    const shopDomain = 
      req.query.shop as string ||
      req.headers['x-shop-domain'] as string ||
      req.body.shop;

    if (!shopDomain) {
      return res.status(400).json({ error: 'Shop domain is required' });
    }

    // Get shop data from database
    const shop = await getShopByDomain(shopDomain);
    
    if (!shop) {
      return res.status(404).json({ error: 'Shop not found. Please install the app first.' });
    }

    if (!shop.access_token) {
      return res.status(401).json({ error: 'Shop not authenticated. Please reinstall the app.' });
    }

    // Attach shop data to request
    req.shop = {
      id: shop.id!,
      shop_domain: shop.shop_domain,
      store_name: shop.store_name,
      access_token: shop.access_token,
      scope: shop.scope,
    };

    // Also set in headers for convenience
    req.headers['x-shop-id'] = shop.id!.toString();

    next();
  } catch (error) {
    console.error('Error attaching shop data:', error);
    res.status(500).json({ error: 'Failed to authenticate shop' });
  }
}

/**
 * Middleware to get shop ID from request (for routes that don't need full shop data)
 */
export function getShopId(req: Request): string | null {
  return (
    (req as AuthenticatedRequest).shop?.id.toString() ||
    req.headers['x-shop-id'] as string ||
    req.query.shop_id as string ||
    null
  );
}

