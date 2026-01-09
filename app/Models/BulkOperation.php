<?php

namespace App\Models;

use App\Core\Model;

class BulkOperation extends Model
{
    protected $table = 'bulk_operations';

    public function findByOperationId($operationId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE operation_id = ?");
        $stmt->execute([$operationId]);
        return $stmt->fetch();
    }

    public function findByShop($shopId, $status = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE shop_id = ?";
        $params = [$shopId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

