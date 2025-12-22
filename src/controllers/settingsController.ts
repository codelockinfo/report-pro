import { Response } from 'express';
import { AuthenticatedRequest } from '../middleware/shopifyAuth';
import {
  getSettingsFromDB,
  updateSettingsInDB,
  getIntegrationsFromDB,
  addIntegrationInDB,
  removeIntegrationFromDB
} from '../services/settingsService';

export const getSettings = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const settings = await getSettingsFromDB(shopId);
    res.json(settings);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch settings' });
  }
};

export const updateSettings = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const settingsData = req.body;

    const settings = await updateSettingsInDB(shopId, settingsData);
    res.json(settings);
  } catch (error) {
    res.status(500).json({ error: 'Failed to update settings' });
  }
};

export const getIntegrations = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const integrations = await getIntegrationsFromDB(shopId);
    res.json(integrations);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch integrations' });
  }
};

export const addIntegration = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;
    const integrationData = req.body;

    const integration = await addIntegrationInDB(shopId, integrationData);
    res.status(201).json(integration);
  } catch (error) {
    res.status(500).json({ error: 'Failed to add integration' });
  }
};

export const removeIntegration = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { id } = req.params;
    const shopId = req.shop?.id.toString() || req.headers['x-shop-id'] as string;

    await removeIntegrationFromDB(id, shopId);
    res.status(204).send();
  } catch (error) {
    res.status(500).json({ error: 'Failed to remove integration' });
  }
};

