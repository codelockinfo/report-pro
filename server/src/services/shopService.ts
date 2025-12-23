import { getPool } from '../database/connection';
import { executeQuery, executeInsertAndSelect } from '../database/mysqlHelper';

export interface ShopData {
  id?: number;
  shop_domain: string;
  store_name?: string;
  access_token?: string;
  scope?: string;
  created_at?: Date;
  updated_at?: Date;
}

export async function getShopByDomain(shopDomain: string): Promise<ShopData | null> {
  const db = getPool();
  const result = await executeQuery(
    db,
    'SELECT * FROM shops WHERE shop_domain = ?',
    [shopDomain]
  );
  return result[0] || null;
}

export async function getShopById(shopId: number): Promise<ShopData | null> {
  const db = getPool();
  const result = await executeQuery(
    db,
    'SELECT * FROM shops WHERE id = ?',
    [shopId]
  );
  return result[0] || null;
}

export async function saveShop(shopData: ShopData): Promise<ShopData> {
  const db = getPool();
  
  // Check if shop exists
  const existing = await getShopByDomain(shopData.shop_domain);
  
  if (existing) {
    // Update existing shop
    const updates: string[] = [];
    const params: any[] = [];
    
    if (shopData.access_token) {
      updates.push('access_token = ?');
      params.push(shopData.access_token);
    }
    if (shopData.scope) {
      updates.push('scope = ?');
      params.push(shopData.scope);
    }
    if (shopData.store_name) {
      updates.push('store_name = ?');
      params.push(shopData.store_name);
    }
    
    updates.push('updated_at = CURRENT_TIMESTAMP');
    params.push(shopData.shop_domain);
    
    const updateQuery = `UPDATE shops SET ${updates.join(', ')} WHERE shop_domain = ?`;
    const selectQuery = 'SELECT * FROM shops WHERE shop_domain = ?';
    
    const connection = await db.getConnection();
    try {
      await connection.beginTransaction();
      await connection.execute(updateQuery, params);
      const [rows]: any = await connection.execute(selectQuery, [shopData.shop_domain]);
      await connection.commit();
      return rows[0];
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  } else {
    // Create new shop
    // Build dynamic query based on available fields
    const fields: string[] = ['shop_domain', 'store_name'];
    const values: any[] = [shopData.shop_domain, shopData.store_name || shopData.shop_domain];
    const placeholders: string[] = ['?', '?'];
    
    if (shopData.access_token) {
      fields.push('access_token');
      values.push(shopData.access_token);
      placeholders.push('?');
    }
    
    if (shopData.scope) {
      fields.push('scope');
      values.push(shopData.scope);
      placeholders.push('?');
    }
    
    const insertQuery = `
      INSERT INTO shops (${fields.join(', ')})
      VALUES (${placeholders.join(', ')})
    `;
    const selectQuery = 'SELECT * FROM shops WHERE id = ?';
    
    return await executeInsertAndSelect(
      db,
      insertQuery,
      selectQuery,
      values
    );
  }
}

export async function deleteShop(shopDomain: string): Promise<void> {
  const db = getPool();
  await executeQuery(db, 'DELETE FROM shops WHERE shop_domain = ?', [shopDomain]);
}

/**
 * Register shop data from frontend (without access token)
 * Used when app is first accessed before OAuth completes
 */
export async function registerShop(shopData: { shop_domain: string; store_name?: string }): Promise<ShopData> {
  const db = getPool();
  
  // Check if shop exists
  const existing = await getShopByDomain(shopData.shop_domain);
  
  if (existing) {
    // Update store name if provided and different
    if (shopData.store_name && shopData.store_name !== existing.store_name) {
      const updateQuery = 'UPDATE shops SET store_name = ?, updated_at = CURRENT_TIMESTAMP WHERE shop_domain = ?';
      const selectQuery = 'SELECT * FROM shops WHERE shop_domain = ?';
      
      const connection = await db.getConnection();
      try {
        await connection.beginTransaction();
        await connection.execute(updateQuery, [shopData.store_name, shopData.shop_domain]);
        const [rows]: any = await connection.execute(selectQuery, [shopData.shop_domain]);
        await connection.commit();
        return rows[0];
      } catch (error) {
        await connection.rollback();
        throw error;
      } finally {
        connection.release();
      }
    }
    return existing;
  } else {
    // Create new shop record (without access token - will be added during OAuth)
    const insertQuery = `
      INSERT INTO shops (shop_domain, store_name)
      VALUES (?, ?)
    `;
    const selectQuery = 'SELECT * FROM shops WHERE id = ?';
    
    return await executeInsertAndSelect(
      db,
      insertQuery,
      selectQuery,
      [
        shopData.shop_domain,
        shopData.store_name || shopData.shop_domain,
      ]
    );
  }
}

