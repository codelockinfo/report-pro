<?php
/**
 * Cron Job: Process Scheduled Reports
 * Run this every minute: * * * * * php /path/to/cron/scheduled_reports.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/Schedule.php';
require_once __DIR__ . '/../app/Models/Report.php';
require_once __DIR__ . '/../app/Models/Shop.php';
require_once __DIR__ . '/../app/Services/ShopifyService.php';
require_once __DIR__ . '/../app/Services/ReportBuilderService.php';
require_once __DIR__ . '/../app/Services/ExportService.php';

use App\Models\Schedule;
use App\Models\Report;
use App\Models\Shop;
use App\Services\ShopifyService;
use App\Services\ReportBuilderService;
use App\Services\ExportService;

$scheduleModel = new Schedule();
$dueSchedules = $scheduleModel->getDueSchedules();

foreach ($dueSchedules as $schedule) {
    try {
        // Get shop
        $shopModel = new Shop();
        $shop = $shopModel->find($schedule['shop_id']);
        
        if (!$shop || !$shop['access_token']) {
            error_log("Scheduled report {$schedule['id']}: Shop not found or no access token");
            continue;
        }

        // Get report
        $reportModel = new Report();
        $report = $reportModel->find($schedule['report_id']);
        
        if (!$report) {
            error_log("Scheduled report {$schedule['id']}: Report not found");
            continue;
        }

        // Initialize services
        $shopifyService = new ShopifyService($shop['shop_domain'], $shop['access_token']);
        $reportBuilder = new ReportBuilderService($shopifyService, $shop['id']);
        
        // Run report
        $operationId = $reportBuilder->executeReport($report['id']);
        
        // Generate export
        $exportService = new ExportService();
        $format = $schedule['format'] ?? 'csv';
        
        // Wait a bit for report to complete (in production, use webhooks or polling)
        sleep(5);
        
        $export = $exportService->exportToCSV($report['id'], $shop['id']);
        
        // Send email (implement email service)
        $recipients = json_decode($schedule['recipients'], true) ?? [];
        if (!empty($recipients)) {
            // sendEmail($recipients, $export);
        }
        
        // Update next run time
        $timeConfig = json_decode($schedule['time_config'], true) ?? [];
        $frequency = $schedule['frequency'];
        $nextRunAt = calculateNextRun($frequency, $timeConfig);
        $scheduleModel->updateNextRun($schedule['id'], $nextRunAt);
        
        error_log("Scheduled report {$schedule['id']} executed successfully");
        
    } catch (Exception $e) {
        error_log("Scheduled report {$schedule['id']} error: " . $e->getMessage());
    }
}

function calculateNextRun($frequency, $timeConfig) {
    $now = new DateTime();
    
    switch ($frequency) {
        case 'daily':
            $hour = $timeConfig['hour'] ?? 9;
            $minute = $timeConfig['minute'] ?? 0;
            $next = clone $now;
            $next->setTime($hour, $minute);
            if ($next <= $now) {
                $next->modify('+1 day');
            }
            return $next->format('Y-m-d H:i:s');
            
        case 'weekly':
            $day = $timeConfig['day'] ?? 1;
            $hour = $timeConfig['hour'] ?? 9;
            $minute = $timeConfig['minute'] ?? 0;
            $next = clone $now;
            $next->setTime($hour, $minute);
            $daysUntil = ($day - $next->format('w') + 7) % 7;
            if ($daysUntil == 0 && $next <= $now) {
                $daysUntil = 7;
            }
            $next->modify("+{$daysUntil} days");
            return $next->format('Y-m-d H:i:s');
            
        case 'monthly':
            $day = $timeConfig['day'] ?? 1;
            $hour = $timeConfig['hour'] ?? 9;
            $minute = $timeConfig['minute'] ?? 0;
            $next = clone $now;
            $next->setTime($hour, $minute);
            $next->setDate($next->format('Y'), $next->format('m'), $day);
            if ($next <= $now) {
                $next->modify('+1 month');
            }
            return $next->format('Y-m-d H:i:s');
            
        default:
            return $now->modify('+1 day')->format('Y-m-d H:i:s');
    }
}

