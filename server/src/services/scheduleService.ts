import { getPool } from '../database/connection';
import { executeQuery, executeInsertAndSelect, executeUpdateAndSelect } from '../database/mysqlHelper';

export async function getSchedulesFromDB(shopId: string, filters: any) {
  const db = getPool();
  let query = `
    SELECT s.*, r.name as report_name
    FROM schedules s
    JOIN reports r ON s.report_id = r.id
    WHERE s.shop_id = ?
  `;
  const params: any[] = [shopId];

  if (filters.status) {
    query += ' AND s.enabled = ?';
    params.push(filters.status === 'enabled' ? 1 : 0);
  }

  if (filters.type) {
    query += ' AND s.format = ?';
    params.push(filters.type);
  }

  if (filters.search) {
    query += ' AND (r.name LIKE ? OR CAST(s.recipients AS CHAR) LIKE ?)';
    const searchTerm = `%${filters.search}%`;
    params.push(searchTerm, searchTerm);
  }

  query += ' ORDER BY s.next_run_at ASC';

  return await executeQuery(db, query, params);
}

export async function getScheduleUsageFromDB(shopId: string) {
  const db = getPool();
  const result = await executeQuery(
    db,
    `SELECT 
      COUNT(*) as total_schedules,
      SUM(CASE WHEN enabled = 1 THEN 1 ELSE 0 END) as enabled_schedules,
      SUM(runs_count) as total_runs,
      SUM(CASE WHEN frequency = 'hourly' THEN 720 
               WHEN frequency = 'daily' THEN 30
               WHEN frequency = 'weekly' THEN 4
               WHEN frequency = 'monthly' THEN 1
               ELSE 0 END) as estimated_monthly_runs
     FROM schedules
     WHERE shop_id = ?`,
    [shopId]
  );
  return result[0];
}

export async function getScheduleByIdFromDB(id: string, shopId: string) {
  const db = getPool();
  const result = await executeQuery(
    db,
    `SELECT s.*, r.name as report_name
     FROM schedules s
     JOIN reports r ON s.report_id = r.id
     WHERE s.id = ? AND s.shop_id = ?`,
    [id, shopId]
  );
  return result[0] || null;
}

export async function createScheduleInDB(shopId: string, scheduleData: any) {
  const db = getPool();
  const insertQuery = `
    INSERT INTO schedules (shop_id, report_id, frequency, time_config, recipients, format, enabled, next_run_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `;
  const selectQuery = 'SELECT * FROM schedules WHERE id = ?';
  
  return await executeInsertAndSelect(
    db,
    insertQuery,
    selectQuery,
    [
      shopId,
      scheduleData.report_id,
      scheduleData.frequency,
      JSON.stringify(scheduleData.time_config || {}),
      JSON.stringify(scheduleData.recipients || []),
      scheduleData.format || 'csv',
      scheduleData.enabled !== false ? 1 : 0,
      calculateNextRun(scheduleData.frequency, scheduleData.time_config),
    ]
  );
}

export async function updateScheduleInDB(id: string, shopId: string, updateData: any) {
  const db = getPool();
  const updates: string[] = [];
  const params: any[] = [];

  if (updateData.frequency) {
    updates.push('frequency = ?');
    params.push(updateData.frequency);
  }
  if (updateData.time_config) {
    updates.push('time_config = ?');
    params.push(JSON.stringify(updateData.time_config));
  }
  if (updateData.recipients) {
    updates.push('recipients = ?');
    params.push(JSON.stringify(updateData.recipients));
  }
  if (updateData.format) {
    updates.push('format = ?');
    params.push(updateData.format);
  }

  updates.push('updated_at = CURRENT_TIMESTAMP');
  params.push(id, shopId);

  const updateQuery = `UPDATE schedules SET ${updates.join(', ')} WHERE id = ? AND shop_id = ?`;
  const selectQuery = 'SELECT * FROM schedules WHERE id = ? AND shop_id = ?';

  return await executeUpdateAndSelect(db, updateQuery, selectQuery, params);
}

export async function toggleScheduleStatusInDB(id: string, shopId: string) {
  const db = getPool();
  const updateQuery = `
    UPDATE schedules 
    SET enabled = NOT enabled, updated_at = CURRENT_TIMESTAMP
    WHERE id = ? AND shop_id = ?
  `;
  const selectQuery = 'SELECT * FROM schedules WHERE id = ? AND shop_id = ?';
  
  return await executeUpdateAndSelect(db, updateQuery, selectQuery, [id, shopId]);
}

export async function deleteScheduleFromDB(id: string, shopId: string) {
  const db = getPool();
  await executeQuery(db, 'DELETE FROM schedules WHERE id = ? AND shop_id = ?', [id, shopId]);
}

function calculateNextRun(frequency: string, timeConfig: any): Date {
  const now = new Date();
  const next = new Date(now);

  switch (frequency) {
    case 'hourly':
      next.setHours(next.getHours() + 1);
      next.setMinutes(timeConfig?.minute || 0);
      break;
    case 'daily':
      next.setDate(next.getDate() + 1);
      next.setHours(timeConfig?.hour || 0);
      next.setMinutes(timeConfig?.minute || 0);
      break;
    case 'weekly':
      const daysUntilNext = (timeConfig?.dayOfWeek || 0) - now.getDay();
      next.setDate(next.getDate() + (daysUntilNext <= 0 ? daysUntilNext + 7 : daysUntilNext));
      next.setHours(timeConfig?.hour || 0);
      break;
    case 'monthly':
      next.setMonth(next.getMonth() + 1);
      next.setDate(timeConfig?.day || 1);
      next.setHours(timeConfig?.hour || 0);
      break;
  }

  return next;
}
