import { Request, Response } from 'express';
import { shopifyApi } from '@shopify/shopify-api';
import { saveShop, getShopByDomain, registerShop } from '../services/shopService';

// This will be initialized in server.ts and passed here
let shopify: ReturnType<typeof shopifyApi>;

export function initializeAuth(shopifyInstance: ReturnType<typeof shopifyApi>) {
  shopify = shopifyInstance;
}

/**
 * Initiate Shopify OAuth flow
 * GET /api/auth/shopify?shop=your-store.myshopify.com
 */
export const authenticateShopify = async (req: Request, res: Response) => {
  try {
    const shop = req.query.shop as string;

    if (!shop) {
      return res.status(400).json({ error: 'Shop parameter is required' });
    }

    // Validate shop domain format
    if (!shop.endsWith('.myshopify.com') && !shop.includes('.')) {
      return res.status(400).json({ error: 'Invalid shop domain format' });
    }

    const shopDomain = shop.includes('.myshopify.com') ? shop : `${shop}.myshopify.com`;

    // Create OAuth begin URL
    const authRoute = await shopify.auth.begin({
      shop: shopDomain,
      callbackPath: '/api/auth/shopify/callback',
      isOnline: false, // Offline access for background jobs
      rawRequest: req,
      rawResponse: res,
    });

    res.redirect(authRoute);
  } catch (error) {
    console.error('OAuth initiation error:', error);
    res.status(500).json({ error: 'Failed to initiate OAuth flow' });
  }
};

/**
 * Handle Shopify OAuth callback
 * GET /api/auth/shopify/callback?code=...&shop=...&hmac=...&timestamp=...
 */
export const callback = async (req: Request, res: Response) => {
  try {
    const { code, shop, hmac, timestamp } = req.query;

    if (!code || !shop || !hmac) {
      return res.status(400).json({ error: 'Missing required OAuth parameters' });
    }

    const shopDomain = shop as string;

    // Exchange code for access token (callback validates HMAC automatically)
    const callbackResponse = await shopify.auth.callback({
      rawRequest: req,
      rawResponse: res,
    });

    if (!callbackResponse || !callbackResponse.session || !callbackResponse.session.accessToken) {
      return res.status(500).json({ error: 'Failed to obtain access token' });
    }

    const accessToken = callbackResponse.session.accessToken;
    const grantedScopes = callbackResponse.session.scope || process.env.SHOPIFY_SCOPES || '';

    // Get shop information using the access token
    let storeName = shopDomain;
    try {
      const shopifySession = shopify.session.customAppSession(shopDomain);
      shopifySession.accessToken = accessToken;

      const client = new shopify.clients.Graphql({ session: shopifySession });
      const query = `{
        shop {
          name
          email
          myshopifyDomain
        }
      }`;
      const shopInfoResponse = await client.request(query);

      const shopInfo = (shopInfoResponse as any)?.data?.shop;
      storeName = shopInfo?.name || shopDomain;
    } catch (error) {
      console.warn('Failed to fetch shop info, using domain as name:', error);
      // Continue with shopDomain as store name
    }

    // Save shop data to database
    await saveShop({
      shop_domain: shopDomain,
      store_name: storeName,
      access_token: accessToken,
      scope: grantedScopes,
    });

    // Redirect to app (embedded app)
    const redirectUrl = shopify.config.isEmbeddedApp
      ? `https://${shopDomain}/admin/apps/${process.env.SHOPIFY_API_KEY}`
      : '/app';

    res.redirect(redirectUrl);
  } catch (error) {
    console.error('OAuth callback error:', error);
    res.status(500).json({ error: 'Failed to complete OAuth flow' });
  }
};

/**
 * Verify shop authentication
 */
export const verifyToken = async (req: Request, res: Response) => {
  try {
    const shopDomain = req.query.shop as string || req.headers['x-shop-domain'] as string;

    if (!shopDomain) {
      return res.status(400).json({ error: 'Shop domain is required' });
    }

    const shop = await getShopByDomain(shopDomain);
    
    if (!shop || !shop.access_token) {
      return res.status(401).json({ valid: false, error: 'Shop not authenticated' });
    }

    res.json({ 
      valid: true, 
      shop: {
        domain: shop.shop_domain,
        name: shop.store_name,
      }
    });
  } catch (error) {
    console.error('Token verification error:', error);
    res.status(500).json({ error: 'Failed to verify token' });
  }
};

/**
 * Register shop data from frontend
 * POST /api/auth/register-shop
 * Body: { shop_domain: string, store_name?: string }
 */
export const registerShopData = async (req: Request, res: Response) => {
  try {
    const { shop_domain, store_name } = req.body;

    if (!shop_domain) {
      return res.status(400).json({ error: 'Shop domain is required' });
    }

    // Validate and normalize shop domain
    let shopDomain = shop_domain.trim();
    if (!shopDomain.endsWith('.myshopify.com') && !shopDomain.includes('.')) {
      shopDomain = `${shopDomain}.myshopify.com`;
    }

    // Extract store name from domain if not provided
    let finalStoreName = store_name;
    if (!finalStoreName) {
      // Extract store name from domain (e.g., "mystore.myshopify.com" -> "mystore")
      const domainParts = shopDomain.split('.');
      finalStoreName = domainParts[0] || shopDomain;
    }

    // Register shop in database
    const shop = await registerShop({
      shop_domain: shopDomain,
      store_name: finalStoreName,
    });

    res.json({
      success: true,
      shop: {
        id: shop.id,
        domain: shop.shop_domain,
        name: shop.store_name,
        registered: true,
      },
    });
  } catch (error) {
    console.error('Shop registration error:', error);
    res.status(500).json({ error: 'Failed to register shop data' });
  }
};
