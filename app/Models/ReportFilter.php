<?php

namespace App\Models;

use App\Core\Model;

class ReportFilter extends Model
{
    protected $table = 'report_filters';

    public function findByReport($reportId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE report_id = ?");
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    public function createMultiple($reportId, $filters)
    {
        foreach ($filters as $filter) {
            $this->create([
                'report_id' => $reportId,
                'filter_type' => $filter['type'],
                'filter_field' => $filter['field'],
                'filter_operator' => $filter['operator'],
                'filter_value' => is_array($filter['value']) ? json_encode($filter['value']) : $filter['value']
            ]);
        }
    }

    public function deleteByReport($reportId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE report_id = ?");
        return $stmt->execute([$reportId]);
    }
}

