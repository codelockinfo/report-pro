import { getPool } from '../database/connection';
import { executeQuery } from '../database/mysqlHelper';

export async function exploreDataFromDB(shopId: string, table: string, filters: any, limit: number) {
  const db = getPool();
  
  // Validate table name to prevent SQL injection
  const allowedTables = ['orders', 'products', 'customers', 'transactions', 'agreement_lines'];
  if (!allowedTables.includes(table)) {
    throw new Error('Invalid table name');
  }

  let query = `SELECT * FROM ${table} WHERE shop_id = ?`;
  const params: any[] = [shopId];

  // Apply filters
  if (filters) {
    for (const [key, value] of Object.entries(filters)) {
      query += ` AND ${key} = ?`;
      params.push(value);
    }
  }

  query += ' LIMIT ?';
  params.push(limit);

  return await executeQuery(db, query, params);
}

export async function getTableSchemaFromDB(table: string) {
  const db = getPool();
  
  // Validate table name
  const allowedTables = ['orders', 'products', 'customers', 'transactions', 'agreement_lines'];
  if (!allowedTables.includes(table)) {
    throw new Error('Invalid table name');
  }

  // MySQL uses different information_schema structure
  const result = await executeQuery(
    db,
    `SELECT COLUMN_NAME as column_name, DATA_TYPE as data_type, IS_NULLABLE as is_nullable
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
     ORDER BY ORDINAL_POSITION`,
    [table]
  );
  return result;
}

export async function runSafeQuery(shopId: string, query: string) {
  const db = getPool();
  
  // Additional safety checks
  const dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE'];
  const upperQuery = query.toUpperCase();
  
  for (const keyword of dangerousKeywords) {
    if (upperQuery.includes(keyword)) {
      throw new Error(`Query contains dangerous keyword: ${keyword}`);
    }
  }

  // Ensure query includes shop_id filter
  if (!upperQuery.includes('SHOP_ID')) {
    throw new Error('Query must include shop_id filter');
  }

  // Replace ?shopId placeholder with actual shop_id (MySQL uses ?)
  const safeQuery = query.replace(/\?shopId/g, shopId).replace(/\$shopId/g, shopId);

  return await executeQuery(db, safeQuery, [shopId]);
}
