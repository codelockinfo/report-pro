import { Queue, Worker } from 'bullmq';
import { getRedisClient } from './redis';
import { generateReport, sendScheduledReport } from './reportGenerator';

let reportQueue: Queue;
let scheduleQueue: Queue;

export function getReportQueue(): Queue {
  if (!reportQueue) {
    reportQueue = new Queue('report-generation', {
      connection: getRedisClient() as any,
    });
  }
  return reportQueue;
}

export function getScheduleQueue(): Queue {
  if (!scheduleQueue) {
    scheduleQueue = new Queue('scheduled-reports', {
      connection: getRedisClient() as any,
    });
  }
  return scheduleQueue;
}

export async function initializeQueue() {
  // Initialize queues
  getReportQueue();
  getScheduleQueue();

  // Setup workers
  const reportWorker = new Worker(
    'report-generation',
    async (job) => {
      const { shopId, reportId, config } = job.data;
      return await generateReport(shopId, reportId, config);
    },
    { connection: getRedisClient() as any }
  );

  const scheduleWorker = new Worker(
    'scheduled-reports',
    async (job) => {
      const { scheduleId, shopId } = job.data;
      return await sendScheduledReport(scheduleId, shopId);
    },
    { connection: getRedisClient() as any }
  );

  // Handle errors
  reportWorker.on('failed', (job, err) => {
    console.error(`Report generation job ${job?.id} failed:`, err);
  });

  scheduleWorker.on('failed', (job, err) => {
    console.error(`Scheduled report job ${job?.id} failed:`, err);
  });
}

export async function addReportJob(shopId: string, reportId: number, config: any) {
  const queue = getReportQueue();
  return await queue.add('generate-report', { shopId, reportId, config });
}

export async function addScheduleJob(schedule: any) {
  const queue = getScheduleQueue();
  
  // Calculate next run time based on schedule frequency
  const nextRun = calculateNextRun(schedule.frequency, schedule.time_config);
  
  return await queue.add(
    'scheduled-report',
    { scheduleId: schedule.id, shopId: schedule.shop_id },
    {
      delay: nextRun.getTime() - Date.now(),
      repeat: {
        pattern: getCronPattern(schedule.frequency, schedule.time_config),
      },
    }
  );
}

function calculateNextRun(frequency: string, timeConfig: any): Date {
  const now = new Date();
  const next = new Date(now);

  switch (frequency) {
    case 'hourly':
      next.setHours(next.getHours() + 1);
      next.setMinutes(timeConfig?.minute || 0);
      next.setSeconds(0);
      break;
    case 'daily':
      next.setDate(next.getDate() + 1);
      next.setHours(timeConfig?.hour || 0);
      next.setMinutes(timeConfig?.minute || 0);
      next.setSeconds(0);
      break;
    case 'weekly':
      const daysUntilNext = (timeConfig?.dayOfWeek || 0) - now.getDay();
      next.setDate(next.getDate() + (daysUntilNext <= 0 ? daysUntilNext + 7 : daysUntilNext));
      next.setHours(timeConfig?.hour || 0);
      next.setMinutes(timeConfig?.minute || 0);
      break;
    case 'monthly':
      next.setMonth(next.getMonth() + 1);
      next.setDate(timeConfig?.day || 1);
      next.setHours(timeConfig?.hour || 0);
      next.setMinutes(timeConfig?.minute || 0);
      break;
  }

  return next;
}

function getCronPattern(frequency: string, timeConfig: any): string {
  switch (frequency) {
    case 'hourly':
      return `${timeConfig?.minute || 0} * * * *`;
    case 'daily':
      return `${timeConfig?.minute || 0} ${timeConfig?.hour || 0} * * *`;
    case 'weekly':
      return `${timeConfig?.minute || 0} ${timeConfig?.hour || 0} * * ${timeConfig?.dayOfWeek || 0}`;
    case 'monthly':
      return `${timeConfig?.minute || 0} ${timeConfig?.hour || 0} ${timeConfig?.day || 1} * *`;
    default:
      return '0 0 * * *';
  }
}

