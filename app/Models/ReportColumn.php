<?php

namespace App\Models;

use App\Core\Model;

class ReportColumn extends Model
{
    protected $table = 'report_columns';

    public function findByReport($reportId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE report_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    public function createMultiple($reportId, $columns)
    {
        foreach ($columns as $index => $column) {
            $this->create([
                'report_id' => $reportId,
                'column_name' => $column['name'],
                'column_label' => $column['label'],
                'column_type' => $column['type'] ?? 'string',
                'is_visible' => $column['visible'] ?? 1,
                'sort_order' => $index
            ]);
        }
    }

    public function deleteByReport($reportId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE report_id = ?");
        return $stmt->execute([$reportId]);
    }
}

