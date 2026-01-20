<?php

namespace App\Models;

use App\Core\Model;

class Shop extends Model
{
    protected $table = 'shops';

    public function findByDomain($domain)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE shop_domain = ?");
            $stmt->execute([$domain]);
            $result = $stmt->fetch();
            
            if ($result) {
                error_log("Shop::findByDomain - Found shop: {$domain}, ID: {$result['id']}");
            } else {
                error_log("Shop::findByDomain - Shop not found: {$domain}");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("Shop::findByDomain exception: " . $e->getMessage());
            return false;
        }
    }

    public function createOrUpdate($domain, $data)
    {
        try {
            error_log("Shop::createOrUpdate - Starting for domain: {$domain}");
            error_log("Shop::createOrUpdate - Data: " . json_encode($data));
            
            $existing = $this->findByDomain($domain);
            
            if ($existing) {
                error_log("Shop::createOrUpdate - Updating existing shop ID: {$existing['id']}");
                $updateResult = $this->update($existing['id'], $data);
                
                if ($updateResult === false) {
                    error_log("Shop::createOrUpdate - Update failed for shop ID: {$existing['id']}");
                    return false;
                }
                
                error_log("Shop::createOrUpdate - Successfully updated shop ID: {$existing['id']}");
                return $existing['id'];
            } else {
                error_log("Shop::createOrUpdate - Creating new shop for domain: {$domain}");
                $data['shop_domain'] = $domain;
                $newId = $this->create($data);
                
                if ($newId === false) {
                    error_log("Shop::createOrUpdate - Create failed for domain: {$domain}");
                    return false;
                }
                
                error_log("Shop::createOrUpdate - Successfully created shop ID: {$newId}");
                return $newId;
            }
        } catch (\Exception $e) {
            error_log("Shop::createOrUpdate exception: " . $e->getMessage());
            error_log("Domain: {$domain}, Data: " . json_encode($data));
            return false;
        }
    }

    public function getAccessToken($shopId)
    {
        $shop = $this->find($shopId);
        return $shop['access_token'] ?? null;
    }
    
    public function getByDomain($domain)
    {
        return $this->findByDomain($domain);
    }
}

