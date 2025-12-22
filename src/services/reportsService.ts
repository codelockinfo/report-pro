import { getPool } from '../database/connection';
import { executeQuery, executeInsertAndSelect, executeUpdateAndSelect } from '../database/mysqlHelper';

export async function getReportsFromDB(shopId: string, filters: any) {
  const db = getPool();
  let query = 'SELECT * FROM reports WHERE shop_id = ?';
  const params: any[] = [shopId];

  if (filters.category) {
    query += ' AND category = ?';
    params.push(filters.category);
  }

  if (filters.search) {
    query += ' AND name LIKE ?';
    params.push(`%${filters.search}%`);
  }

  query += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
  params.push(filters.limit || 50);
  params.push((filters.page - 1) * (filters.limit || 50));

  return await executeQuery(db, query, params);
}

export async function getReportByIdFromDB(id: string, shopId: string) {
  const db = getPool();
  const result = await executeQuery(
    db,
    'SELECT * FROM reports WHERE id = ? AND shop_id = ?',
    [id, shopId]
  );
  return result[0] || null;
}

export async function createReportInDB(shopId: string, reportData: any) {
  const db = getPool();
  const insertQuery = `
    INSERT INTO reports (shop_id, name, category, description, query_config, is_custom)
    VALUES (?, ?, ?, ?, ?, ?)
  `;
  const selectQuery = 'SELECT * FROM reports WHERE id = ?';
  
  return await executeInsertAndSelect(
    db,
    insertQuery,
    selectQuery,
    [
      shopId,
      reportData.name,
      reportData.category,
      reportData.description,
      JSON.stringify(reportData.query_config || {}),
      reportData.is_custom || true,
    ]
  );
}

export async function updateReportInDB(id: string, shopId: string, updateData: any) {
  const db = getPool();
  const updates: string[] = [];
  const params: any[] = [];

  if (updateData.name) {
    updates.push('name = ?');
    params.push(updateData.name);
  }
  if (updateData.category) {
    updates.push('category = ?');
    params.push(updateData.category);
  }
  if (updateData.description) {
    updates.push('description = ?');
    params.push(updateData.description);
  }
  if (updateData.query_config) {
    updates.push('query_config = ?');
    params.push(JSON.stringify(updateData.query_config));
  }

  updates.push('updated_at = CURRENT_TIMESTAMP');
  params.push(id, shopId);

  const updateQuery = `UPDATE reports SET ${updates.join(', ')} WHERE id = ? AND shop_id = ?`;
  const selectQuery = 'SELECT * FROM reports WHERE id = ? AND shop_id = ?';

  return await executeUpdateAndSelect(db, updateQuery, selectQuery, params);
}

export async function deleteReportFromDB(id: string, shopId: string) {
  const db = getPool();
  await executeQuery(db, 'DELETE FROM reports WHERE id = ? AND shop_id = ?', [id, shopId]);
}
