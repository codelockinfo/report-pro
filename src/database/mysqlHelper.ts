import mysql from 'mysql2/promise';

/**
 * Helper function to execute MySQL queries with proper result handling
 */
export async function executeQuery(
  pool: mysql.Pool,
  query: string,
  params: any[] = []
): Promise<any[]> {
  const [rows] = await pool.execute(query, params);
  return rows as any[];
}

/**
 * Helper function to execute INSERT and get the inserted ID
 */
export async function executeInsert(
  pool: mysql.Pool,
  query: string,
  params: any[] = []
): Promise<number> {
  const [result]: any = await pool.execute(query, params);
  return result.insertId;
}

/**
 * Helper function to execute INSERT and return the inserted row
 */
export async function executeInsertAndSelect(
  pool: mysql.Pool,
  insertQuery: string,
  selectQuery: string,
  params: any[] = []
): Promise<any> {
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    
    const [result]: any = await connection.execute(insertQuery, params);
    const insertId = result.insertId;
    
    const [rows] = await connection.execute(selectQuery, [insertId]);
    await connection.commit();
    
    return (rows as any[])[0];
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}

/**
 * Helper function to execute UPDATE and return the updated row
 */
export async function executeUpdateAndSelect(
  pool: mysql.Pool,
  updateQuery: string,
  selectQuery: string,
  params: any[] = [],
  selectParams: any[] = []
): Promise<any | null> {
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    
    await connection.execute(updateQuery, params);
    
    // Use provided selectParams or extract from params (usually last two params are id and shop_id)
    const finalSelectParams = selectParams.length > 0 
      ? selectParams 
      : params.slice(-2); // Last two params (id, shop_id)
    
    const [rows] = await connection.execute(selectQuery, finalSelectParams);
    await connection.commit();
    
    return (rows as any[])[0] || null;
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}

/**
 * Convert PostgreSQL-style parameterized queries ($1, $2) to MySQL (?)
 */
export function convertQueryToMySQL(query: string): string {
  // Replace $1, $2, etc. with ?
  return query.replace(/\$\d+/g, '?');
}

