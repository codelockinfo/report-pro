import { Router } from 'express';
import { 
  authenticateShopify, 
  callback, 
  verifyToken,
  registerShopData,
  initializeAuth 
} from '../controllers/authController';
import { shopifyApi } from '@shopify/shopify-api';

export function createAuthRouter(shopify: ReturnType<typeof shopifyApi>) {
  const authRouter = Router();
  
  // Initialize auth controller with shopify instance
  initializeAuth(shopify);

  // Shopify OAuth flow
  authRouter.get('/shopify', authenticateShopify);
  authRouter.get('/shopify/callback', callback);

  // Register shop data from frontend (before OAuth completes)
  authRouter.post('/register-shop', registerShopData);

  // Verify token
  authRouter.get('/verify', verifyToken);

  return authRouter;
}

