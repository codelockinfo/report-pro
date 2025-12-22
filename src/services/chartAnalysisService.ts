import { getPool } from '../database/connection';
import { executeQuery, executeInsertAndSelect, executeUpdateAndSelect } from '../database/mysqlHelper';

export async function getChartsFromDB(shopId: string) {
  const db = getPool();
  return await executeQuery(
    db,
    'SELECT * FROM charts WHERE shop_id = ? ORDER BY created_at DESC',
    [shopId]
  );
}

export async function getChartByIdFromDB(id: string, shopId: string) {
  const db = getPool();
  const result = await executeQuery(
    db,
    'SELECT * FROM charts WHERE id = ? AND shop_id = ?',
    [id, shopId]
  );
  return result[0] || null;
}

export async function createChartInDB(shopId: string, chartData: any) {
  const db = getPool();
  const insertQuery = `
    INSERT INTO charts (shop_id, name, chart_type, config)
    VALUES (?, ?, ?, ?)
  `;
  const selectQuery = 'SELECT * FROM charts WHERE id = ?';
  
  return await executeInsertAndSelect(
    db,
    insertQuery,
    selectQuery,
    [
      shopId,
      chartData.name,
      chartData.chart_type,
      JSON.stringify(chartData.config || {}),
    ]
  );
}

export async function updateChartInDB(id: string, shopId: string, updateData: any) {
  const db = getPool();
  const updates: string[] = [];
  const params: any[] = [];

  if (updateData.name) {
    updates.push('name = ?');
    params.push(updateData.name);
  }
  if (updateData.chart_type) {
    updates.push('chart_type = ?');
    params.push(updateData.chart_type);
  }
  if (updateData.config) {
    updates.push('config = ?');
    params.push(JSON.stringify(updateData.config));
  }

  updates.push('updated_at = CURRENT_TIMESTAMP');
  params.push(id, shopId);

  const updateQuery = `UPDATE charts SET ${updates.join(', ')} WHERE id = ? AND shop_id = ?`;
  const selectQuery = 'SELECT * FROM charts WHERE id = ? AND shop_id = ?';

  return await executeUpdateAndSelect(db, updateQuery, selectQuery, params);
}

export async function deleteChartFromDB(id: string, shopId: string) {
  const db = getPool();
  await executeQuery(db, 'DELETE FROM charts WHERE id = ? AND shop_id = ?', [id, shopId]);
}

export async function getChartDataFromDB(chartId: number, shopId: string) {
  const chart = await getChartByIdFromDB(chartId.toString(), shopId);
  if (!chart) {
    throw new Error('Chart not found');
  }
  return await generateChartData(shopId, chart.config);
}

export async function generateChartData(shopId: string, config: any) {
  // This would generate chart data based on config
  // For now, return mock data structure
  const { chartType, metrics, dimensions, filters, dateRange } = config;

  // In a real implementation, this would query the database or Shopify API
  // based on the chart configuration
  return {
    labels: [],
    datasets: [],
    chartType,
    metrics,
    dimensions,
  };
}
