import { Express } from 'express';
import { shopifyApi } from '@shopify/shopify-api';

import { reportsRouter } from './reports';
import { exploreRouter } from './explore';
import { scheduleRouter } from './schedule';
import { settingsRouter } from './settings';
import { chartAnalysisRouter } from './chartAnalysis';
import { createAuthRouter } from './auth';
import { attachShopData } from '../middleware/shopifyAuth';

export function setupRoutes(app: Express, shopify: ReturnType<typeof shopifyApi>) {
  // Authentication routes (no shop auth required)
  app.use('/api/auth', createAuthRouter(shopify));

  // Main feature routes (require shop authentication)
  app.use('/api/reports', attachShopData, reportsRouter);
  app.use('/api/explore', attachShopData, exploreRouter);
  app.use('/api/schedule', attachShopData, scheduleRouter);
  app.use('/api/settings', attachShopData, settingsRouter);
  app.use('/api/chart-analysis', attachShopData, chartAnalysisRouter);
}

