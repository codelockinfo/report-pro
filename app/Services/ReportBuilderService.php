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
    private $activeFilters = [];

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

        // FIX: Total Inventory Summary must filter ALL products, ignoring date range
        if ($dataset === 'total_inventory_summary') {
             $filters = array_filter($filters, function($f) {
                 return !in_array($f['field'], ['created_at', 'updated_at']);
             });
        }

        switch ($dataset) {
            case 'orders':
                return $this->buildOrdersQuery($filters, $columns, $groupBy, $aggregations);
            case 'products':
            case 'products_by_type':
            case 'products_vendor':
            case 'inventory_by_product':
            case 'inventory_by_vendor':
            case 'inventory_by_sku':
            case 'total_inventory_summary':
            case 'variant_costs':
                return $this->buildProductsQuery($filters, $columns, $groupBy, $aggregations, $dataset);
            case 'customers':
            case 'customers_by_country':
                return $this->buildCustomersQuery($filters, $columns, $groupBy, $aggregations);
            case 'transactions':
                return $this->buildTransactionsQueryFixed($filters, $columns, $groupBy, $aggregations);
            case 'inventory_levels':
                return $this->buildInventoryLevelsQueryFixed($filters, $columns, $groupBy, $aggregations);
            case 'markets':
                return $this->buildMarketsQuery($filters, $columns, $groupBy, $aggregations);
            case 'draft_orders':
                return $this->buildDraftOrdersQuery($filters, $columns, $groupBy, $aggregations);
            case 'line_items':
            case 'products_variant':
            case 'sales_by_variant':
                return $this->buildLineItemsQuery($filters, $columns, $groupBy, $aggregations);
            case 'sales_summary':
                return $this->buildSalesSummaryQuery($filters, $columns, $groupBy, $aggregations);
            case 'aov_time':
                return $this->buildAovTimeQuery($filters, $columns, $groupBy, $aggregations);
            case 'browser_share':
                return $this->buildBrowserShareQuery($filters, $columns, $groupBy, $aggregations);
            case 'pending_fulfillment_by_variant':
                return $this->buildPendingFulfillmentsQuery($filters, $columns, $groupBy, $aggregations); // Implement this method
            case 'payouts':
                return $this->buildPayoutsQuery($filters, $columns, $groupBy, $aggregations);

            case 'monthly_disputes':
            case 'pending_disputes':
                return $this->buildDisputesQuery($filters, $columns, $groupBy, $aggregations);
            default:

                throw new \Exception("Unknown dataset: {$dataset}");
        }
    }

    private function buildOrdersQuery($filters, $columns, $groupBy, $aggregations)
    {
        // 1. Build Query (excluding status fields)
        $searchQuery = $this->buildSearchQuery($filters, [
            'id', 'name', 'email', 'created_at', 'updated_at', 'tag' 
        ]);
        
        // 2. FORCE CLEANUP: In case something slipped through, regex remove status filters
        // Shopify API throws 500 if these exist in the query string.
        $searchQuery = preg_replace('/(financialStatus|fulfillmentStatus|financial_status|fulfillment_status):[^\s]+/', '', $searchQuery);
        $searchQuery = trim(preg_replace('/\s+AND\s+/', ' AND ', $searchQuery));
        $searchQuery = trim($searchQuery, " AND");

        $args = "first: 250";
        // Default to status:any to see ALL orders
        $queryStr = "status:any";
        
        if (!empty($searchQuery)) {
            $queryStr .= " AND " . $searchQuery;
        }
        
        $args .= ", query: \"" . addslashes($queryStr) . "\"";

        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { orders($args) { edges { node { ";
        
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
                    // $fields[] = 'financialStatus'; // Temporarily disabled to debug API error
                    break;
                case 'fulfillment_status':
                    // $fields[] = 'fulfillmentStatus'; // Temporarily disabled to debug API error
                    break;
                case 'country':
                    $fields[] = 'shippingAddress { country }';
                    break;
                case 'updated_at':
                    $fields[] = 'updatedAt';
                    break;
            }
        }
        
        if (empty($fields)) {
            $fields[] = 'id';
        }
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        
        return $query;
    }

    private function buildTransactionsQueryFixed($filters, $columns, $groupBy, $aggregations)
    {
        // Transactions must be fetched via Orders in Admin API
        $searchQuery = $this->buildSearchQuery($filters, ['id', 'status', 'created_at']);
        
        // We filter orders, not transactions directly (mostly)
        // If sorting/filtering by transaction date is needed, it's complex via API, so we fetch recent orders.
        $args = "first: 250, query: \"status:any\"";
        if (!empty($searchQuery)) {
             $args = "first: 250, query: \"" . addslashes($searchQuery) . "\"";
        }
        
        $query = "query { orders($args) { edges { node { id name email ";
        
        // Fetch nested transactions
        $query .= "transactions { id kind status amountSet { shopMoney { amount currencyCode } } createdAt gateway test parentTransaction { id } }";
        
        $query .= " } } } }";
        
        return $query;
    }

    private function buildInventoryLevelsQueryFixed($filters, $columns, $groupBy, $aggregations)
    {
         $query = "query { inventoryLevels(first: 250) { edges { node { id available updatedAt location { id name } inventoryItem { id sku } } } } }";
         return $query;
    }

    private function buildMarketsQuery($filters, $columns, $groupBy, $aggregations)
    {
         // We query Markets and their regions. Only Countries supported for now in this report.
         // Nested Pagination for regions (first: 250) handled by Bulk API? 
         // Usually Bulk API flattens 1 level of nesting if it's a Connection.
         $query = "query { markets(first: 250) { edges { node { id name primary enabled regions(first: 250) { edges { node { id name ... on MarketRegionCountry { code } } } } } } } }";
         return $query;
    }

    private function buildProductsQuery($filters, $columns, $groupBy, $aggregations, $dataset = '')
    {
        $searchQuery = $this->buildSearchQuery($filters, [
            'id', 'title', 'vendor', 'product_type', 'status', 'created_at', 'updated_at', 'tag'
        ]);
        $args = "first: 250";
        if (!empty($searchQuery)) {
            $args .= ", query: \"" . addslashes($searchQuery) . "\"";
        }

        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { products($args) { edges { node { ";
        
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
                case 'price':
                    $fields[] = 'priceRangeV2 { minVariantPrice { amount currencyCode } }';
                    break;
                case 'image':
                    $fields[] = 'featuredImage { url }';
                    break;
                case 'product_title':
                    $fields[] = 'title';
                    break;
            }
        }

        // Force fetch variants for specific datasets or if variant fields are needed
        $needsVariants = false;
        $variantFields = ['total_variants', 'total_inventory_value', 'total_inventory_cost', 'total_quantity', 'unit_margin', 'unit_margin_percent', 'cost', 'sku', 'variant_title'];
        $datasetNeedsVariants = ['inventory_by_sku', 'pending_fulfillment_by_variant'];
        
        $hasVariantColumn = !empty(array_intersect($variantFields, $columns));
        $filterFields = array_column($filters, 'field');
        $hasVariantFilter = !empty(array_intersect($variantFields, $filterFields));
        $isVariantDataset = in_array($dataset, $datasetNeedsVariants);

        if ($hasVariantColumn || $hasVariantFilter || $isVariantDataset) {
            $needsVariants = true;
        }

        if ($needsVariants) {
             $fields[] = 'variants(first: 200) { edges { node { id title sku price inventoryQuantity image { url } inventoryItem { unitCost { amount currencyCode } } } } }';
             if (!in_array('id', $fields) && !in_array('id: id', $fields)) {
                 array_unshift($fields, 'id');
             }
        }
        
        // Force fetch fields required for filtering and mapping, even if not in columns
        if (!in_array('createdAt', $fields)) $fields[] = 'createdAt';
        if (!in_array('updatedAt', $fields)) $fields[] = 'updatedAt';
        if (!in_array('status', $fields)) $fields[] = 'status';
        if (!in_array('vendor', $fields)) $fields[] = 'vendor';
        if (!in_array('productType', $fields)) $fields[] = 'productType';

        if (empty($fields)) {
            $fields[] = 'id';
        }
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        
        return $query;
    }

    private function buildCustomersQuery($filters, $columns, $groupBy, $aggregations)
    {
        // 1. Build Customer Query
        // CRITICAL FIX: "Date Range" (created_at) usually means "Sales Activity" in this report context.
        // If we filter Customers by created_at, we exclude old customers who bought recently.
        // So for the PARENT (Customer) query, we map 'created_at' filters to 'updated_at' to find ANY active customer.
        $custFilters = $filters;
        foreach ($custFilters as &$f) {
            if ($f['field'] === 'created_at') {
                $f['field'] = 'updated_at';
            }
        }
        unset($f); // break ref

        $custSearchQuery = $this->buildSearchQuery($custFilters, [
            'id', 'first_name', 'last_name', 'email', 'country', 'updated_at', 'tag', 'accepts_marketing'
        ]);
        
        $custArgs = "first: 250";
        if (!empty($custSearchQuery)) {
             $custArgs .= ", query: \"" . addslashes($custSearchQuery) . "\"";
        }

        // 2. Build Nested Order Query
        // RELIABILITY FIX: We fetch the last 250 orders (unfiltered) and filter them in PHP.
        // This avoids Shopify Search Syntax issues and ensures we don't miss orders due to weird indexing.
        $ordArgs = "first: 250, sortKey: CREATED_AT, reverse: true";
        
        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        $query = "query { customers($custArgs) { edges { node { ";
        
        
        $fetchOrders = false;
        // Check for 'total_customers' to identify the country report since $this->config is not available here
        if (in_array('orders_count', $columns) || in_array('total_spent', $columns) || in_array('total_customers', $columns)) {
            $fetchOrders = true;
        }

        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $fields[] = 'id';
                    break;
                case 'first_name':
                    $fields[] = 'first_name: firstName';
                    break;
                case 'last_name':
                    $fields[] = 'last_name: lastName';
                    break;
                case 'email':
                    $fields[] = 'email';
                    break;
                // Note: We calculate orders_count and total_spent manually from nested orders
                case 'country':
                    $fields[] = 'defaultAddress { country }';
                    $fields[] = 'addresses { country }';
                    break;
                case 'created_at':
                    $fields[] = 'created_at: createdAt';
                    break;
                case 'updated_at':
                    $fields[] = 'updated_at: updatedAt';
                    break;
                case 'full_name':
                    $fields[] = 'full_name: displayName';
                    break;
                case 'accepts_marketing':
                    $fields[] = 'emailMarketingConsent { marketingState }';
                    break;
            }
        }
        
        if ($fetchOrders) {
            // Bulk API Requirement: Parent node must select 'id' when fetching nested connections
            if (!in_array('id', $fields)) {
                array_unshift($fields, 'id');
            }
            $fields[] = 'orders(' . $ordArgs . ') { edges { node { id createdAt totalPriceSet { shopMoney { amount currencyCode } } shippingAddress { country } billingAddress { country } } } }';
        }
        
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        
        return $query;
    }

    private function buildTransactionsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // Fetch via Orders to ensure stability and context
        // Fetch ALL orders (status:any) to ensure full history
        $query = "query { orders(query: \"status:any\") { edges { node { id name createdAt ";
        $query .= "transactions { edges { node { ";
        
        $fields = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'id': $fields[] = 'id'; break;
                case 'kind': $fields[] = 'kind'; break;
                case 'status': $fields[] = 'status'; break;
                case 'amount': $fields[] = 'amountSet { shopMoney { amount } }'; break;
                case 'currency_code': $fields[] = 'amountSet { shopMoney { currencyCode } }'; break;
                case 'gateway': $fields[] = 'gateway'; break;
                case 'created_at': $fields[] = 'createdAt'; break;
            }
        }
        
        if (empty($fields)) {
            $fields[] = 'id';
        }
        $query .= implode(' ', $fields);
        $query .= " } } } } } } }";
        
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
                case 'image':
                    $fields[] = 'inventoryItem { variant { image { url } } }';
                    break;
            }
        }
        
        if (empty($fields)) {
            $fields[] = 'id';
        }
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        
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
        if (empty($fields)) {
            $fields[] = 'id';
        }
        $query .= implode(' ', $fields);
        $query .= " } } } }";
        return $query;
    }

    private function buildLineItemsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // For Bulk API, we fetch lineItems under orders
        // Fetch ALL orders (status:any) to ensure full history
        $query = "query { orders(query: \"status:any\") { edges { node { id name createdAt ";
        $query .= "lineItems { edges { node { id title quantity sku variant { id title product { title productType } } taxLines { priceSet { shopMoney { amount } } } originalUnitPriceSet { shopMoney { amount currencyCode } } vendor } } } } } } }";
        
        return $query;
    }

    private function buildPendingFulfillmentsQuery($filters, $columns, $groupBy, $aggregations)
    {
         // Fetch ALL orders from 2025 onwards (when your data starts based on screenshot)
         // Don't filter by fulfillment_status - let fulfillableQuantity do the filtering
         // This ensures we get every order and every line item
         $queryStr = "";
         
         // If generic search is added via filters, append it
         $searchQuery = $this->buildSearchQuery($filters, ['id', 'name', 'created_at']);
         if (!empty($searchQuery)) {
             $queryStr = $searchQuery;
         }

         // Fetch all orders from 2020 onwards to be absolutely sure
         if (empty($queryStr)) {
             $queryStr = "created_at:>=2020-01-01";
         }
         
         error_log("buildPendingFulfillmentsQuery - Using query: $queryStr");
         
         $args = "query: \"" . addslashes($queryStr) . "\"";
         
         // We fetch Orders -> LineItems -> Variant details
         $query = "query { orders($args) { edges { node { id name createdAt ";
         
         // Fetch Line Items with Nested Variant Data needed for the report
         // Required cols: product_title (from variant.product), variant_title, inventory_policy, inventory_quantity, vendor
         $query .= "lineItems { edges { node { ";
         $query .= "quantity fulfillableQuantity sku vendor variant { id title inventoryQuantity inventoryPolicy product { title } } ";
         $query .= "} } } ";
         
         $query .= "} } } }";
         
         error_log("buildPendingFulfillmentsQuery - Full Query: " . $query);
         error_log("buildPendingFulfillmentsQuery - Search String: " . $queryStr);
         
         return $query;
    }

    private function buildPayoutsQuery($filters, $columns, $groupBy, $aggregations)
    {
        // 1. Build Query
        // Map 'date' filter to 'issued_at' for search query builder
        $searchQuery = $this->buildSearchQuery($filters, ['id', 'status', 'issued_at']);
        
        $args = "first: 250";
        if (!empty($searchQuery)) {
             $args .= ", query: \"" . addslashes($searchQuery) . "\"";
        }

        // Bulk Operations require pagination arguments on connections (e.g. first: 250)
        // Wraps in shopifyPaymentsAccount -> id -> payouts
        $query = "query { shopifyPaymentsAccount { id payouts($args) { edges { node { ";
        
        // Corrected Fields based on Standard ShopifyPaymentsPayout
        $fields = ['id', 'issuedAt', 'status'];
        
        // Payout Amount (Net)
        $fields[] = 'net { amount currencyCode }';

        // Summary for Gross/Fee calculations
        // Only including fields known to be valid in this API version
        $fields[] = 'summary { 
            adjustmentsFee { amount }
            adjustmentsGross { amount }
            chargesFee { amount }
            chargesGross { amount }
            reservedFundsFee { amount }
            reservedFundsGross { amount }
            retriedPayoutsFee { amount }
            retriedPayoutsGross { amount }
        }';
        
        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }



    private function buildDisputesQuery($filters, $columns, $groupBy, $aggregations)
    {
        // 1. Build Query (using initiated_at for date filtering)
        $searchQuery = $this->buildSearchQuery($filters, ['id', 'status', 'initiated_at']);
        
        $args = "first: 250";
        if (!empty($searchQuery)) {
             $args .= ", query: \"" . addslashes($searchQuery) . "\"";
        }

        // shopifyPaymentsAccount -> disputes
        // Note: We remove 'id' from shopifyPaymentsAccount as it sometimes triggers access issues if not strictly needed
        $query = "query { shopifyPaymentsAccount { disputes($args) { edges { node { ";
        
        $fields = ['id', 'initiatedAt', 'status', 'type', 'reason', 'amount { amount currencyCode }'];
        
        // Extended fields for 'Pending Disputes' detail view
        $fields[] = 'evidenceDueBy';
        $fields[] = 'evidenceSentOn';
        // Note: Accessing Order > Customer might be restricted depending on granular permissions, 
        // but read_orders + read_customers should be enough.
        $fields[] = 'order { name createdAt email customer { displayName } }';

        $query .= implode(' ', $fields);
        $query .= " } } } } }";
        
        return $query;
    }

    public function executeReport($reportId, $runtimeConfig = [])
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
        
        // Merge runtime config overrides
        if (!empty($runtimeConfig)) {
            error_log("ReportBuilderService::executeReport - Runtime Config: " . json_encode($runtimeConfig));
            // ... (rest of logic)
            if (isset($runtimeConfig['filters'])) {
                $config['filters'] = $runtimeConfig['filters'];
            }
            // ...
        }
        
        // FIX: Force Strip Date Filters here for Total Inventory
        if (($config['dataset']??'') === 'total_inventory_summary') {
             $config['filters'] = array_values(array_filter($config['filters'] ?? [], function($f) {
                 return !in_array($f['field'], ['created_at', 'updated_at']);
             }));
             error_log("ReportBuilderService::executeReport - STRIPPED DATE FILTERS for Inventory Report: " . $config['dataset']);
        }

        $this->activeFilters = $config['filters'] ?? [];
        error_log("ReportBuilderService::executeReport - Active Filters Set: " . json_encode($this->activeFilters));
        error_log("ReportBuilderService::executeReport - Dataset: " . ($config['dataset'] ?? 'NONE'));

        $query = $this->buildQuery($config);
        
        // DEBUG: Save query
        file_put_contents(__DIR__ . '/../../debug_query.txt', "Dataset: " . ($config['dataset']??'unknown') . "\nQuery:\n$query\n");

        error_log("ReportBuilderService::executeReport - Generated Query: {$query}");

        // Fallback: some stores/apps cannot run Bulk Ops due to protected customer data restrictions.
        // For small summary/chart datasets we can run a normal GraphQL query and save results directly.
        $dataset = $config['dataset'] ?? 'orders';
        $directDatasets = ['browser_share']; // Moved sales_summary and aov_time to Bulk for full history support
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

        if ($dataset === 'browser_share') {
             // ...
             // Not implemented fully yet in this restoration as it wasn't the focus, 
             // but keeping method stub or basic if previous code had it.
             return []; 
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

        if (in_array($currentStatus, ['FAILED', 'EXPIRED', 'CANCELED'])) {
            $errorCode = $node['errorCode'] ?? 'Unknown Error';
            $errorMsg = "Bulk Operation failed. Status: $currentStatus, Code: $errorCode";
            
            // Graceful handling for Shopify Payments restrictions (store doesn't have it enabled)
            if ($errorCode === 'ACCESS_DENIED' && in_array($operation['operation_type'], ['payouts', 'monthly_disputes', 'pending_disputes'])) {
                 error_log("ReportBuilderService::processBulkOperationResult - ACCESS_DENIED for {$operation['operation_type']}. Assuming feature disabled on store. Returning empty set.");
                 $this->saveResult($reportId, []); // Save empty result
                 $bulkOpModel->update($operation['id'], ['status' => 'COMPLETED']); // Mark as completed to stop retries
                 return true;
            }

            if ($errorCode === 'ACCESS_DENIED') {
                $grantedScopes = $this->shopifyService->getGrantedAccessScopes();
                $grantedStr = is_array($grantedScopes) ? implode(', ', $grantedScopes) : 'Could not fetch scopes';
                $opType = $operation['operation_type'] ?? '';
                
                $neededScope = '';
                if (in_array($opType, ['monthly_disputes', 'pending_disputes', 'payouts'])) {
                    $neededScope = 'read_shopify_payments_disputes';
                } elseif ($opType === 'markets') {
                    $neededScope = 'read_markets';
                }
                
                $hasScope = $neededScope && is_array($grantedScopes) && in_array($neededScope, $grantedScopes);
                
                if ($neededScope && !$hasScope) {
                     $errorMsg .= ". Re-authorization required. Needed: $neededScope. Current: [$grantedStr]";
                } elseif ($neededScope === 'read_shopify_payments_disputes') {
                     // Scope present but denied -> Feature disabled
                     $errorMsg .= ". Access Denied even with correct scopes. Ensure 'Shopify Payments' is enabled and active on this store.";
                } else {
                     $errorMsg .= ". Access Denied. Check permissions. Scope '$neededScope' is present? " . ($hasScope ? 'Yes' : 'No');
                }
                error_log("ReportBuilderService - ACCESS_DENIED. Granted Scopes: $grantedStr");
            }
            
            error_log("ReportBuilderService::processBulkOperationResult - $errorMsg");
            throw new \Exception($errorMsg);
        }

        if ($currentStatus === 'COMPLETED' && isset($node['url'])) {
            // Download and process the file
            error_log("ReportBuilderService::processBulkOperationResult - COMPLETED, downloading: {$node['url']}");
            $fileData = $this->shopifyService->downloadBulkOperationFile($node['url']);
            
            if ($fileData) {
                error_log("ReportBuilderService::processBulkOperationResult - Data downloaded, length: " . strlen($fileData));
                $this->processBulkData($operation, $fileData, $reportId, $operation['operation_type']);
            } else {
                error_log("ReportBuilderService::processBulkOperationResult - Failed to download file data");
            }
        }

        return $currentStatus === 'COMPLETED';
    }

    private function processBulkData($operation, $fileData, $reportId = null, $dataset = 'orders')
    {
        error_log("ReportBuilderService::processBulkData - Starting with " . strlen($fileData) . " bytes, Active Filters: " . json_encode($this->activeFilters) . " Dataset: $dataset");
        
        // Parse NDJSON file
        $lines = explode("\n", trim($fileData));
        
        // DEBUG: Save raw strings - ALL LINES to see complete data
        file_put_contents(__DIR__ . '/../../debug_bulk_raw.txt', $fileData);
        error_log("ReportBuilderService::processBulkData - Saved " . count($lines) . " lines to debug file");

        // FEATURE FIX: Pending Fulfillments should show ALL open orders regardless of date range.
        if ($dataset === 'pending_fulfillment_by_variant' || $dataset === 'total_inventory_summary') {
            $this->activeFilters = array_filter($this->activeFilters, function($f) {
                return !in_array($f['field'], ['created_at', 'updated_at']);
            });
            error_log("ReportBuilderService::processBulkData - Disabled date filters for Dataset: $dataset");
        }

        $data = [];
        
        $parentsMap = [];
        $orphanedChildren = []; // parentId => [child1, child2, ...]
        
        // Datasets that return Child Rows (flattened) instead of Aggregated Parents
        $isChildRowReport = in_array($dataset, ['line_items', 'transactions', 'products_variant', 'sales_by_variant', 'inventory_by_sku', 'pending_fulfillment_by_variant', 'payouts', 'monthly_disputes', 'pending_disputes', 'markets']);  

        $totalLinesProcessed = 0;
        $totalChildItems = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $decoded = json_decode($line, true);
            if (!$decoded) continue;

            $totalLinesProcessed++;
            
            // Handle Nested Data
            if (isset($decoded['__parentId'])) {
                $totalChildItems++;
                $parentId = $decoded['__parentId'];
                
                if ($isChildRowReport) {
                    // FLATTENING STRATEGY:
                    $parentContext = $parentsMap[$parentId] ?? [];
                    
                    // Pre-merge Context for Filtering (e.g. Line Items need Order Date)
                    if(isset($parentContext['name'])) $decoded['order_name'] = $parentContext['name'];
                    if(isset($parentContext['createdAt'])) {
                         $decoded['order_date'] = $parentContext['createdAt'];
                         // Fallback for Filtering: Use Parent Date if Child lacks it
                         if(!isset($decoded['createdAt'])) $decoded['createdAt'] = $parentContext['createdAt'];
                    }
                    // Merge other common Parent fields for filtering (crucial for Pending Fulfillments)
                    foreach(['financialStatus', 'fulfillmentStatus', 'status', 'tags', 'customer'] as $fKey) {
                        if (isset($parentContext[$fKey]) && !isset($decoded[$fKey])) {
                            $decoded[$fKey] = $parentContext[$fKey];
                        }
                    }

                    // Mapping for Pending Fulfillment by Variant (Standard Flattening first)
                    if ($dataset === 'pending_fulfillment_by_variant') {
                        // Extract fields from nested structure
                        $variant = $decoded['variant'] ?? [];
                        $product = $variant['product'] ?? [];
                        
                        $decoded['product_title'] = $product['title'] ?? 'Unknown Product';
                        $decoded['variant_title'] = $variant['title'] ?? $decoded['title'] ?? 'Unknown Variant';
                        $decoded['inventory_policy'] = $variant['inventoryPolicy'] ?? '';
                        $decoded['inventory_quantity'] = (int)($variant['inventoryQuantity'] ?? 0);
                        
                        // Preserve vendor field (comes directly from lineItem)
                        // vendor is already in $decoded from GraphQL response
                        
                        // Ensure order_name and order_date are preserved from parent context
                        // (already merged in lines 803-805, but we keep them explicitly)
                        
                        // Use fulfillableQuantity (remaining) if available, otherwise total quantity
                        $pendingQty = 0;
                        if (isset($decoded['fulfillableQuantity']) && (int)$decoded['fulfillableQuantity'] > 0) {
                            $pendingQty = (int)$decoded['fulfillableQuantity'];
                        } else {
                            $pendingQty = (int)($decoded['quantity'] ?? 0);
                        }
                        
                        $decoded['quantity_pending_fulfillment'] = $pendingQty;
                        $decoded['variant_id'] = $variant['id'] ?? $decoded['sku'] ?? 'unknown'; // Grouping Key
                        
                        // Debug: Log ALL items to see what's being filtered
                        error_log("Pending Fulfillment Row (qty=$pendingQty, fulfillable=" . ($decoded['fulfillableQuantity'] ?? 'N/A') . "): " .
                                  "order_date=" . ($decoded['order_date'] ?? 'MISSING') . 
                                  ", order_name=" . ($decoded['order_name'] ?? 'MISSING') . 
                                  ", vendor=" . ($decoded['vendor'] ?? 'MISSING') . 
                                  ", product=" . ($decoded['product_title'] ?? 'MISSING'));
                        
                        // optimization: filter out 0 values immediately
                        if ($pendingQty <= 0) {
                            error_log("Pending Fulfillment Row FILTERED OUT (pendingQty=$pendingQty)");
                            continue;
                        }
                    }
                    
                    // Log summary for pending fulfillment
                    if ($dataset === 'pending_fulfillment_by_variant') {
                        static $pendingFulfillmentProcessed = 0;
                        static $pendingFulfillmentKept = 0;
                        $pendingFulfillmentProcessed++;
                        if ($pendingQty > 0) {
                            $pendingFulfillmentKept++;
                        }
                    }
                    
                    // Mapping for Inventory by SKU (Flattening Variants)
                    if ($dataset === 'inventory_by_sku') {
                        $decoded['product_title'] = $parentContext['title'] ?? 'Unknown Product';
                        $decoded['variant_title'] = $decoded['title'] ?? ''; 
                        $decoded['product_type'] = $parentContext['productType'] ?? '';
                        $decoded['vendor'] = $parentContext['vendor'] ?? '';
                        $decoded['status'] = $parentContext['status'] ?? '';
                        $decoded['image'] = $decoded['image']['url'] ?? ($parentContext['featuredImage']['url'] ?? '');
                        // Pass through dates for filtering (both styles for safety)
                        $decoded['createdAt'] = $parentContext['createdAt'] ?? '';
                        $decoded['updatedAt'] = $parentContext['updatedAt'] ?? '';
                        $decoded['created_at'] = $parentContext['createdAt'] ?? '';
                        $decoded['updated_at'] = $parentContext['updatedAt'] ?? '';
                        
                        $qty = (int)($decoded['inventoryQuantity'] ?? 0);
                        $price = (float)($decoded['price'] ?? 0);
                        
                        $costStats = $decoded['inventoryItem']['unitCost'] ?? [];
                        $costAmount = $costStats['amount'] ?? '';
                        $cost = !empty($costAmount) && is_numeric($costAmount) ? (float)$costAmount : 0.0;
                        $currency = $costStats['currencyCode'] ?? 'INR'; 
                        
                        $decoded['total_quantity'] = $qty;
                        $decoded['total_inventory_value'] = [
                            'amount' => number_format($qty * $price, 2, '.', ''),
                            'currencyCode' => $currency
                        ];
                        $decoded['total_inventory_cost'] = [
                            'amount' => number_format($qty * $cost, 2, '.', ''),
                            'currencyCode' => $currency
                        ];
                        $decoded['total_variants'] = 1; // It's a single SKU
                        
                        // Add price field (for Variant costs report)
                        $decoded['price'] = [
                            'amount' => number_format($price, 2, '.', ''),
                            'currencyCode' => $currency
                        ];
                        
                        // Add cost and margin fields (for Variant costs report)
                        $hasCost = !empty($costAmount) && is_numeric($costAmount) && $cost > 0;
                        if ($hasCost) {
                            $unitMargin = $price - $cost;
                            $unitMarginPercent = $cost > 0 ? (($unitMargin / $cost) * 100) : 0.0;
                            
                            $decoded['cost'] = [
                                'amount' => number_format($cost, 2, '.', ''),
                                'currencyCode' => $currency
                            ];
                            $decoded['unit_margin'] = [
                                'amount' => number_format($unitMargin, 2, '.', ''),
                                'currencyCode' => $currency
                            ];
                            $decoded['unit_margin_percent'] = number_format($unitMarginPercent, 2, '.', '') . '%';
                        } else {
                            $decoded['cost'] = '-';
                            $decoded['unit_margin'] = '-';
                            $decoded['unit_margin_percent'] = '-';
                        }
                    }

                    // Payouts Flattening
                    if ($dataset === 'payouts') {
                        // Skip parent node (Account) which lacks 'net', or malformed rows
                        if (!isset($decoded['net'])) {
                            continue;
                        }

                        try {
                            $summary = $decoded['summary'] ?? [];
                            $totalGross = 0.0;
                            $totalFee = 0.0;
                            
                            // Sum Gross
                            $grossFields = ['adjustmentsGross', 'chargesGross', 'reservedFundsGross', 'retriedPayoutsGross'];
                            foreach ($grossFields as $f) {
                                if (isset($summary[$f]['amount'])) {
                                    $totalGross += (float)$summary[$f]['amount'];
                                }
                            }

                            // Sum Fees
                            $feeFields = ['adjustmentsFee', 'chargesFee', 'reservedFundsFee', 'retriedPayoutsFee'];
                            foreach ($feeFields as $f) {
                                if (isset($summary[$f]['amount'])) {
                                    $totalFee += (float)$summary[$f]['amount'];
                                }
                            }

                            // Net (Payout Amount)
                            $netVal = (float)($decoded['net']['amount'] ?? 0);
                            $currency = $decoded['net']['currencyCode'] ?? '';

                            $decoded['total_gross'] = [
                                'amount' => number_format($totalGross, 2, '.', ''),
                                'currencyCode' => $currency
                            ];
                            $decoded['total_fee'] = [
                                'amount' => number_format($totalFee, 2, '.', ''),
                                'currencyCode' => $currency
                            ];
                            $decoded['total_net'] = [
                                'amount' => number_format($netVal, 2, '.', ''),
                                'currencyCode' => $currency
                            ];
                            $decoded['currency'] = $currency;
                            
                            // Map Date
                            $payoutDate = $decoded['issuedAt'] ?? '';
                            $decoded['date'] = $payoutDate;
                            $decoded['created_at'] = $payoutDate; 
                            $decoded['createdAt'] = $payoutDate;
                        } catch (\Throwable $e) {
                            error_log("ReportBuilderService::processBulkData - Payout Processing Error: " . $e->getMessage());
                            continue;
                        }
                    }

                    // Monthly Disputes Flattening (Raw)
                    if ($dataset === 'monthly_disputes') {
                        // Skip parent (Account)
                        if (!isset($decoded['reason'])) continue; // Basic check for dispute node

                        $decoded['amount_val'] = (float)($decoded['amount']['amount'] ?? 0);
                        $decoded['currency_code'] = $decoded['amount']['currencyCode'] ?? '';
                        $decoded['date'] = $decoded['initiatedAt'] ?? '';
                        // Initialize aggregation keys if needed, but we'll do that in post-processing
                    }

                    // Pending Disputes Flattening (Detailed)
                    if ($dataset === 'pending_disputes') {
                         if (!isset($decoded['reason'])) continue;

                         $decoded['amount_val'] = (float)($decoded['amount']['amount'] ?? 0);
                         $decoded['currency'] = $decoded['amount']['currencyCode'] ?? '';
                         
                         $decoded['initiated_at'] = $decoded['initiatedAt'] ?? '';
                         $decoded['date'] = $decoded['initiated_at']; // For standard sorting
                         
                         $decoded['evidence_due_by'] = $decoded['evidenceDueBy'] ?? '';
                         $decoded['evidence_sent_on'] = $decoded['evidenceSentOn'] ?? '';
                         
                         $decoded['order_name'] = $decoded['order']['name'] ?? '';
                         $decoded['order_date'] = $decoded['order']['createdAt'] ?? '';
                         $decoded['email'] = $decoded['order']['email'] ?? '';
                         $decoded['customer_name'] = $decoded['order']['customer']['displayName'] ?? '';
                         
                         $decoded['total_amount'] = [
                             'amount' => number_format($decoded['amount_val'], 2, '.', ''),
                             'currencyCode' => $decoded['currency']
                         ];
                    }

                    // Mapping for Markets
                    if ($dataset === 'markets') {
                         $decoded['market_name'] = $parentContext['name'] ?? 'Unknown Market';
                         $decoded['is_primary'] = ($parentContext['primary'] ?? false) ? 'Yes' : 'No';
                         $decoded['is_enabled'] = ($parentContext['enabled'] ?? false) ? 'Yes' : 'No';
                         
                         $decoded['region'] = $decoded['name'] ?? '';
                         $decoded['country_code'] = $decoded['code'] ?? '';
                    }

                    if ($this->matchesFilters($decoded)) {
                        // Debug: Log final data structure
                        static $dataLogCount = 0;
                        if ($dataLogCount < 1 && $dataset === 'inventory_by_sku') {
                            error_log("inventory_by_sku DEBUG - Final row price field: " . json_encode($decoded['price'] ?? 'NOT SET'));
                            error_log("inventory_by_sku DEBUG - Final row cost field: " . json_encode($decoded['cost'] ?? 'NOT SET'));
                            $dataLogCount++;
                        }
                        $data[] = $decoded;
                    }
                } else {
                    // AGGREGATION STRATEGY:
                    if (isset($parentsMap[$parentId])) {
                        if ($dataset === 'customers' || $dataset === 'customers_by_country') {
                             $this->aggregateCustomerOrder($parentsMap[$parentId], $decoded);
                        } elseif (strpos($dataset, 'product') !== false || strpos($dataset, 'inventory') !== false) {
                             // Aggregate Variants/Images into Product
                             // If it looks like a variant (has inventoryQuantity or price or sku)
                             if (isset($decoded['inventoryQuantity']) || isset($decoded['price']) || isset($decoded['sku'])) {
                                 if (!isset($parentsMap[$parentId]['variants'])) {
                                     $parentsMap[$parentId]['variants'] = [];
                                 }
                                 $parentsMap[$parentId]['variants'][] = $decoded;
                             }
                             // If it looks like an image (has url)
                             if (isset($decoded['url'])) {
                                  // featuredImage might come as child if unconnected? usually embedded.
                             }
                        }
                    } else {
                        if (!isset($orphanedChildren[$parentId])) {
                            $orphanedChildren[$parentId] = [];
                        }
                        $orphanedChildren[$parentId][] = $decoded;
                    }
                }
            } else {
                // Parent Node (e.g. Customer, Order, Product)
                
                // Specific Logic for Summary Datasets removed here to allow Aggregation (ParentsMap)
                /* if ($dataset === 'sales_summary' || $dataset === 'aov_time') { ... } */

                $id = $decoded['id'] ?? null;
                
                if ($isChildRowReport) {
                    // Store parent just for context, don't add to $data yet
                    // But if the parent ITSELF is the row (unlikely for nested query), we skip.
                    if ($id) {
                         $parentsMap[$id] = $decoded;
                    }
                } else {
                    // Standard Report (Parent is the row)
                    // Flatten Country
                    if (isset($decoded['defaultAddress']['country'])) {
                        $decoded['country'] = $decoded['defaultAddress']['country'];
                    } elseif (isset($decoded['addresses']) && !empty($decoded['addresses'])) {
                        // Fallback: Robust check for country in addresses
                        $addrConn = $decoded['addresses'];
                        
                        // Case 1: Connection (edges -> node)
                        if (isset($addrConn['edges'][0]['node']['country'])) {
                             $decoded['country'] = $addrConn['edges'][0]['node']['country'];
                        } 
                        // Case 2: List of Objects ( [ { country: "..." } ] )
                        elseif (isset($addrConn[0]['country'])) {
                             $decoded['country'] = $addrConn[0]['country'];
                             error_log("ReportBuilderService: Found Country in addresses list for ID " . ($decoded['id']??'?') . " -> " . $addrConn[0]['country']);
                        }
                        // Case 3: Single Object ( { country: "..." } ) - rare for plural field but possible in some schemas
                        elseif (isset($addrConn['country'])) {
                             $decoded['country'] = $addrConn['country'];
                        }
                    } else {
                        // Debug: Addresses field missing or empty
                        // error_log("ReportBuilderService: No addresses found for ID " . ($decoded['id']??'?'));
                    }
                    
                    if (!isset($decoded['country']) && isset($decoded['shippingAddress']['country'])) {
                        $decoded['country'] = $decoded['shippingAddress']['country'];
                    }

                    if (!isset($decoded['country']) && isset($decoded['shippingAddress']['country'])) {
                        $decoded['country'] = $decoded['shippingAddress']['country'];
                    }

                    if (isset($decoded['featuredImage']['url'])) {
                        $decoded['image'] = $decoded['featuredImage']['url'];
                    }
                    if (isset($decoded['inventoryItem']['variant']['image']['url'])) {
                        $decoded['image'] = $decoded['inventoryItem']['variant']['image']['url'];
                    }

                    if ($id) {
                        // Init stats
                        if (!isset($decoded['orders_count'])) $decoded['orders_count'] = 0;
                        if (!isset($decoded['total_spent'])) $decoded['total_spent'] = ['amount' => '0.00', 'currencyCode' => ''];
                        
                        $parentsMap[$id] = $decoded;
                        
                        // Process orphans
                        if (isset($orphanedChildren[$id])) {
                            foreach ($orphanedChildren[$id] as $child) {
                                if ($dataset === 'customers' || $dataset === 'customers_by_country') {
                                    $this->aggregateCustomerOrder($parentsMap[$id], $child);
                                } elseif (strpos($dataset, 'product') !== false || strpos($dataset, 'inventory') !== false) {
                                     // Aggregate Variants/Images into Product
                                     if (isset($child['inventoryQuantity']) || isset($child['price']) || isset($child['sku'])) {
                                         if (!isset($parentsMap[$id]['variants'])) {
                                             $parentsMap[$id]['variants'] = [];
                                         }
                                         $parentsMap[$id]['variants'][] = $child;
                                     }
                                }
                            }
                            unset($orphanedChildren[$id]);
                        }
                    } else {
                        // Non-ID node?
                        $data[] = $decoded;
                    }
                }
            }
        }
        
        // Log summary of what was processed
        error_log("ReportBuilderService::processBulkData - Processed $totalLinesProcessed total lines, $totalChildItems child items for dataset: $dataset");
        if ($dataset === 'pending_fulfillment_by_variant') {
            error_log("ReportBuilderService::processBulkData - Pending Fulfillment: Processed line items, kept " . count($data) . " items with qty > 0");
        }
        
        // Post-Processing for Summary Datasets (Now using Aggregated Parents)
        if ($dataset === 'sales_summary') {
            // Aggregate all rows into one summary
            $totalOrders = 0;
            $totalSales = 0.0;
            $currency = '';
            
            // Iterate ParentsMap (Orders) instead of raw data, to allow matchesFilters to see Children
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;

            foreach ($sourceData as $row) {
                 // Deep Filter Check (now that we have children aggregated)
                 if (!$this->matchesFilters($row)) continue;

                 $totalOrders++;
                 
                 $amount = 0;
                 if (isset($row['totalPriceSet']['shopMoney']['amount'])) {
                     $amount = $row['totalPriceSet']['shopMoney']['amount'];
                     $currency = $row['totalPriceSet']['shopMoney']['currencyCode'] ?? $currency;
                 }
                 
                 $totalSales += (float)$amount;
            }
            
            $summary = [[
                'total_orders' => $totalOrders,
                'total_gross_sales' => ['amount' => sprintf('%.2f', $totalSales), 'currencyCode' => $currency],
                'total_discounts' => ['amount' => '0.00', 'currencyCode' => $currency],
                'total_refunds' => ['amount' => '0.00', 'currencyCode' => $currency],
                'total_net_sales' => ['amount' => sprintf('%.2f', $totalSales), 'currencyCode' => $currency],
                'total_taxes' => ['amount' => '0.00', 'currencyCode' => $currency],
                'total_shipping' => ['amount' => '0.00', 'currencyCode' => $currency],
                'total_sales' => ['amount' => sprintf('%.2f', $totalSales), 'currencyCode' => $currency],
                'total_cost_of_goods_sold' => ['amount' => '0.00', 'currencyCode' => $currency],
                'total_gross_margin' => ['amount' => '0.00', 'currencyCode' => $currency],
            ]];
            $data = $summary; // Replace data with summary
        } elseif ($dataset === 'products_by_type' || $dataset === 'total_inventory_summary') {
            // Aggregate Products by Type
            $byType = [];
            $grandTotal = [
                'products' => 0, // Track total products
                'variants' => 0,
                'quantity' => 0,
                'value' => 0.0,
                'cost' => 0.0
            ];
            $currency = '';

            // Use parentsMap (products)
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;

            foreach ($sourceData as $product) {
                 if (!$this->matchesFilters($product)) continue;
                 
                 $grandTotal['products']++;

                 $type = $product['productType'] ?? 'Unknown';
                 if (trim($type) === '') $type = 'Unknown';

                 if (!isset($byType[$type])) {
                     $byType[$type] = [
                         'variants' => 0,
                         'quantity' => 0,
                         'value' => 0.0,
                         'cost' => 0.0,
                         'image' => null
                     ];
                 }

                 // Capture first image found for this type
                 if (!$byType[$type]['image'] && isset($product['image'])) {
                     $byType[$type]['image'] = $product['image'];
                 }


                 if (!$byType[$type]['image'] && isset($product['featuredImage']['url'])) {
                     $byType[$type]['image'] = $product['featuredImage']['url'];
                 }

                 // Process Nested Variants (Handle both bulk JSONL and direct GraphQL structure)
                 $variants = [];
                 if (isset($product['variants']['edges'])) {
                     foreach($product['variants']['edges'] as $edge) {
                         $variants[] = $edge['node'];
                     }
                 } elseif (isset($product['variants']) && is_array($product['variants'])) {
                      $variants = $product['variants'];
                 }
                 
                 // If no variants found, check if mapped via parentsMap logic
                 if (empty($variants) && isset($product['variants_count'])) {
                     // Check if parentsMap has variants attached via orphan reconciliation
                     if (isset($product['variants']) && is_array($product['variants'])) {
                          // Already handled above
                     }
                 }
                 
                foreach ($variants as $v) {
                     $qty = (int)($v['inventoryQuantity'] ?? 0);
                     // CLEANUP: Remove commas to ensure is_numeric works
                     $priceStr = str_replace(',', '', $v['price'] ?? 0);
                     $costStr = str_replace(',', '', $v['inventoryItem']['unitCost']['amount'] ?? 0);

                     $price = is_numeric($priceStr) ? (float)$priceStr : 0.0;
                     $cost = is_numeric($costStr) ? (float)$costStr : 0.0;
                     
                     if (is_nan($price)) $price = 0.0;
                     if (is_nan($cost)) $cost = 0.0;
                     
                     $byType[$type]['variants']++;
                     $byType[$type]['quantity'] += $qty;
                     $byType[$type]['value'] += ($price * $qty);
                     $byType[$type]['cost'] += ($cost * $qty);
                     
                     $grandTotal['variants']++;
                     $grandTotal['quantity'] += $qty;
                     $grandTotal['value'] += ($price * $qty);
                     $grandTotal['cost'] += ($cost * $qty);
                     
                     if (!$currency && isset($v['priceCurrency'])) $currency = $v['priceCurrency'];
                 }
                 
                 if (!$currency && isset($product['priceRangeV2']['minVariantPrice']['currencyCode'])) {
                     $currency = $product['priceRangeV2']['minVariantPrice']['currencyCode'];
                 }
            }
            if (!$currency) $currency = 'INR';

            if ($dataset === 'total_inventory_summary') {
                $gtValue = $grandTotal['value'];
                $gtCost = $grandTotal['cost'];
                
                // PARANOID CHECK: Log the raw values
                error_log("ReportBuilderService::total_inventory_summary - Raw Value: " . json_encode($gtValue) . ", Raw Cost: " . json_encode($gtCost));

                // Force numeric type
                if (!is_numeric($gtValue)) $gtValue = 0.0;
                if (!is_numeric($gtCost)) $gtCost = 0.0;
                
                $gtValue = (float)$gtValue;
                $gtCost = (float)$gtCost;

                if (is_nan($gtValue) || is_infinite($gtValue)) $gtValue = 0.0;
                if (is_nan($gtCost) || is_infinite($gtCost)) $gtCost = 0.0;

                $data = [[
                    'total_products' => $grandTotal['products'],
                    'total_variants' => $grandTotal['variants'],
                    'total_quantity' => number_format($grandTotal['quantity']),
                    'total_inventory_value' => ['amount' => number_format($gtValue, 2, '.', ''), 'currencyCode' => $currency],
                    'total_inventory_cost' => ['amount' => number_format($gtCost, 2, '.', ''), 'currencyCode' => $currency],
                ]];
            } else {
                uasort($byType, function($a, $b) {
                    return $b['value'] <=> $a['value'];
                });

                $rows = [];
                foreach ($byType as $type => $stats) {
                    $rows[] = [
                        'product_type' => $type,
                        'image' => $stats['image'],
                        'total_variants' => $stats['variants'],
                        'total_quantity' => number_format($stats['quantity']),
                        'total_inventory_value' => ['amount' => number_format($stats['value'], 2, '.', ''), 'currencyCode' => $currency],
                        'total_inventory_cost' => ['amount' => number_format($stats['cost'], 2, '.', ''), 'currencyCode' => $currency],
                    ];
                }

                // TOTAL Row
                $rows[] = [
                    'product_type' => 'TOTAL',
                    'image' => '',
                    'total_variants' => $grandTotal['variants'],
                    'total_quantity' => number_format($grandTotal['quantity']),
                    'total_inventory_value' => ['amount' => number_format($grandTotal['value'], 2, '.', ''), 'currencyCode' => $currency],
                    'total_inventory_cost' => ['amount' => number_format($grandTotal['cost'], 2, '.', ''), 'currencyCode' => $currency],
                ];
                
                $data = $rows;
            }
        } elseif ($dataset === 'monthly_disputes') {
             // Aggregate Disputes by Month, Status, Type, Reason
             $grouped = [];
             
             foreach ($data as $row) {
                  $date = new \DateTime($row['initiatedAt']);
                  $month = $date->format('Y-m'); // Group by Month
                  $monthLabel = $date->format('F Y'); // "January 2024"
                  
                  $status = $row['status'] ?? 'Unknown';
                  $type = $row['type'] ?? 'Unknown';
                  $reason = $row['reason'] ?? 'Unknown';
                  
                  $key = implode('|', [$month, $status, $type, $reason]);
                  
                  if (!isset($grouped[$key])) {
                      $grouped[$key] = [
                          'month_val' => $month, // For sorting
                          'month_initiated_at' => $monthLabel,
                          'status' => $status,
                          'type' => $type,
                          'reason' => $reason,
                          'total_disputes' => 0,
                          'total_amount_val' => 0.0,
                          'currency' => $row['currency_code'] ?? 'USD'
                      ];
                  }
                  
                  $grouped[$key]['total_disputes']++;
                  $grouped[$key]['total_amount_val'] += ($row['amount_val'] ?? 0);
             }
             
             // Sort by Month Desc
             uasort($grouped, function($a, $b) {
                 return $b['month_val'] <=> $a['month_val'];
             });
             
             $rows = [];
             foreach ($grouped as $g) {
                 $rows[] = [
                     'month_initiated_at' => $g['month_initiated_at'],
                     'status' => $g['status'],
                     'type' => $g['type'],
                     'reason' => $g['reason'],
                     'total_disputes' => $g['total_disputes'],
                     'total_amount' => [
                         'amount' => number_format($g['total_amount_val'], 2, '.', ''),
                         'currencyCode' => $g['currency']
                     ]
                 ];
             }
             $data = $rows;
             
        } elseif ($dataset === 'pending_fulfillment_by_variant') {
             // Aggregate by Variant ID
             $byVariant = [];
             
             foreach ($data as $row) {
                 // Change: include order name in grouping key to show individual orders separately
                 $vid = ($row['order_name'] ?? 'unknown') . '_' . ($row['variant_id'] ?? $row['sku'] ?? 'unknown');
                 
                 if (!isset($byVariant[$vid])) {
                     $byVariant[$vid] = [
                          'order_date' => $row['order_date'] ?? '',
                          'order_name' => $row['order_name'] ?? '',
                          'vendor' => $row['vendor'] ?? '',
                         'product_title' => $row['product_title'],
                         'variant_title' => $row['variant_title'],
                         'inventory_policy' => strtolower($row['inventory_policy']), // 'deny' or 'continue'
                         'inventory_quantity' => $row['inventory_quantity'],
                         'quantity_pending_fulfillment' => 0
                     ];
                 }
                 
                 // Accumulate pending quantity
                 $byVariant[$vid]['quantity_pending_fulfillment'] += $row['quantity_pending_fulfillment'];
             }
             
             // Filter out 0 values
             $filteredData = [];
             $preCount = count($byVariant);
             foreach ($byVariant as $item) {
                 $pending = (int)($item['quantity_pending_fulfillment'] ?? 0);
                 if ($pending > 0) {
                     $item['quantity_pending_fulfillment'] = $pending; // Ensure it's int
                     $filteredData[] = $item;
                 }
             }
             
             $postCount = count($filteredData);
             error_log("ReportBuilderService - Pending Fulfillments Filter: {$preCount} -> {$postCount}");
             
             $data = array_values($filteredData);

        } elseif ($dataset === 'products_vendor') {
            // Aggregate Products by Vendor
            $byVendor = [];
            $grandTotal = [
                'products' => 0,
                'variants' => 0,
                'quantity' => 0,
                'value' => 0.0,
                'cost' => 0.0
            ];
            $currency = '';

            // Use parentsMap (products)
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;

            foreach ($sourceData as $product) {
                 if (!$this->matchesFilters($product)) continue;

                 $vendor = $product['vendor'] ?? 'Unknown';
                 if (trim($vendor) === '') $vendor = 'Unknown';

                 if (!isset($byVendor[$vendor])) {
                     $byVendor[$vendor] = [
                         'products' => 0,
                         'variants' => 0,
                         'quantity' => 0,
                         'value' => 0.0,
                         'cost' => 0.0,
                         'image' => null
                     ];
                 }

                 // Capture first image found for this vendor
                 if (!$byVendor[$vendor]['image'] && isset($product['image'])) {
                     $byVendor[$vendor]['image'] = $product['image'];
                 }

                 $byVendor[$vendor]['products']++;
                 $grandTotal['products']++;

                 // Process Nested Variants
                 $variants = [];
                 if (isset($product['variants']['edges'])) {
                     foreach($product['variants']['edges'] as $edge) {
                         $variants[] = $edge['node'];
                     }
                 } elseif (isset($product['variants']) && is_array($product['variants'])) {
                      $variants = $product['variants'];
                 }
                 
                 foreach ($variants as $v) {
                     $qty = (int)($v['inventoryQuantity'] ?? 0);
                     $price = (float)($v['price'] ?? 0);
                     $cost = (float)($v['inventoryItem']['unitCost']['amount'] ?? 0);
                     
                     $byVendor[$vendor]['variants']++;
                     $byVendor[$vendor]['quantity'] += $qty;
                     $byVendor[$vendor]['value'] += ($price * $qty);
                     $byVendor[$vendor]['cost'] += ($cost * $qty);
                     
                     $grandTotal['variants']++;
                     $grandTotal['quantity'] += $qty;
                     $grandTotal['value'] += ($price * $qty);
                     $grandTotal['cost'] += ($cost * $qty);
                     
                     if (!$currency && isset($v['priceCurrency'])) $currency = $v['priceCurrency'];
                 }
                 
                 if (!$currency && isset($product['priceRangeV2']['minVariantPrice']['currencyCode'])) {
                     $currency = $product['priceRangeV2']['minVariantPrice']['currencyCode'];
                 }
            }
            if (!$currency) $currency = 'INR';

            uasort($byVendor, function($a, $b) {
                return $b['quantity'] <=> $a['quantity']; 
            });

            $rows = [];
            foreach ($byVendor as $vendor => $stats) {
                $rows[] = [
                    'vendor' => $vendor,
                    'image' => $stats['image'],
                    'total_products' => $stats['products'],
                    'total_variants' => $stats['variants'],
                    'total_quantity' => number_format($stats['quantity']),
                    'total_inventory_value' => ['amount' => number_format($stats['value'], 2, '.', ''), 'currencyCode' => $currency],
                    'total_inventory_cost' => ['amount' => number_format($stats['cost'], 2, '.', ''), 'currencyCode' => $currency],
                ];
            }

            // TOTAL Row
            $rows[] = [
                'vendor' => 'TOTAL',
                'image' => '',
                'total_products' => $grandTotal['products'],
                'total_variants' => $grandTotal['variants'],
                'total_quantity' => number_format($grandTotal['quantity']),
                'total_inventory_value' => ['amount' => number_format($grandTotal['value'], 2, '.', ''), 'currencyCode' => $currency],
                'total_inventory_cost' => ['amount' => number_format($grandTotal['cost'], 2, '.', ''), 'currencyCode' => $currency],
            ];
            
            $data = $rows;
            
            $data = $rows;
            
        } elseif ($dataset === 'inventory_by_product') {
            // Aggregate Inventory by Product
            $rows = [];
            
            // Use parentsMap (products)
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;

            foreach ($sourceData as $product) {
                 if (!$this->matchesFilters($product)) continue;

                 $currency = '';
                 
                 $stats = [
                     'variants' => 0,
                     'quantity' => 0,
                     'value' => 0.0,
                     'cost' => 0.0
                 ];

                 // Process Nested Variants (Handle both bulk JSONL and direct GraphQL structure)
                 $variants = [];
                 if (isset($product['variants']['edges'])) {
                     foreach($product['variants']['edges'] as $edge) {
                         $variants[] = $edge['node'];
                     }
                 } elseif (isset($product['variants']) && is_array($product['variants'])) {
                      $variants = $product['variants'];
                 }
                 
                 // If no variants found, check if mapped via parentsMap logic
                 if (empty($variants) && isset($product['variants_count'])) {
                     // Maybe it was flattened? No, we are iterating parents.
                     // Just log if empty to debug
                     // error_log("Product " . $product['id'] . " has no variants found.");
                 }
                 
                 foreach ($variants as $v) {
                     $qty = (int)($v['inventoryQuantity'] ?? 0);
                     $price = (float)($v['price'] ?? 0);
                     $cost = (float)($v['inventoryItem']['unitCost']['amount'] ?? 0);
                     
                     $stats['variants']++;
                     $stats['quantity'] += $qty;
                     $stats['value'] += ($price * $qty);
                     $stats['cost'] += ($cost * $qty);
                     
                     if (!$currency && isset($v['priceCurrency'])) $currency = $v['priceCurrency'];
                 }
                 
                 if (!$currency && isset($product['priceRangeV2']['minVariantPrice']['currencyCode'])) {
                     $currency = $product['priceRangeV2']['minVariantPrice']['currencyCode'];
                 }
                 if (!$currency) $currency = 'INR';
                 
                 // FILTER: If no variants found, skip row (avoids fake rows for collections or malformed data)
                 if ($stats['variants'] === 0) continue;

                 $image = $product['image'] ?? ($product['featuredImage']['url'] ?? null);
                 $title = $product['title'] ?? 'Unknown Product';

                 $rows[] = [
                     'id' => $product['id'],
                     'product_title' => $title,
                     'image' => $image,
                     'total_variants' => $stats['variants'],
                     'total_quantity' => number_format($stats['quantity']),
                     'total_inventory_value' => ['amount' => number_format($stats['value'], 2, '.', ''), 'currencyCode' => $currency],
                     'total_inventory_cost' => ['amount' => number_format($stats['cost'], 2, '.', ''), 'currencyCode' => $currency]
                 ];
            }
            
            
            $data = $rows;
            
        } elseif ($dataset === 'customers_by_country') {
            // Aggregate Customers by Country
            $byCountry = [];
            $totalCustomers = 0;
            
            // Use parentsMap if populated (standard customers logic populates it), else data
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;
            
            // DEBUG: Dump first customer to file
            if (!empty($sourceData)) {
                $firstKey = array_key_first($sourceData);
                file_put_contents(__DIR__ . '/../../debug_customer_data.txt', print_r($sourceData[$firstKey], true));
            }

            foreach ($sourceData as $row) {
                if (!$this->matchesFilters($row)) continue;

                $country = $row['country'] ?? 'Unknown';
                if (trim($country) === '') $country = 'Unknown';
                
                // DATA TRACING
                error_log("TRACE ROW: ID " . ($row['id']??'null') . " | CountryRaw: '" . ($row['country']??'MISSING') . "' | Final: '$country'");

                if (!isset($byCountry[$country])) {
                    $byCountry[$country] = 0;
                }
                $byCountry[$country]++;
                $totalCustomers++;
            }
            ksort($byCountry);

            $rows = [];
            foreach ($byCountry as $country => $count) {
                $rows[] = [
                    'country' => $country,
                    'total_customers' => $count
                ];
            }

            // Append TOTAL row
            // We use 'TOTAL' in the first column key ('country')
            $rows[] = [
                'country' => 'TOTAL',
                'total_customers' => $totalCustomers
            ];

            $data = $rows;
        } elseif ($dataset === 'aov_time') {
             // Aggregate by Date
            $byDate = [];
            $sourceData = !empty($parentsMap) ? $parentsMap : $data;

            foreach ($sourceData as $node) {
                if (!$this->matchesFilters($node)) continue;

                $createdAt = $node['createdAt'] ?? null;
                $amount = $node['totalPriceSet']['shopMoney']['amount'] ?? null;
                if (!$createdAt || !is_numeric($amount)) continue;
                
                // Adjust date to day? 
                // Using UTC date from string is simpler for grouping
                $date = substr($createdAt, 0, 10);
                if (!isset($byDate[$date])) $byDate[$date] = ['sum' => 0.0, 'count' => 0];
                $byDate[$date]['sum'] += (float)$amount;
                $byDate[$date]['count']++;
            }
            ksort($byDate);
            $rows = [];
            foreach ($byDate as $date => $agg) {
                $avg = $agg['count'] > 0 ? ($agg['sum'] / $agg['count']) : 0.0;
                $rows[] = [
                    'date' => $date,
                    'average_order_value' => number_format($avg, 2, '.', ''),
                ];
            }
            $data = $rows;
        } elseif ($dataset === 'products_variant' || $dataset === 'sales_by_variant') {
             // Aggregate Line Items by Variant
             $byVariant = [];
             foreach ($data as $item) {
                 // Identify Variant
                 $varId = $item['variant']['id'] ?? 'N/A';
                 if ($varId === 'N/A' && isset($item['title'])) $varId = $item['title']; // Fallback

                 if (!isset($byVariant[$varId])) {
                     $pTitle = $item['variant']['product']['title'] ?? ($item['title'] ?? 'Unknown Product');
                     $vTitle = $item['variant']['title'] ?? ($item['title'] ?? '');
                     if ($vTitle === 'Default Title') $vTitle = 'Default Title'; // Keep explicit

                     $byVariant[$varId] = [
                         'product_title' => $pTitle,
                         'variant_title' => $vTitle,
                         'sku' => $item['sku'] ?? '',
                         'orders_unique' => [],
                         'net_quantity' => 0,
                         'gross_sales' => 0.0,
                         'discounts' => 0.0,
                         'returns' => 0.0,
                         'net_sales' => 0.0,
                         'tax' => 0.0,
                         'shipping' => 0.0,
                         'total_sales' => 0.0,
                         'currency' => ''
                     ];
                 }

                 // Metrics
                 $qty = (int)($item['quantity'] ?? 0);
                 $price = (float)($item['priceSet']['shopMoney']['amount'] ?? 0);
                 $currency = $item['priceSet']['shopMoney']['currencyCode'] ?? '';
                 $byVariant[$varId]['currency'] = $currency;

                 // Gross Sales = Price * Qty
                 $gross = $price * $qty;
                 
                 // Tax
                 $tax = 0.0;
                 if (isset($item['taxLines'])) {
                     foreach ($item['taxLines'] as $tl) {
                         $tax += (float)($tl['priceSet']['shopMoney']['amount'] ?? 0);
                     }
                 }

                 $byVariant[$varId]['net_quantity'] += $qty;
                 $byVariant[$varId]['gross_sales'] += $gross;
                 $byVariant[$varId]['tax'] += $tax;
                 // Assuming Net Sales = Gross (minus defaults) for now
                 // Total Sales = Gross + Tax
                 $byVariant[$varId]['total_sales'] += ($gross + $tax); 
                 
                 // Track Order ID for Count
                 $parentId = $item['__parentId'] ?? null;
                 if ($parentId) $byVariant[$varId]['orders_unique'][$parentId] = true;
             }

             // Format Output
             $rows = [];
             foreach ($byVariant as $vid => $agg) {
                 $agg['orders_count'] = count($agg['orders_unique']);
                 unset($agg['orders_unique']); // Cleanup
                 
                 // Format Money
                 $c = $agg['currency'];
                 $rows[] = [
                     'product_title' => $agg['product_title'],
                     'variant_title' => $agg['variant_title'],
                     'sku' => $agg['sku'],
                     'orders_count' => $agg['orders_count'],
                     'net_quantity' => $agg['net_quantity'],
                     'gross_sales' => ['amount' => number_format($agg['gross_sales'], 2, '.', ''), 'currencyCode' => $c],
                     'discounts' => ['amount' => number_format($agg['discounts'], 2, '.', ''), 'currencyCode' => $c],
                     'returns' => ['amount' => number_format($agg['returns'], 2, '.', ''), 'currencyCode' => $c],
                     'net_sales' => ['amount' => number_format($agg['gross_sales'] - $agg['discounts'] - $agg['returns'], 2, '.', ''), 'currencyCode' => $c],
                     'total_taxes' => ['amount' => number_format($agg['tax'], 2, '.', ''), 'currencyCode' => $c],
                     'total_shipping' => ['amount' => number_format($agg['shipping'], 2, '.', ''), 'currencyCode' => $c],
                     'total_sales' => ['amount' => number_format($agg['total_sales'], 2, '.', ''), 'currencyCode' => $c],
                 ];
             }
             $data = $rows;
        } elseif ($dataset === 'inventory_by_vendor') {
             // Aggregate by Vendor
             $byVendor = [];
             $sourceData = !empty($parentsMap) ? $parentsMap : $data;
             
             error_log("ReportBuilderService::inventory_by_vendor - Source Nodes: " . count($sourceData));

             foreach ($sourceData as $node) {
                 if (!$this->matchesFilters($node)) continue;
                 
                 $vendor = $node['vendor'] ?? 'Unknown Vendor';
                 if (!isset($byVendor[$vendor])) {
                     $byVendor[$vendor] = [
                         'vendor' => $vendor,
                         'total_variants' => 0,
                         'total_quantity' => 0,
                         'total_inventory_value' => 0.0,
                         'total_inventory_cost' => 0.0,
                         'currency' => 'INR' 
                     ];
                 }
                 
                 // Aggregate Variants
                 $variants = $node['variants'] ?? [];
                 
                 // Debug first few variants
                 static $debugVarCount = 0;
                 if ($debugVarCount++ < 3) {
                     error_log("ReportBuilderService::inventory_by_vendor - Node Vendor: $vendor, Variant Count: " . count($variants));
                 }
                 
                 foreach ($variants as $v) {
                     $qty = (int)($v['inventoryQuantity'] ?? 0);
                     $price = (float)($v['price'] ?? 0);
                     $costStats = $v['inventoryItem']['unitCost'] ?? [];
                     $cost = (float)($costStats['amount'] ?? 0);
                     $currency = $costStats['currencyCode'] ?? 'INR';
                     
                     if ($currency && $currency !== 'INR') $byVendor[$vendor]['currency'] = $currency; 
                     
                     $byVendor[$vendor]['total_variants']++;
                     $byVendor[$vendor]['total_quantity'] += $qty;
                     $byVendor[$vendor]['total_inventory_value'] += ($qty * $price);
                     $byVendor[$vendor]['total_inventory_cost'] += ($qty * $cost);
                 }
             }
             
             $rows = [];
             foreach ($byVendor as $vendor => $stats) {
                 $c = $stats['currency'];
                 $rows[] = [
                     'vendor' => $stats['vendor'],
                     'total_variants' => $stats['total_variants'],
                     'total_quantity' => $stats['total_quantity'],
                     'total_inventory_value' => ['amount' => number_format($stats['total_inventory_value'], 2, '.', ''), 'currencyCode' => $c],
                     'total_inventory_cost' => ['amount' => number_format($stats['total_inventory_cost'], 2, '.', ''), 'currencyCode' => $c],
                 ];
             }
             $data = $rows;
        }

        // For Standard Reports, add the Aggregated Parents to Data
        if (!$isChildRowReport && $dataset !== 'sales_summary' && $dataset !== 'aov_time' && $dataset !== 'customers_by_country' && $dataset !== 'products_by_type' && $dataset !== 'products_vendor' && $dataset !== 'inventory_by_product' && $dataset !== 'inventory_by_vendor') {
            error_log("ReportBuilderService::processBulkData - Merging " . count($parentsMap) . " parents into final data");
            foreach ($parentsMap as $p) {
                // Apply Filters to Parents (e.g. Order Date)
                if ($this->matchesFilters($p)) {
                     $data[] = $p;
                }
            }
        } else {
             error_log("ReportBuilderService::processBulkData - Child/Summary Report: " . count($data) . " rows extracted/aggregated");
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

    private function aggregateCustomerOrder(&$customer, $orderNode)
    {
         // DEBUG: Dump order node
         file_put_contents('c:\xampp\htdocs\report-pro\debug_dump.txt', "Order Node: " . print_r($orderNode, true) . "\n\n", FILE_APPEND);
         
         // Standard Aggregation (Child into Parent)
         // For Orders, "Child" might be LineItem or Transaction
         // For Customers, "Child" is Order
         
         // Generic check: where to put it?
         if (isset($orderNode['__parentId'])) {
             // Address Fallback (for Country)
             // Check if it has 'country' and NOT order fields
             if (isset($orderNode['country']) && !isset($orderNode['totalPriceSet']) && !isset($orderNode['quantity'])) {
                 if (empty($customer['country']) || $customer['country'] === 'Unknown') {
                     $customer['country'] = $orderNode['country'];
                 }
                 return;
             }

            // ORDER ADDRESS FALLBACK (Shipping)
            if (isset($orderNode['shippingAddress']['country'])) {
                if (empty($customer['country']) || $customer['country'] === 'Unknown') {
                     $customer['country'] = $orderNode['shippingAddress']['country'];
                }
            }
            // ORDER ADDRESS FALLBACK (Billing - Last Resort)
            if (isset($orderNode['billingAddress']['country'])) {
                if (empty($customer['country']) || $customer['country'] === 'Unknown') {
                     $customer['country'] = $orderNode['billingAddress']['country'];
                }
            }

            // RECURSIVE "CATCH-ALL" FALLBACK (If still unknown)
            if (empty($customer['country']) || $customer['country'] === 'Unknown') {
                $foundCountry = $this->findCountryRecursively($orderNode);
                if ($foundCountry && $foundCountry !== 'Unknown') {
                    $customer['country'] = $foundCountry;
                }
            }

             // It's a child node (LineItem, Transaction)
             // Determine type based on fields
             if (isset($orderNode['title']) && isset($orderNode['quantity'])) {
                 // LineItem
                 if (!isset($customer['line_items'])) $customer['line_items'] = [];
                 $customer['line_items'][] = $orderNode;
             } elseif (isset($orderNode['gateway']) || isset($orderNode['kind'])) {
                 // Transaction
                 if (!isset($customer['transactions'])) $customer['transactions'] = [];
                 $customer['transactions'][] = $orderNode;
             } elseif (isset($orderNode['id'])) {
                 // Unknown child (maybe order under customer)
                 // This matches original logic for Customer Report
                // PHP-Side Filtering for Customer's Orders
                // CRITICAL FIX: For Customer Reports, user expects "Total Spent" to be LIFETIME value.
                // The Global Report filters (e.g. "Last 30 Days") are for finding *which* customers to show.
                // We typically do NOT want to limit the summed orders to that same window (otherwise it's "Spend in Period").
                // To support true LTV, we exclude date filters from the Order Aggregation check.
                
                $orderFilters = [];
                foreach ($this->activeFilters as $af) {
                    if ($af['field'] !== 'created_at' && $af['field'] !== 'updated_at') {
                        $orderFilters[] = $af;
                    }
                }

                $matches = $this->matchesFilters($orderNode, $orderFilters);
                if (!$matches) return;
        
                // Aggregate Count
                $customer['orders_count'] = ($customer['orders_count'] ?? 0) + 1;
               
                // Aggregate Total Spent
                if (isset($orderNode['totalPriceSet']['shopMoney']['amount'])) {
                    $amount = (float)$orderNode['totalPriceSet']['shopMoney']['amount'];
                    $currentTotal = (float)($customer['total_spent']['amount'] ?? 0);
                    $currency = $orderNode['totalPriceSet']['shopMoney']['currencyCode'] ?? ($customer['total_spent']['currencyCode'] ?? '');
                    
                    $customer['total_spent'] = [
                        'amount' => number_format($currentTotal + $amount, 2, '.', ''),
                        'currencyCode' => $currency
                    ];
                }
             }
         }
    }

    private function matchesFilters($node, $overrideFilters = null)
    {
        $filtersToUse = $overrideFilters !== null ? $overrideFilters : $this->activeFilters;
        if (empty($filtersToUse)) return true;
        
        // ... (debug logic)
        static $debugCount = 0;
        $shouldLog = ($debugCount++ < 5);

        foreach ($filtersToUse as $filter) {
            $field = $filter['field'];
            $value = $filter['value'];
            $operator = $filter['operator'] ?? '=';

            // Support date aliases and Deep Filtering
            $nodeValue = null;
            
            if ($field === 'created_at') {
                $nodeValue = $node['createdAt'] ?? null;
            } elseif ($field === 'updated_at') {
                $nodeValue = $node['updatedAt'] ?? null;
            } elseif ($field === 'financial_status') {
                $nodeValue = $node['financialStatus'] ?? null;
            } elseif ($field === 'fulfillment_status') {
                $nodeValue = $node['fulfillmentStatus'] ?? null;
            } elseif ($field === 'product_type') {
                 // Deep Check: Scan Line Items
                 // If any line item matches, the Order matches.
                 $items = $node['line_items'] ?? [];
                 $matchFound = false;
                 foreach($items as $item) {
                     $pType = $item['variant']['product']['productType'] ?? '';
                     // Simple check: default operator '=' for product type usually
                     // But let's reuse logic below by picking the first match? No, needs ALL check logic.
                     // Quick implementation:
                     if ($operator === '=' && stripos($pType, $value) !== false) $matchFound = true; // Use simple contains/equals
                     if ($operator === 'contains' && stripos($pType, $value) !== false) $matchFound = true;
                     if ($operator === '!=' && $pType !== $value) $matchFound = true; // Wait, != means matches if at least one is NOT? No. if filtering out, we want NONE to be.
                     // Logic: "Product Type is Gift Card" -> Show orders containing a gift card.
                 }
                 if ($matchFound) {
                     $nodeValue = $value; // Force match
                 } else {
                     $nodeValue = '__NO_MATCH__';
                 }
            } elseif ($field === 'gateway') {
                 // Deep Check: Scan Transactions
                 $trans = $node['transactions'] ?? [];
                 $matchFound = false;
                 foreach($trans as $t) {
                     $gw = $t['gateway'] ?? '';
                     if (strcasecmp($gw, $value) === 0) $matchFound = true;
                 }
                 // Logic: "Gateway is gift_card"
                 if ($matchFound) {
                     $nodeValue = $value;
                 } else {
                     $nodeValue = '__NO_MATCH__';
                 }
            } elseif (isset($node[$field])) {
                $nodeValue = $node[$field];
            } else {
                continue; 
            }

            // Extract amount if it's a monetary object
            if (is_array($nodeValue) && isset($nodeValue['amount'])) {
                $nodeValue = $nodeValue['amount'];
            }

            // Valid value check: allowed null/empty/zero for comparison
            if ($nodeValue === '__NO_MATCH__') return false; 
            if ($nodeValue === null) continue; 

            // Date Comparison
            if (strpos($field, '_at') !== false) {
                 // ... (existing date logic)
                $nodeTime = strtotime($nodeValue);
                // Fix for Day Boundaries
                if (strlen($value) === 10 && $operator === '<=') {
                     $filterTime = strtotime($value . ' 23:59:59');
                } else {
                     $filterTime = strtotime($value);
                }
                
                switch ($operator) {
                    case '>': if (!($nodeTime > $filterTime)) return false; break;
                    case '>=': if (!($nodeTime >= $filterTime)) return false; break;
                    case '<': if (!($nodeTime < $filterTime)) return false; break;
                    case '<=': if (!($nodeTime <= $filterTime)) return false; break;
                    case '=': 
                        if (date('Y-m-d', $nodeTime) !== date('Y-m-d', $filterTime)) return false; 
                        break;
                }
            } else {
                // String/Number comparison
                switch ($operator) {
                    case '=': if (strcasecmp($nodeValue, $value) != 0) return false; break; // Case insensitive string
                    case '!=': if (strcasecmp($nodeValue, $value) == 0) return false; break;
                    case 'contains': if (stripos($nodeValue, $value) === false) return false; break;
                }
            }
        }
        return true;
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
        // Fetch Orders including LineItems for product filtering
        // Removing 'transactions' block temporarily to resolve API schema error "Field 'edges' doesn't exist on OrderTransaction"
        return "query { orders(query: \"status:any\") { edges { node { id createdAt totalPriceSet { shopMoney { amount currencyCode } } lineItems { edges { node { title variant { product { productType } } } } } } } } }";
    }

    private function buildAovTimeQuery($filters, $columns, $groupBy, $aggregations)
    {
        return "query { orders(query: \"status:any\") { edges { node { id createdAt totalPriceSet { shopMoney { amount } } lineItems { edges { node { title variant { product { productType } } } } } } } } }";
    }

    private function buildBrowserShareQuery($filters, $columns, $groupBy, $aggregations)
    {
        return "query { orders(first: 250) { edges { node { id customerJourneySummary { lastVisit { source browser } } } } } }";
    }
    private function buildSearchQuery($filters, $allowedFields)
    {
        $parts = [];
        foreach ($filters as $filter) {
            // Basic validation
            if (empty($filter['field']) || empty($filter['value'])) continue;
            
            // Map frontend fields
            $field = $filter['field'];
            if ($field === 'accepts_email_marketing') $field = 'accepts_marketing';

            // CRITICAL FIX: Exclude date filters from Shopify Search Query.
            // Shopify's timezone handling in search syntax is tricky and can exclude "today's" items.
            // We rely on PHP-side filtering (matchesFilters) which is now robust.
            // Since we fetch 250 items (small store), fetching all recent items and filtering in PHP is safer.
            // CRITICAL FIX: Exclude date filters from Shopify Search Query.
            if ($field === 'created_at' || $field === 'updated_at') continue;
            
            // CRITICAL FIX: Exclude Status filters from Shopify Search Query (API Error Prevention)
            // Shopify API rejects 'financialStatus' (camelCase) in search query.
            // We force these to be filtered in PHP (matchesFilters) instead.
            if (in_array($field, ['financial_status', 'financialStatus', 'fulfillment_status', 'fulfillmentStatus'])) {
                 error_log("buildSearchQuery - Skipping restricted field: $field");
                 continue;
            }

            if (!in_array($field, $allowedFields)) {
                 error_log("buildSearchQuery - Field $field not in allowed list: " . implode(', ', $allowedFields));
                 continue;
            }

            $val = $filter['value'];
            $op = $filter['operator'] ?? '=';

            // Automatic quoting for strings with spaces (unless it's a known non-quoted type or already quoted)
            // Ideally we check if $val contains space and doesn't look like a date or number.
            // Simple heuristic: if it has space and isn't enclosed in ", wrap it.
            if (is_string($val) && strpos($val, ' ') !== false && substr($val, 0, 1) !== '"') {
                $val = '"' . $val . '"';
            }

            // Shopify Search Syntax Construction
            switch ($op) {
                case '>':
                    $parts[] = "{$field}:>{$val}";
                    break;
                case '>=':
                    $parts[] = "{$field}:>={$val}";
                    break;
                case '<':
                    $parts[] = "{$field}:<{$val}";
                    break;
                case '<=':
                    $parts[] = "{$field}:<={$val}";
                    break;
                case '!=':
                    // Negation query
                    $parts[] = "-{$field}:{$val}";
                    break;
                case 'contains':
                    // removal of quotes might be needed for wildcards strictly, but usually *foo bar* needs quotes "*foo bar*"
                    // Let's assume for contains we wrap wildcards outside the quotes if quoted? 
                    // Actually Shopify syntax: title:*foo*
                    // If quoted: title:"*foo bar*"
                    $parts[] = "{$field}:*{$val}*";
                    break;
                case '=':
                default:
                    $parts[] = "{$field}:{$val}";
                    break;
            }
        }
        
        return implode(' AND ', $parts);
    }


    private function findCountryRecursively($array) {
        foreach ($array as $key => $value) {
            if ($key === 'country' && is_string($value) && !empty($value) && $value !== 'Unknown') {
                return $value;
            }
            if (is_array($value)) {
                $res = $this->findCountryRecursively($value);
                if ($res) return $res;
            }
        }
        return null;
    }
}
