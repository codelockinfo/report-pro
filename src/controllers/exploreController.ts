import { Request, Response } from 'express';
import { AuthenticatedRequest } from '../middleware/shopifyAuth';
import { exploreDataFromDB, getTableSchemaFromDB, runSafeQuery } from '../services/exploreService';

export const exploreData = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const { table, filters, limit = 100 } = req.body;

    const data = await exploreDataFromDB(shopId, table, filters, Number(limit));
    res.json(data);
  } catch (error) {
    res.status(500).json({ error: 'Failed to explore data' });
  }
};

export const getTableSchema = async (req: Request, res: Response) => {
  try {
    const table = (req.params as { table: string }).table;
    const schema = await getTableSchemaFromDB(table);
    res.json(schema);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch table schema' });
  }
};

export const runQuery = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const { query } = req.body;

    // Only allow SELECT queries for safety
    if (!query.trim().toUpperCase().startsWith('SELECT')) {
      return res.status(400).json({ error: 'Only SELECT queries are allowed' });
    }

    const result = await runSafeQuery(shopId, query);
    res.json(result);
  } catch (error) {
    res.status(500).json({ error: 'Failed to run query' });
  }
};

