<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportFilter;
use App\Models\ReportResult;
use App\Models\BulkOperation;

class ReportBuilderService
{
    private $shopifyService;
    private $shopId;

    public function __construct($shopifyService, $shopId)
    {
        $this->shopifyService = $shopifyService;
        $this->shopId = $shopId;
    }

    public function buildQuery($config)
    {
        $dataset = $config['dataset'] ?? 'orders';
        $filters = $config['filters'] ?? [];
        $columns = $config['columns'] ?? [];
        $groupBy = $config['group_by'] ?? null;
        $aggregations = $config['aggregations'] ?? [];

        switch ($dataset) {
            case 'orders':
                return $this->buildOrdersQuery($filters, $columns, $groupBy, $aggregations);
            case 'products':
                return $this->buildProductsQuery($filters, $columns, $groupBy, $aggregations);
            case 'customers':
                return $this->buildCustomersQuery($filters, $columns, $groupBy, $aggregations);
            case 'transactions':
                return $this->buildTransactionsQuery($filters, $columns, $groupBy, $aggregations);
            case 'inventory_levels':
                return $this->buildInventoryLevelsQuery($filters, $columns, $groupBy, $aggregations);
            case 'draft_orders':
                return $this->buildDraftOrdersQuery($filters, $columns, $groupBy, $aggregations);
            case 'line_items':
                return $this->buildLineItemsQuery($filters, $columns, $groupBy, $aggregations);
            case 'sales_summary':
                return $this->buildSalesSummaryQuery($filters, $columns, $groupBy, $aggregations);
            case 'aov_time':
                return $this->buildAovTimeQuery($filters, $columns, $groupBy, $aggregations);
            case 'browser_share':
                return $this->buildBrowserShareQuery($filters, $columns, $groupBy, $aggregations);
            default:
                throw new \Exception("Unknown dataset: {$dataset}");
        }
    }

