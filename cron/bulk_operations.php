<?php
/**
 * Cron Job: Check Bulk Operation Status
 * Run this every 5 minutes: */5 * * * * php /path/to/cron/bulk_operations.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/BulkOperation.php';
require_once __DIR__ . '/../app/Models/Shop.php';
require_once __DIR__ . '/../app/Services/ShopifyService.php';
require_once __DIR__ . '/../app/Services/ReportBuilderService.php';

use App\Models\BulkOperation;
use App\Models\Shop;
use App\Services\ShopifyService;
use App\Services\ReportBuilderService;

$bulkOpModel = new BulkOperation();
$pendingOps = $bulkOpModel->findAll(['status' => 'RUNNING']);

foreach ($pendingOps as $operation) {
    try {
        $shopModel = new Shop();
        $shop = $shopModel->find($operation['shop_id']);
        
        if (!$shop || !$shop['access_token']) {
            continue;
        }

        $shopifyService = new ShopifyService($shop['shop_domain'], $shop['access_token']);
        $reportBuilder = new ReportBuilderService($shopifyService, $shop['id']);
        
        $status = $reportBuilder->processBulkOperationResult($operation['operation_id']);
        
        if ($status) {
            error_log("Bulk operation {$operation['operation_id']} completed");
        }
        
    } catch (Exception $e) {
        error_log("Bulk operation check error: " . $e->getMessage());
    }
}

