import { getPool } from '../database/connection';
import { executeQuery, executeInsertAndSelect, executeUpdateAndSelect } from '../database/mysqlHelper';

export async function getSettingsFromDB(shopId: string) {
  const db = getPool();
  let result = await executeQuery(db, 'SELECT * FROM settings WHERE shop_id = ?', [shopId]);

  if (result.length === 0) {
    // Create default settings
    const insertQuery = `
      INSERT INTO settings (shop_id, week_start, locale, timezone)
      VALUES (?, 'sunday', 'en-US', 'UTC')
    `;
    const selectQuery = 'SELECT * FROM settings WHERE id = ?';
    result = await executeInsertAndSelect(db, insertQuery, selectQuery, [shopId]);
  }

  return result[0];
}

export async function updateSettingsInDB(shopId: string, settingsData: any) {
  const db = getPool();
  const updates: string[] = [];
  const params: any[] = [];

  if (settingsData.week_start) {
    updates.push('week_start = ?');
    params.push(settingsData.week_start);
  }
  if (settingsData.locale) {
    updates.push('locale = ?');
    params.push(settingsData.locale);
  }
  if (settingsData.timezone) {
    updates.push('timezone = ?');
    params.push(settingsData.timezone);
  }

  updates.push('updated_at = CURRENT_TIMESTAMP');
  params.push(shopId);

  // Check if settings exist
  const existing = await executeQuery(db, 'SELECT id FROM settings WHERE shop_id = ?', [shopId]);

  if (existing.length === 0) {
    const insertQuery = `
      INSERT INTO settings (shop_id, week_start, locale, timezone)
      VALUES (?, ?, ?, ?)
    `;
    const selectQuery = 'SELECT * FROM settings WHERE id = ?';
    const result = await executeInsertAndSelect(
      db,
      insertQuery,
      selectQuery,
      [
        shopId,
        settingsData.week_start || 'sunday',
        settingsData.locale || 'en-US',
        settingsData.timezone || 'UTC',
      ]
    );
    return result;
  } else {
    const updateQuery = `UPDATE settings SET ${updates.join(', ')} WHERE shop_id = ?`;
    const selectQuery = 'SELECT * FROM settings WHERE shop_id = ?';
    return await executeUpdateAndSelect(db, updateQuery, selectQuery, params, [shopId]);
  }
}

export async function getIntegrationsFromDB(shopId: string) {
  const db = getPool();
  return await executeQuery(db, 'SELECT * FROM integrations WHERE shop_id = ?', [shopId]);
}

export async function addIntegrationInDB(shopId: string, integrationData: any) {
  const db = getPool();
  const insertQuery = `
    INSERT INTO integrations (shop_id, type, config, enabled)
    VALUES (?, ?, ?, ?)
  `;
  const selectQuery = 'SELECT * FROM integrations WHERE id = ?';
  
  return await executeInsertAndSelect(
    db,
    insertQuery,
    selectQuery,
    [
      shopId,
      integrationData.type,
      JSON.stringify(integrationData.config || {}),
      integrationData.enabled !== false ? 1 : 0,
    ]
  );
}

export async function removeIntegrationFromDB(id: string, shopId: string) {
  const db = getPool();
  await executeQuery(db, 'DELETE FROM integrations WHERE id = ? AND shop_id = ?', [id, shopId]);
}
