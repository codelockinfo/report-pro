import { getPool } from '../database/connection';
import { executeQuery } from '../database/mysqlHelper';
import { sendEmail } from './email';

export async function generateReport(shopId: string, reportId: number, config: any) {
  const db = getPool();
  
  // Get report configuration
  const reportResult = await executeQuery(
    db,
    'SELECT * FROM reports WHERE id = ? AND shop_id = ?',
    [reportId, shopId]
  );

  if (reportResult.length === 0) {
    throw new Error('Report not found');
  }

  const report = reportResult[0];
  
  // Generate report data based on query_config
  // This would typically fetch data from Shopify API or cached data
  const data = await executeReportQuery(report.query_config, shopId);
  
  return data;
}

export async function sendScheduledReport(scheduleId: number, shopId: string) {
  const db = getPool();
  
  // Get schedule details
  const scheduleResult = await executeQuery(
    db,
    'SELECT * FROM schedules WHERE id = ? AND shop_id = ?',
    [scheduleId, shopId]
  );

  if (scheduleResult.length === 0) {
    throw new Error('Schedule not found');
  }

  const schedule = scheduleResult[0];
  
  // Generate report
  const reportData = await generateReport(shopId, schedule.report_id, {});
  
  // Format report based on format (csv, json, etc.)
  const formattedData = formatReport(reportData, schedule.format);
  
  // Send to recipients
  const recipients = Array.isArray(schedule.recipients) 
    ? schedule.recipients 
    : JSON.parse(schedule.recipients || '[]');
    
  for (const recipient of recipients) {
    if (recipient.type === 'email') {
      await sendEmail(recipient.value, `Report: ${schedule.report_id}`, formattedData);
    }
  }
  
  // Update schedule
  await executeQuery(
    db,
    'UPDATE schedules SET last_run_at = NOW(), runs_count = runs_count + 1, next_run_at = ? WHERE id = ?',
    [calculateNextRun(schedule.frequency, schedule.time_config), scheduleId]
  );
}

async function executeReportQuery(queryConfig: any, shopId: string): Promise<any[]> {
  // This would execute the actual report query
  // For now, return mock data
  return [];
}

function formatReport(data: any[], format: string): string {
  if (format === 'csv') {
    // Convert to CSV
    return data.map(row => Object.values(row).join(',')).join('\n');
  }
  return JSON.stringify(data, null, 2);
}

function calculateNextRun(frequency: string, timeConfig: any): Date {
  const now = new Date();
  const next = new Date(now);

  switch (frequency) {
    case 'hourly':
      next.setHours(next.getHours() + 1);
      break;
    case 'daily':
      next.setDate(next.getDate() + 1);
      break;
    case 'weekly':
      next.setDate(next.getDate() + 7);
      break;
    case 'monthly':
      next.setMonth(next.getMonth() + 1);
      break;
  }

  return next;
}
