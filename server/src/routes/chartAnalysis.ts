import { Router } from 'express';
import {
  getChartData,
  createChart,
  getCharts,
  getChartById,
  updateChart,
  deleteChart,
  exportChart
} from '../controllers/chartAnalysisController';

export const chartAnalysisRouter = Router();

// Get all charts
chartAnalysisRouter.get('/', getCharts);

// Get chart data for visualization
chartAnalysisRouter.post('/data', getChartData);

// Create new chart
chartAnalysisRouter.post('/', createChart);

// Get specific chart
chartAnalysisRouter.get('/:id', getChartById);

// Update chart
chartAnalysisRouter.put('/:id', updateChart);

// Delete chart
chartAnalysisRouter.delete('/:id', deleteChart);

// Export chart data
chartAnalysisRouter.get('/:id/export', exportChart);

