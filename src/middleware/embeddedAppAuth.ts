import { Request, Response, NextFunction } from 'express';
import { shopifyApi } from '@shopify/shopify-api';
import { getShopByDomain } from '../services/shopService';
import { sessionStorage } from '../services/sessionStorage';

/**
 * Middleware to handle embedded app authentication
 * This verifies the session when Shopify loads the embedded app
 */
export function createEmbeddedAppAuth(shopify: ReturnType<typeof shopifyApi>) {
  return async (req: Request, res: Response, next: NextFunction) => {
    try {
      // Skip for API routes and auth routes
      if (req.path.startsWith('/api')) {
        return next();
      }

      // For embedded apps, Shopify sends shop parameter in query string
      const shop = req.query.shop as string;
      
      if (!shop) {
        // Not an embedded app request, allow through
        return next();
      }

      // Normalize shop domain
      const shopDomain = shop.includes('.myshopify.com') 
        ? shop 
        : `${shop}.myshopify.com`;

      // Check if shop is installed (has access token)
      const shopData = await getShopByDomain(shopDomain);
      
      if (!shopData || !shopData.access_token) {
        // Shop not installed - redirect to OAuth
        const authUrl = `/api/auth/shopify?shop=${encodeURIComponent(shopDomain)}`;
        return res.redirect(authUrl);
      }

      // Shop is installed, allow request to proceed
      // The frontend will handle the embedded app initialization
      next();
    } catch (error) {
      console.error('Embedded app auth error:', error);
      // On error, allow request through - let the frontend handle it
      next();
    }
  };
}

