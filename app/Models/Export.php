<?php

namespace App\Models;

use App\Core\Model;

class Export extends Model
{
    protected $table = 'exports';

    public function findByShop($shopId, $limit = 50)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE shop_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$shopId, $limit]);
        return $stmt->fetchAll();
    }

    public function findByToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE download_token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}

