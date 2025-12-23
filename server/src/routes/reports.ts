import { Router } from 'express';
import { getReports, getReportById, createCustomReport, updateReport, deleteReport } from '../controllers/reportsController';

export const reportsRouter = Router();

// Get all reports (with filtering and pagination)
reportsRouter.get('/', getReports);

// Get specific report by ID
reportsRouter.get('/:id', getReportById);

// Create custom report
reportsRouter.post('/custom', createCustomReport);

// Update report
reportsRouter.put('/:id', updateReport);

// Delete report
reportsRouter.delete('/:id', deleteReport);

