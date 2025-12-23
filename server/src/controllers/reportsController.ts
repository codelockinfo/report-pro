import { Request, Response } from 'express';
import { getReportsFromDB, getReportByIdFromDB, createReportInDB, updateReportInDB, deleteReportFromDB } from '../services/reportsService';
import { cacheGet, cacheSet } from '../services/redis';

import { AuthenticatedRequest } from '../middleware/shopifyAuth';

export const getReports = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { category, search, page = 1, limit = 50 } = req.query;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const cacheKey = `reports:${shopId}:${category}:${search}:${page}:${limit}`;
    const cached = await cacheGet(cacheKey);

    if (cached) {
      return res.json(JSON.parse(cached));
    }

    const reports = await getReportsFromDB(shopId, {
      category: category as string,
      search: search as string,
      page: Number(page),
      limit: Number(limit),
    });

    await cacheSet(cacheKey, JSON.stringify(reports), 300); // 5 min cache
    res.json(reports);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch reports' });
  }
};

export const getReportById = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const report = await getReportByIdFromDB(id, shopId);
    if (!report) {
      return res.status(404).json({ error: 'Report not found' });
    }

    res.json(report);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch report' });
  }
};

export const createCustomReport = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const reportData = req.body;

    const report = await createReportInDB(shopId, reportData);
    res.status(201).json(report);
  } catch (error) {
    res.status(500).json({ error: 'Failed to create report' });
  }
};

export const updateReport = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const updateData = req.body;

    const report = await updateReportInDB(id, shopId, updateData);
    if (!report) {
      return res.status(404).json({ error: 'Report not found' });
    }

    res.json(report);
  } catch (error) {
    res.status(500).json({ error: 'Failed to update report' });
  }
};

export const deleteReport = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    await deleteReportFromDB(id, shopId);
    res.status(204).send();
  } catch (error) {
    res.status(500).json({ error: 'Failed to delete report' });
  }
};

