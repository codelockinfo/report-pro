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
            default:
                throw new \Exception("Unknown dataset: {$dataset}");
        }
    }

    private function buildOrdersQuery($filters, $columns, $groupBy, $aggregations)
    {
        $query = "query { orders { edges { node { ";
        
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
                    $fields[] = 'email';
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
                    $fields[] = 'shippingAddress { country }';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildProductsQuery($filters, $columns, $groupBy, $aggregations)
    {
        $query = "query { products { edges { node { ";
        
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
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildCustomersQuery($filters, $columns, $groupBy, $aggregations)
    {
        $query = "query { customers { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'first_name':
                    $fields[] = 'firstName';
                    break;
                case 'last_name':
                    $fields[] = 'lastName';
                    break;
                case 'email':
                    $fields[] = 'email';
                    break;
                case 'orders_count':
                    $fields[] = 'ordersCount';
                    break;
                case 'total_spent':
                    $fields[] = 'totalSpent { amount currencyCode }';
                    break;
                case 'country':
                    $fields[] = 'defaultAddress { country }';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    private function buildTransactionsQuery($filters, $columns, $groupBy, $aggregations)
    {
        $query = "query { transactions { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'kind':
                    $fields[] = 'kind';
                    break;
                case 'status':
                    $fields[] = 'status';
                    break;
                case 'amount':
                    $fields[] = 'amount';
                    break;
                case 'currency_code':
                    $fields[] = 'currencyCode';
                    break;
                case 'gateway':
                    $fields[] = 'gateway';
                    break;
                case 'created_at':
                    $fields[] = 'createdAt';
                    break;
            }
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    public function executeReport($reportId)
    {
        $reportModel = new Report();
        $report = $reportModel->getWithColumns($reportId);

        if (!$report || $report['shop_id'] != $this->shopId) {
            throw new \Exception("Report not found");
        }

        $config = json_decode($report['query_config'], true);
        $query = $this->buildQuery($config);

        // Create bulk operation
        $result = $this->shopifyService->createBulkOperation($query);
        
        if (!$result || !isset($result['bulkOperationRunQuery']['bulkOperation'])) {
            throw new \Exception("Failed to create bulk operation");
        }

        $operation = $result['bulkOperationRunQuery']['bulkOperation'];
        $operationId = $operation['id'];

        // Save bulk operation
        $bulkOpModel = new BulkOperation();
        $bulkOpModel->create([
            'shop_id' => $this->shopId,
            'operation_id' => $operationId,
            'operation_type' => $config['dataset'],
            'status' => $operation['status'],
            'query' => $query
        ]);

        return $operationId;
    }

    public function processBulkOperationResult($operationId)
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
            $fileData = $this->shopifyService->downloadBulkOperationFile($node['url']);
            
            if ($fileData) {
                $this->processBulkData($operation, $fileData);
            }
        }

        return $currentStatus === 'COMPLETED';
    }

    private function processBulkData($operation, $fileData)
    {
        // Parse NDJSON file
        $lines = explode("\n", trim($fileData));
        $data = [];
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $data[] = json_decode($line, true);
        }

        // Find associated report
        $reportModel = new Report();
        $reports = $reportModel->findAll(['shop_id' => $operation['shop_id']]);
        
        // For now, save to cached_data or process based on operation type
        // This would need to be enhanced based on specific report requirements
    }
}

