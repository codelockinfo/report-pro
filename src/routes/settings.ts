import { Router } from 'express';
import {
  getSettings,
  updateSettings,
  getIntegrations,
  addIntegration,
  removeIntegration
} from '../controllers/settingsController';

export const settingsRouter = Router();

// Get all settings
settingsRouter.get('/', getSettings);

// Update settings
settingsRouter.put('/', updateSettings);

// Get integrations
settingsRouter.get('/integrations', getIntegrations);

// Add integration
settingsRouter.post('/integrations', addIntegration);

// Remove integration
settingsRouter.delete('/integrations/:id', removeIntegration);

