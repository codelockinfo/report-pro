<?php

namespace App\Models;

use App\Core\Model;

class Schedule extends Model
{
    protected $table = 'schedules';

    public function findByShop($shopId)
    {
        $stmt = $this->db->prepare("SELECT s.*, r.name as report_name FROM {$this->table} s 
            LEFT JOIN reports r ON s.report_id = r.id 
            WHERE s.shop_id = ? ORDER BY s.created_at DESC");
        $stmt->execute([$shopId]);
        return $stmt->fetchAll();
    }

    public function getDueSchedules()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
            WHERE enabled = 1 AND next_run_at <= NOW() 
            ORDER BY next_run_at ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateNextRun($id, $nextRunAt)
    {
        $this->update($id, [
            'next_run_at' => $nextRunAt,
            'last_run_at' => date('Y-m-d H:i:s'),
            'runs_count' => $this->getRunsCount($id) + 1
        ]);
    }

    private function getRunsCount($id)
    {
        $schedule = $this->find($id);
        return $schedule['runs_count'] ?? 0;
    }
}

