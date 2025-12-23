import { Router } from 'express';
import {
  getSchedules,
  getScheduleById,
  createSchedule,
  updateSchedule,
  deleteSchedule,
  toggleScheduleStatus,
  getScheduleUsage
} from '../controllers/scheduleController';

export const scheduleRouter = Router();

// Get all schedules
scheduleRouter.get('/', getSchedules);

// Get schedule usage stats
scheduleRouter.get('/usage', getScheduleUsage);

// Get specific schedule
scheduleRouter.get('/:id', getScheduleById);

// Create schedule
scheduleRouter.post('/', createSchedule);

// Update schedule
scheduleRouter.put('/:id', updateSchedule);

// Toggle schedule status (enable/disable)
scheduleRouter.patch('/:id/toggle', toggleScheduleStatus);

// Delete schedule
scheduleRouter.delete('/:id', deleteSchedule);

