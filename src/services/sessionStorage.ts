import { Session } from '@shopify/shopify-api';
import { getPool } from '../database/connection';

/**
 * Custom session storage adapter for Shopify API using MySQL
 * This implements the SessionStorage interface required by Shopify API v9+
 * 
 * For embedded apps, Shopify needs to verify sessions when loading the app.
 * This storage adapter provides the session persistence required for that verification.
 */
export class MySQLSessionStorage {
  /**
   * Store a session
   */
  async storeSession(session: Session): Promise<boolean> {
    try {
      const db = getPool();
      
      // Serialize session data - store individual fields for querying and full JSON for reconstruction
      const sessionData = {
        id: session.id,
        shop: session.shop,
        state: session.state,
        isOnline: session.isOnline,
        scope: session.scope,
        expires: session.expires?.toISOString() || null,
        accessToken: session.accessToken,
        userId: (session as any).userId || null,
      };
      
      const serializedData = JSON.stringify(sessionData);
      
      // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and existing sessions
      const query = `
        INSERT INTO shopify_sessions (id, shop, state, is_online, scope, expires, access_token, user_id, session_data)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          shop = VALUES(shop),
          state = VALUES(state),
          is_online = VALUES(is_online),
          scope = VALUES(scope),
          expires = VALUES(expires),
          access_token = VALUES(access_token),
          user_id = VALUES(user_id),
          session_data = VALUES(session_data),
          updated_at = CURRENT_TIMESTAMP
      `;
      
      await db.execute(query, [
        session.id,
        session.shop,
        session.state || null,
        session.isOnline ? 1 : 0,
        session.scope || null,
        session.expires ? new Date(session.expires) : null,
        session.accessToken || null,
        (session as any).userId || null,
        serializedData,
      ]);
      
      return true;
    } catch (error) {
      console.error('Error storing session:', error);
      return false;
    }
  }

  /**
   * Load a session by ID
   */
  async loadSession(id: string): Promise<Session | undefined> {
    try {
      const db = getPool();
      const [rows]: any = await db.execute(
        'SELECT session_data FROM shopify_sessions WHERE id = ?',
        [id]
      );
      
      if (rows.length === 0) {
        return undefined;
      }
      
      const sessionData = JSON.parse(rows[0].session_data);
      
      // Reconstruct Session object
      const session = new Session({
        id: sessionData.id,
        shop: sessionData.shop,
        state: sessionData.state,
        isOnline: sessionData.isOnline || false,
        scope: sessionData.scope,
        expires: sessionData.expires ? new Date(sessionData.expires) : undefined,
        accessToken: sessionData.accessToken,
      });
      
      if (sessionData.userId) {
        (session as any).userId = sessionData.userId;
      }
      
      return session;
    } catch (error) {
      console.error('Error loading session:', error);
      return undefined;
    }
  }

  /**
   * Delete a session
   */
  async deleteSession(id: string): Promise<boolean> {
    try {
      const db = getPool();
      await db.execute('DELETE FROM shopify_sessions WHERE id = ?', [id]);
      return true;
    } catch (error) {
      console.error('Error deleting session:', error);
      return false;
    }
  }

  /**
   * Delete all sessions for a shop (optional method)
   */
  async deleteSessions(shop: string): Promise<boolean> {
    try {
      const db = getPool();
      await db.execute('DELETE FROM shopify_sessions WHERE shop = ?', [shop]);
      return true;
    } catch (error) {
      console.error('Error deleting sessions:', error);
      return false;
    }
  }

  /**
   * Find sessions by shop (optional method)
   */
  async findSessionsByShop(shop: string): Promise<Session[]> {
    try {
      const db = getPool();
      const [rows]: any = await db.execute(
        'SELECT session_data FROM shopify_sessions WHERE shop = ?',
        [shop]
      );
      
      return rows.map((row: any) => {
        const sessionData = JSON.parse(row.session_data);
        return new Session({
          id: sessionData.id,
          shop: sessionData.shop,
          state: sessionData.state,
          isOnline: sessionData.isOnline || false,
          scope: sessionData.scope,
          expires: sessionData.expires ? new Date(sessionData.expires) : undefined,
          accessToken: sessionData.accessToken,
        });
      });
    } catch (error) {
      console.error('Error finding sessions:', error);
      return [];
    }
  }
}

/**
 * Session storage implementation matching Shopify API expectations
 */
export const sessionStorage = new MySQLSessionStorage();

