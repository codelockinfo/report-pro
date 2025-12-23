import { Response } from 'express';
import { AuthenticatedRequest } from '../middleware/shopifyAuth';
import {
  getChartDataFromDB,
  createChartInDB,
  getChartsFromDB,
  getChartByIdFromDB,
  updateChartInDB,
  deleteChartFromDB,
  generateChartData
} from '../services/chartAnalysisService';

export const getCharts = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const charts = await getChartsFromDB(shopId);
    res.json(charts);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch charts' });
  }
};

export const getChartData = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const { chartType, metrics, dimensions, filters, dateRange } = req.body;

    const data = await generateChartData(shopId, {
      chartType,
      metrics,
      dimensions,
      filters,
      dateRange,
    });

    res.json(data);
  } catch (error) {
    res.status(500).json({ error: 'Failed to generate chart data' });
  }
};

export const createChart = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const chartData = req.body;

    const chart = await createChartInDB(shopId, chartData);
    res.status(201).json(chart);
  } catch (error) {
    res.status(500).json({ error: 'Failed to create chart' });
  }
};

export const getChartById = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const chart = await getChartByIdFromDB(id, shopId);
    if (!chart) {
      return res.status(404).json({ error: 'Chart not found' });
    }

    res.json(chart);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch chart' });
  }
};

export const updateChart = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const updateData = req.body;

    const chart = await updateChartInDB(id, shopId, updateData);
    if (!chart) {
      return res.status(404).json({ error: 'Report not found' });
    }

    res.json(chart);
  } catch (error) {
    res.status(500).json({ error: 'Failed to update chart' });
  }
};

export const deleteChart = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    await deleteChartFromDB(id, shopId);
    res.status(204).send();
  } catch (error) {
    res.status(500).json({ error: 'Failed to delete chart' });
  }
};

export const exportChart = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { format = 'csv' } = req.query;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const chart = await getChartByIdFromDB(id, shopId);
    if (!chart) {
      return res.status(404).json({ error: 'Chart not found' });
    }

    const data = await getChartDataFromDB(chart.id, shopId);
    
    if (format === 'csv') {
      res.setHeader('Content-Type', 'text/csv');
      res.setHeader('Content-Disposition', `attachment; filename="chart-${id}.csv"`);
      // CSV export logic here
      res.send('CSV data');
    } else {
      res.json(data);
    }
  } catch (error) {
    res.status(500).json({ error: 'Failed to export chart' });
  }
};

