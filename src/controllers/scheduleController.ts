import { Response } from 'express';
import { AuthenticatedRequest } from '../middleware/shopifyAuth';
import {
  getSchedulesFromDB,
  getScheduleByIdFromDB,
  createScheduleInDB,
  updateScheduleInDB,
  deleteScheduleFromDB,
  toggleScheduleStatusInDB,
  getScheduleUsageFromDB
} from '../services/scheduleService';
import { addScheduleJob } from '../services/queue';

export const getSchedules = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { status, type, search } = req.query;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const schedules = await getSchedulesFromDB(shopId, {
      status: status as string,
      type: type as string,
      search: search as string,
    });

    res.json(schedules);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch schedules' });
  }
};

export const getScheduleUsage = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const usage = await getScheduleUsageFromDB(shopId);
    res.json(usage);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch schedule usage' });
  }
};

export const getScheduleById = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const schedule = await getScheduleByIdFromDB(id, shopId);
    if (!schedule) {
      return res.status(404).json({ error: 'Schedule not found' });
    }

    res.json(schedule);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch schedule' });
  }
};

export const createSchedule = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const scheduleData = req.body;

    const schedule = await createScheduleInDB(shopId, scheduleData);
    
    // Add to queue if enabled
    if (schedule.enabled) {
      await addScheduleJob(schedule);
    }

    res.status(201).json(schedule);
  } catch (error) {
    res.status(500).json({ error: 'Failed to create schedule' });
  }
};

export const updateSchedule = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const updateData = req.body;

    const schedule = await updateScheduleInDB(id, shopId, updateData);
    if (!schedule) {
      return res.status(404).json({ error: 'Schedule not found' });
    }

    // Update queue job
    if (schedule.enabled) {
      await addScheduleJob(schedule);
    }

    res.json(schedule);
  } catch (error) {
    res.status(500).json({ error: 'Failed to update schedule' });
  }
};

export const toggleScheduleStatus = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    const schedule = await toggleScheduleStatusInDB(id, shopId);
    if (!schedule) {
      return res.status(404).json({ error: 'Schedule not found' });
    }

    // Update queue job
    if (schedule.enabled) {
      await addScheduleJob(schedule);
    }

    res.json(schedule);
  } catch (error) {
    res.status(500).json({ error: 'Failed to toggle schedule status' });
  }
};

export const deleteSchedule = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    await deleteScheduleFromDB(id, shopId);
    res.status(204).send();
  } catch (error) {
    res.status(500).json({ error: 'Failed to delete schedule' });
  }
};

