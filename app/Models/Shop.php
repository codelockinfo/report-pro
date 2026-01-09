<?php

namespace App\Models;

use App\Core\Model;

class Shop extends Model
{
    protected $table = 'shops';

    public function findByDomain($domain)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE shop_domain = ?");
        $stmt->execute([$domain]);
        return $stmt->fetch();
    }

    public function createOrUpdate($domain, $data)
    {
        $existing = $this->findByDomain($domain);
        
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        } else {
            $data['shop_domain'] = $domain;
            return $this->create($data);
        }
    }

    public function getAccessToken($shopId)
    {
        $shop = $this->find($shopId);
        return $shop['access_token'] ?? null;
    }
}

