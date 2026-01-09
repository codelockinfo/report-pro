<?php

namespace App\Models;

use App\Core\Model;

class ReportResult extends Model
{
    protected $table = 'report_results';

    public function findByReport($reportId, $limit = 1)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE report_id = ? ORDER BY generated_at DESC LIMIT ?");
        $stmt->execute([$reportId, $limit]);
        return $limit == 1 ? $stmt->fetch() : $stmt->fetchAll();
    }

    public function saveResult($reportId, $data, $totalRecords = 0)
    {
        return $this->create([
            'report_id' => $reportId,
            'result_data' => json_encode($data),
            'total_records' => $totalRecords
        ]);
    }
}

