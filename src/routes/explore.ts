import { Router } from 'express';
import { exploreData, getTableSchema, runQuery } from '../controllers/exploreController';

export const exploreRouter = Router();

// Explore data with filters
exploreRouter.post('/data', exploreData);

// Get table schema
exploreRouter.get('/schema/:table', getTableSchema);

// Run custom query (with safety checks)
exploreRouter.post('/query', runQuery);

