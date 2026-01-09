<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Schedule;
use App\Models\Report;

class ScheduleController extends Controller
{
    public function index()
    {
        $shop = $this->requireAuth();
        
        $scheduleModel = new Schedule();
        $schedules = $scheduleModel->findByShop($shop['id']);

        $reportModel = new Report();
        $reports = $reportModel->findByShop($shop['id']);

        $this->view->render('schedule/index', [
            'shop' => $shop,
            'schedules' => $schedules,
            'reports' => $reports,
            'config' => $this->config
        ]);
    }

    public function store()
    {
        $shop = $this->requireAuth();
        
        $reportId = $_POST['report_id'] ?? null;
        $frequency = $_POST['frequency'] ?? 'daily';
        $timeConfig = $_POST['time_config'] ?? [];
        $recipients = $_POST['recipients'] ?? [];
        $format = $_POST['format'] ?? 'csv';

        if (!$reportId) {
            $this->json(['error' => 'Report ID is required'], 400);
        }

        // Calculate next run time
        $nextRunAt = $this->calculateNextRun($frequency, $timeConfig);

        $scheduleModel = new Schedule();
        $scheduleId = $scheduleModel->create([
            'shop_id' => $shop['id'],
            'report_id' => $reportId,
            'frequency' => $frequency,
            'time_config' => json_encode($timeConfig),
            'recipients' => json_encode($recipients),
            'format' => $format,
            'enabled' => 1,
            'next_run_at' => $nextRunAt
        ]);

        $this->json(['success' => true, 'schedule_id' => $scheduleId]);
    }

    public function toggle($id)
    {
        $shop = $this->requireAuth();
        
        $scheduleModel = new Schedule();
        $schedule = $scheduleModel->find($id);

        if (!$schedule || $schedule['shop_id'] != $shop['id']) {
            $this->json(['error' => 'Schedule not found'], 404);
        }

        $enabled = $schedule['enabled'] ? 0 : 1;
        $scheduleModel->update($id, ['enabled' => $enabled]);

        $this->json(['success' => true, 'enabled' => $enabled]);
    }

    private function calculateNextRun($frequency, $timeConfig)
    {
        $now = new \DateTime();
        
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
                $day = $timeConfig['day'] ?? 1; // Monday
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
}

