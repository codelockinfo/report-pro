<?php

namespace App\Models;

use App\Core\Model;

class Report extends Model
{
    protected $table = 'reports';

    public function findByShop($shopId, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE shop_id = ?";
        $params = [$shopId];

        if (isset($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (isset($filters['is_custom'])) {
            $sql .= " AND is_custom = ?";
            $params[] = $filters['is_custom'];
        }

        if (isset($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getWithColumns($id)
    {
        $report = $this->find($id);
        if (!$report) {
            return null;
        }

        $columnModel = new ReportColumn();
        $report['columns'] = $columnModel->findByReport($id);

        $filterModel = new ReportFilter();
        $report['filters'] = $filterModel->findByReport($id);

        return $report;
    }
}