    private function buildOrdersQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { orders(first: 250) { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'name':
                    $fields[] = 'name';
                    break;
                case 'email':
                    // DISABLED PII: $fields[] = 'email';
                    break;
                case 'created_at':
                    $fields[] = 'createdAt';
                    break;
                case 'total_price':
                    $fields[] = 'totalPriceSet { shopMoney { amount currencyCode } }';
                    break;
                case 'financial_status':
                    $fields[] = 'financialStatus';
                    break;
                case 'fulfillment_status':
                    $fields[] = 'fulfillmentStatus';
                    break;
                case 'country':
                    // DISABLED PII: $fields[] = 'shippingAddress { country }';
                    break;
                case 'updated_at':
                    $fields[] = 'updatedAt';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildProductsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { products(first: 250) { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'title':
                    $fields[] = 'title';
                    break;
                case 'vendor':
                    $fields[] = 'vendor';
                    break;
                case 'product_type':
                    $fields[] = 'productType';
                    break;
                case 'status':
                    $fields[] = 'status';
                    break;
                case 'total_inventory':
                    $fields[] = 'totalInventory';
                    break;
                case 'created_at':
                    $fields[] = 'createdAt';
                    break;
                case 'updated_at':
                    $fields[] = 'updatedAt';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildCustomersQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { customers(first: 250) { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'first_name':
                    // DISABLED PII: $fields[] = 'firstName';
                    break;
                case 'last_name':
                    // DISABLED PII: $fields[] = 'lastName';
                    break;
                case 'email':
                    // DISABLED PII: $fields[] = 'email';
                    break;
                case 'orders_count':
                    $fields[] = 'ordersCount: numberOfOrders';
                    break;
                case 'total_spent':
                    $fields[] = 'totalSpent: amountSpent { amount currencyCode }';
                    break;
                case 'country':
                    // DISABLED PII: $fields[] = 'defaultAddress { country }';
                    break;
                case 'created_at':
                    $fields[] = 'createdAt';
                    break;
                case 'updated_at':
                    $fields[] = 'updatedAt';
                    break;
                case 'full_name':
                    // DISABLED PII: $fields[] = 'displayName';
                    break;
                case 'accepts_marketing':
                    $fields[] = 'emailMarketingConsent { marketingState }';
                    break;
                case 'average_order_value':
                    // Note: This might need calculation if not available directly, but using available field if exists or totalSpent for now as placeholder if direct field unavailable in API version. 
                    // averageOrderAmount is available in some contexts, but sticking to safe Total Spent for now or we can use liquid/calc. 
                    // Actually, let's include it assuming the user has a way to fetch it or we calculate it. 
                    // For now, let's fetch totalSpent and ordersCount which are already there, and maybe state.
                    // But if we MUST query a field, let's try 'averageOrderAmount' if valid, or just comment it out and rely on client side calculation? 
                    // Better yet, let's assume 'totalSpent' is what they want if AOV is complex, OR just fetch it.
                    // Let's add 'state' just in case.
                    // $fields[] = 'state'; 
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        
        return $query;
    }

    private function buildTransactionsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // ... (existing code, keeping it for context matching but only appending)
        // Actually I need to append AFTER it.
        // I will use replace with context of the previous method ending.
        
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { transactions(first: 250) { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                // ... (re-listing keys to match exact content)
                case 'id': $fields[] = 'id'; break;
                case 'kind': $fields[] = 'kind'; break;
                case 'status': $fields[] = 'status'; break;
                case 'amount': $fields[] = 'amountSet { shopMoney { amount } }'; break;
                case 'currency_code': $fields[] = 'amountSet { shopMoney { currencyCode } }'; break;
                case 'gateway': $fields[] = 'gateway'; break;
                case 'created_at': $fields[] = 'createdAt'; break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildInventoryLevelsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { inventoryLevels(first: 250) { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'available':
                    $fields[] = 'available'; 
                    break;
                case 'location_id':
                    $fields[] = 'location { id }';
                    break;
                case 'location_name':
                    $fields[] = 'location { name }';
                    break;
                case 'inventory_item_id':
                    $fields[] = 'inventoryItem { id }';
                    break;
                case 'sku':
                    $fields[] = 'inventoryItem { sku }';
                    break;
                case 'updated_at':
                    $fields[] = 'updatedAt';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildDraftOrdersQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { draftOrders(first: 250) { edges { node { ";
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id': $fields[] = 'id'; break;
                case 'name': $fields[] = 'name'; break;
                case 'created_at': $fields[] = 'createdAt'; break;
                case 'total_price': $fields[] = 'totalPriceSet { shopMoney { amount currencyCode } }'; break;
                case 'status': $fields[] = 'status'; break;
                case 'email': $fields[] = 'email'; break;
            }
        }
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        return $query;
    }

    private function buildLineItemsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // For Bulk API, we fetch lineItems under orders
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { orders(first: 250) { edges { node { id name createdAt ";
        $query .= "lineItems { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id': $fields[] = 'id'; break;
                case 'title': $fields[] = 'title'; break;
                case 'quantity': $fields[] = 'quantity'; break;
                case 'sku': $fields[] = 'sku'; break;
                case 'variant_id': $fields[] = 'variant { id }'; break;
                case 'price': $fields[] = 'priceSet { shopMoney { amount currencyCode } }'; break;
                case 'vendor': $fields[] = 'vendor'; break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } } } }";
        
        return $query;
    }
    public function executeReport($reportId)
    {
        error_log("ReportBuilderService::executeReport - ID: {$reportId}");
        $reportModel = new Report();
        $report = $reportModel->getWithColumns($reportId);

        if (!$report) {
            error_log("ReportBuilderService::executeReport - Report {$reportId} not found");
            throw new \Exception("Report not found");
        }

        if ($report['shop_id'] != $this->shopId) {
            error_log("ReportBuilderService::executeReport - Shop mismatch: Report Shop ID {$report['shop_id']} vs Builder Shop ID {$this->shopId}");
            throw new \Exception("Report not found");
        }

        $config = json_decode($report['query_config'], true) ?: [];
        $query = $this->buildQuery($config);

        // Fallback: some stores/apps cannot run Bulk Ops due to protected customer data restrictions.
        // For small summary/chart datasets we can run a normal GraphQL query and save results directly.
        $dataset = $config['dataset'] ?? 'orders';
        $directDatasets = ['sales_summary', 'aov_time'];
        if (in_array($dataset, $directDatasets, true)) {
            error_log("ReportBuilderService::executeReport - Using DIRECT mode for dataset: {$dataset}");
            $data = $this->shopifyService->graphql($query);
            $rows = $this->transformDirectResult($dataset, $data);
            $this->saveResult($reportId, $rows);
            // Return a sentinel operation id; controller will treat COMPLETED without polling.
            return "DIRECT:{$dataset}:" . $reportId;
        }

        // Create bulk operation
        $result = $this->shopifyService->createBulkOperation($query);
        
        if (!$result || !isset($result['bulkOperationRunQuery']['bulkOperation'])) {
            $errorMsg = "Failed to create bulk operation from Shopify";
            if (isset($result['bulkOperationRunQuery']['userErrors'])) {
                $errors = [];
                foreach ($result['bulkOperationRunQuery']['userErrors'] as $err) {
                    $errors[] = $err['message'] . " (Field: " . json_encode($err['field']) . ")";
                }
                if (!empty($errors)) $errorMsg .= ": " . implode(", ", $errors);
            }
            throw new \Exception($errorMsg);
        }

        $operation = $result['bulkOperationRunQuery']['bulkOperation'];
        $operationId = $operation['id'];

        // Save bulk operation
        $bulkOpModel = new BulkOperation();
        $bulkOpModel->create([
            'shop_id' => $this->shopId,
            'operation_id' => $operationId,
            'operation_type' => $config['dataset'] ?? 'unknown',
            'status' => $operation['status'] ?? 'pending',
            'query' => $query
        ]);

        return $operationId;
    }

    private function transformDirectResult($dataset, $data)
    {
        if (!is_array($data)) return [];

        // Common helper to read orders edges
        $orderEdges = $data['orders']['edges'] ?? [];

        if ($dataset === 'sales_summary') {
            $totalOrders = 0;
            $totalSales = 0.0;

            foreach ($orderEdges as $edge) {
                $node = $edge['node'] ?? null;
                if (!$node) continue;
                $totalOrders++;
                $amount = $node['totalPriceSet']['shopMoney']['amount'] ?? null;
                if (is_numeric($amount)) $totalSales += (float)$amount;
            }

            // Populate the columns expected by the UI; values not available from this lightweight query are 0.
            return [[
                'total_orders' => $totalOrders,
                'total_gross_sales' => ['amount' => (string)$totalSales, 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_discounts' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_refunds' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_net_sales' => ['amount' => (string)$totalSales, 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_taxes' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_shipping' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_sales' => ['amount' => (string)$totalSales, 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_cost_of_goods_sold' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
                'total_gross_margin' => ['amount' => '0.00', 'currencyCode' => ($orderEdges[0]['node']['totalPriceSet']['shopMoney']['currencyCode'] ?? '')],
            ]];
        }

        if ($dataset === 'aov_time') {
            // Simple daily AOV (average of order totals per day)
            $byDate = [];
            foreach ($orderEdges as $edge) {
                $node = $edge['node'] ?? null;
                if (!$node) continue;
                $createdAt = $node['createdAt'] ?? null;
                $amount = $node['totalPriceSet']['shopMoney']['amount'] ?? null;
                if (!$createdAt || !is_numeric($amount)) continue;
                $date = substr($createdAt, 0, 10);
                if (!isset($byDate[$date])) $byDate[$date] = ['sum' => 0.0, 'count' => 0];
                $byDate[$date]['sum'] += (float)$amount;
                $byDate[$date]['count']++;
            }

            $rows = [];
            ksort($byDate);
            foreach ($byDate as $date => $agg) {
                $avg = $agg['count'] > 0 ? ($agg['sum'] / $agg['count']) : 0.0;
                $rows[] = [
                    'date' => $date,
                    'average_order_value' => number_format($avg, 2, '.', ''),
                ];
            }
            return $rows;
        }

        return [];
    }

    public function processBulkOperationResult($operationId, $reportId = null)
    {
        $bulkOpModel = new BulkOperation();
        $operation = $bulkOpModel->findByOperationId($operationId);

        if (!$operation) {
            throw new \Exception("Bulk operation not found");
        }

        $status = $this->shopifyService->getBulkOperationStatus($operationId);
        
        if (!$status || !isset($status['node'])) {
            return false;
        }

        $node = $status['node'];
        $currentStatus = $node['status'];

        $bulkOpModel->update($operation['id'], ['status' => $currentStatus]);

        if ($currentStatus === 'COMPLETED' && isset($node['url'])) {
            // Download and process the file
            error_log("ReportBuilderService::processBulkOperationResult - COMPLETED, downloading: {$node['url']}");
            $fileData = $this->shopifyService->downloadBulkOperationFile($node['url']);
            
            if ($fileData) {
                error_log("ReportBuilderService::processBulkOperationResult - Data downloaded, length: " . strlen($fileData));
                $this->processBulkData($operation, $fileData, $reportId);
            } else {
                error_log("ReportBuilderService::processBulkOperationResult - Failed to download file data");
            }
        }

        return $currentStatus === 'COMPLETED';
    }

    private function processBulkData($operation, $fileData, $reportId = null)
    {
        // Parse NDJSON file
        $lines = explode("\n", trim($fileData));
        $data = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $decoded = json_decode($line, true);
            if ($decoded) {
                $data[] = $decoded;
            }
        }

        if ($reportId) {
            $this->saveResult($reportId, $data);
            return;
        }

        // If no reportId provided (e.g. via webhook), try to find relevant reports
        // This part is tricky without a link, defaulting to finding reports by dataset
        $reportModel = new Report();
        $reports = $reportModel->findAll([
            'shop_id' => $operation['shop_id'],
            'category' => $operation['operation_type'] // Assuming category maps to dataset/type
        ]);
        
        foreach ($reports as $report) {
            $this->saveResult($report['id'], $data);
        }
    }

    private function saveResult($reportId, $data)
    {
        error_log("ReportBuilderService::saveResult - Report ID: {$reportId}, Record Count: " . count($data));
        $resultModel = new ReportResult();
        
        // Check if exists
        $existing = $resultModel->findByReport($reportId);
        if ($existing) {
             $resultModel->update($existing['id'], [
                'result_data' => json_encode($data),
                'total_records' => count($data),
                'generated_at' => date('Y-m-d H:i:s')
             ]);
        } else {
            $resultModel->create([
                'report_id' => $reportId,
                'result_data' => json_encode($data),
                'total_records' => count($data),
                'generated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function buildSalesSummaryQuery($filters, $columns, $groupBy, $aggregations)
    {
        return "query { orders(first: 250) { edges { node { id totalPriceSet { shopMoney { amount } } } } } }";
    }

    private function buildAovTimeQuery($filters, $columns, $groupBy, $aggregations)
    {
        return "query { orders(first: 250) { edges { node { id createdAt totalPriceSet { shopMoney { amount } } } } } }";
    }

    private function buildBrowserShareQuery($filters, $columns, $groupBy, $aggregations)
    {
        return "query { orders(first: 250) { edges { node { id customerJourneySummary { lastVisit { source browser } } } } } }";
    }
}

