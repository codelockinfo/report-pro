import { Router, Request, Response, NextFunction } from 'express';
import { getShopByDomain } from '../services/shopService';

/**
 * Installation route handler
 * This handles initial app access and checks if shop needs to be installed
 * Similar to the PHP index.php installation flow
 * 
 * GET /?shop=store-name.myshopify.com
 */
export function createInstallRouter() {
  const installRouter = Router();

  installRouter.get('/', async (req: Request, res: Response, next: NextFunction) => {
    try {
      const shop = req.query.shop as string;

      if (!shop) {
        // No shop parameter - show error or redirect to Shopify App Store
        return res.status(400).send(`
          <html>
            <head><title>Installation Required</title></head>
            <body>
              <h1>Shop Parameter Required</h1>
              <p>Please install this app through the Shopify App Store.</p>
              <p><a href="https://apps.shopify.com">Go to Shopify App Store</a></p>
            </body>
          </html>
        `);
      }

      // Normalize shop domain
      let shopDomain = shop.trim();
      shopDomain = shopDomain.replace(/^https?:\/\//, '');
      shopDomain = shopDomain.replace(/\/$/, '');
      shopDomain = shopDomain.toLowerCase();
      
      if (!shopDomain.includes('.')) {
        shopDomain = `${shopDomain}.myshopify.com`;
      } else if (!shopDomain.endsWith('.myshopify.com')) {
        return res.status(400).json({ error: 'Invalid shop domain format' });
      }

      // Check if this is an embedded app request (has hmac/host parameters)
      const isEmbeddedApp = !!(req.query.hmac && req.query.host);
      
      // Check if shop is already installed
      const shopData = await getShopByDomain(shopDomain);

      if (shopData && shopData.access_token) {
        // Shop is installed - let request continue to frontend serving
        // The embedded app auth middleware and frontend serving will handle it
        console.log('[INSTALL] Shop is installed, allowing request to continue:', shopDomain);
        return next();
      } else {
        console.log('[INSTALL] Shop not installed, redirecting to OAuth:', shopDomain);
        // Shop is not installed - redirect to OAuth
        const oauthUrl = `/api/auth/shopify?shop=${encodeURIComponent(shopDomain)}`;
        
        if (isEmbeddedApp) {
          // For embedded apps, we need to break out of iframe first
          return res.send(`
            <!DOCTYPE html>
            <html>
              <head>
                <script>
                  top.location.href = "${oauthUrl}";
                </script>
              </head>
              <body>Redirecting to installation...</body>
            </html>
          `);
        } else {
          // Direct redirect to OAuth
          return res.redirect(oauthUrl);
        }
      }
    } catch (error) {
      console.error('Install route error:', error);
      res.status(500).json({ error: 'Failed to process installation request' });
    }
  });

  return installRouter;
}

