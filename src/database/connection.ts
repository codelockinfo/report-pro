import mysql from 'mysql2/promise';
import dotenv from 'dotenv';

dotenv.config();

let pool: mysql.Pool;

export function getPool(): mysql.Pool {
  if (!pool) {
    pool = mysql.createPool({
      host: process.env.DB_HOST || 'localhost',
      port: Number(process.env.DB_PORT) || 3306,
      database: process.env.DB_NAME || 'report_pro',
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
      waitForConnections: true,
      connectionLimit: 20,
      queueLimit: 0,
      enableKeepAlive: true,
      keepAliveInitialDelay: 0,
    });
  }
  return pool;
}

export async function initializeDatabase() {
  const db = getPool();
  
  // Test connection
  await db.execute('SELECT NOW()');
  
  // Run migrations
  await runMigrations();
}

async function runMigrations() {
  const db = getPool();
  
  // Create tables if they don't exist
  await db.execute(`
    CREATE TABLE IF NOT EXISTS shops (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_domain VARCHAR(255) UNIQUE NOT NULL,
      store_name VARCHAR(255),
      access_token TEXT,
      scope TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_shop_domain (shop_domain)
    )
  `);

  await db.execute(`
    CREATE TABLE IF NOT EXISTS reports (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_id INT NOT NULL,
      name VARCHAR(255) NOT NULL,
      category VARCHAR(100),
      description TEXT,
      query_config JSON,
      is_custom BOOLEAN DEFAULT false,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
      INDEX idx_reports_shop_id (shop_id),
      INDEX idx_reports_category (category)
    )
  `);

  await db.query(`
    CREATE TABLE IF NOT EXISTS schedules (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_id INT NOT NULL,
      report_id INT NOT NULL,
      frequency VARCHAR(50) NOT NULL,
      time_config JSON,
      recipients JSON,
      format VARCHAR(20) DEFAULT 'csv',
      enabled BOOLEAN DEFAULT true,
      next_run_at DATETIME,
      last_run_at DATETIME,
      runs_count INT DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
      FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
      INDEX idx_schedules_shop_id (shop_id),
      INDEX idx_schedules_next_run (next_run_at),
      INDEX idx_schedules_enabled (enabled)
    )
  `);

  await db.query(`
    CREATE TABLE IF NOT EXISTS settings (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_id INT NOT NULL,
      week_start VARCHAR(20) DEFAULT 'sunday',
      locale VARCHAR(50) DEFAULT 'en-US',
      timezone VARCHAR(50) DEFAULT 'UTC',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
      UNIQUE KEY unique_shop_settings (shop_id)
    )
  `);

  await db.query(`
    CREATE TABLE IF NOT EXISTS integrations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_id INT NOT NULL,
      type VARCHAR(50) NOT NULL,
      config JSON,
      enabled BOOLEAN DEFAULT true,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
      INDEX idx_integrations_shop_id (shop_id),
      INDEX idx_integrations_type (type)
    )
  `);

  await db.query(`
    CREATE TABLE IF NOT EXISTS charts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      shop_id INT NOT NULL,
      name VARCHAR(255) NOT NULL,
      chart_type VARCHAR(50) NOT NULL,
      config JSON,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
      INDEX idx_charts_shop_id (shop_id)
    )
  `);
}

