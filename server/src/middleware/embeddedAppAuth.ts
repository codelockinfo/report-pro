import { Request, Response, NextFunction } from 'express';
import { shopifyApi } from '@shopify/shopify-api';
import { getShopByDomain } from '../services/shopService';

/**
 * Middleware to handle embedded app authentication
 * This verifies the session when Shopify loads the embedded app
 * 
 * For embedded apps, Shopify sends requests with hmac, host, shop, and session parameters.
 * The frontend Shopify JS SDK handles most of the authentication, but we need to:
 * 1. Check if the shop is installed
 * 2. Serve the frontend HTML which will use the Shopify JS SDK for authentication
 * 
 * Note: The 403 error typically occurs when:
 * - The shop is not properly installed (missing access token)
 * - The session storage is not configured in the Shopify API instance
 * - The app URL doesn't match what's configured in Shopify Partners
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
      const hmac = req.query.hmac as string;
      const host = req.query.host as string;
      
      // If this is an embedded app request (has shop parameter)
      if (shop) {
        // Normalize shop domain
        const shopDomain = shop.includes('.myshopify.com') 
          ? shop 
          : `${shop}.myshopify.com`;

        // Check if shop is installed (has access token in database)
        const shopData = await getShopByDomain(shopDomain);
        
        if (!shopData || !shopData.access_token) {
          // Shop not installed - redirect to OAuth
          console.log('[EMBEDDED_AUTH] Shop not installed or missing token, redirecting to OAuth:', shopDomain);
          const authUrl = `/api/auth/shopify?shop=${encodeURIComponent(shopDomain)}`;
          
          // For embedded apps, we need to break out of iframe to redirect to OAuth
          if (hmac && host) {
            return res.send(`
              <!DOCTYPE html>
              <html>
                <head>
                  <script>
                    top.location.href = "${authUrl}";
                  </script>
                </head>
                <body>Redirecting to installation...</body>
              </html>
            `);
          }
          return res.redirect(authUrl);
        }

        // Shop is installed - allow request to proceed
        // The frontend Shopify JS SDK will handle the actual session validation
        // We just need to serve the HTML, and the SDK will authenticate using the session token
        console.log('[EMBEDDED_AUTH] Shop is installed, serving frontend:', shopDomain, {
          hasHmac: !!hmac,
          hasHost: !!host,
        });
        next();
      } else {
        // Not an embedded app request, allow through
        next();
      }
    } catch (error) {
      console.error('[EMBEDDED_AUTH] Error:', error);
      // On error, try to redirect to OAuth if we have shop info
      const shop = req.query.shop as string;
      if (shop) {
        const shopDomain = shop.includes('.myshopify.com') 
          ? shop 
          : `${shop}.myshopify.com`;
        const authUrl = `/api/auth/shopify?shop=${encodeURIComponent(shopDomain)}`;
        const hmac = req.query.hmac as string;
        const host = req.query.host as string;
        
        if (hmac && host) {
          return res.send(`
            <!DOCTYPE html>
            <html>
              <head>
                <script>
                  top.location.href = "${authUrl}";
                </script>
              </head>
              <body>Error occurred. Redirecting...</body>
            </html>
          `);
        }
        return res.redirect(authUrl);
      }
      // Otherwise, allow request through - let the frontend handle it
      next();
    }
  };
}

