<?php

namespace App\Services;

class ShopifyService
{
    private $shopDomain;
    private $accessToken;
    private $apiVersion;

    public function __construct($shopDomain, $accessToken)
    {
        $this->shopDomain = $shopDomain;
        $this->accessToken = $accessToken;
        $config = require CONFIG_PATH . '/config.php';
        $this->apiVersion = $config['shopify']['api_version'];
    }

    public function graphql($query, $variables = [])
    {
        $url = "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/graphql.json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'query' => $query,
            'variables' => empty($variables) ? new \stdClass() : $variables
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $this->accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorMsg = "Shopify API HTTP Error: {$httpCode} - {$response}";
            error_log($errorMsg);
            throw new \Exception($errorMsg);
        }

        $data = json_decode($response, true);
        
        if (isset($data['errors'])) {
            $errorMsg = "Shopify GraphQL Error: " . json_encode($data['errors']);
            error_log($errorMsg);
            throw new \Exception($errorMsg);
        }

        return $data['data'] ?? null;
    }

    public function createBulkOperation($query)
    {
        // Mock for Local Dev - DISABLED to fetch real data
        if (false && getenv('APP_ENV') === 'local') {
            $type = 'orders';
            if (strpos($query, 'products') !== false) $type = 'products';
            if (strpos($query, 'customers') !== false) $type = 'customers';
            if (strpos($query, 'transactions') !== false) $type = 'transactions';
            if (strpos($query, 'inventoryLevels') !== false) $type = 'inventory_levels';
            if (strpos($query, 'draftOrders') !== false) $type = 'draft_orders';
            if (strpos($query, 'lineItems') !== false) $type = 'line_items';
            if (strpos($query, '#DATASET:SALES_SUMMARY') !== false) $type = 'sales_summary';
            if (strpos($query, '#DATASET:AOV_TIME') !== false) $type = 'aov_time';
            if (strpos($query, '#DATASET:BROWSER_SHARE') !== false) $type = 'browser_share';
            
            error_log("ShopifyService::createBulkOperation - Mock type detected: {$type}");

            return [
                'bulkOperationRunQuery' => [
                    'bulkOperation' => [
                        'id' => "gid://shopify/BulkOperation/MOCK_{$type}_123",
                        'status' => 'CREATED',
                        'query' => $query
                    ],
                    'userErrors' => []
                ]
            ];
        }

        $encodedQuery = json_encode($query);

        $mutation = <<<GRAPHQL
mutation {
  bulkOperationRunQuery(
    query: {$encodedQuery}
  ) {
    bulkOperation {
      id
      status
      query
    }
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;

        return $this->graphql($mutation);
    }

    public function getBulkOperationStatus($operationId)
    {
        // Mock for Local Dev - DISABLED
        if (false && getenv('APP_ENV') === 'local') {
            $type = 'orders';
            if (strpos($operationId, 'MOCK_products') !== false) $type = 'products';
            if (strpos($operationId, 'MOCK_customers') !== false) $type = 'customers';
            if (strpos($operationId, 'MOCK_transactions') !== false) $type = 'transactions';
            if (strpos($operationId, 'MOCK_inventory_levels') !== false) $type = 'inventory_levels';
            if (strpos($operationId, 'MOCK_draft_orders') !== false) $type = 'draft_orders';
            if (strpos($operationId, 'MOCK_line_items') !== false) $type = 'line_items';
            if (strpos($operationId, 'MOCK_sales_summary') !== false) $type = 'sales_summary';
            if (strpos($operationId, 'MOCK_aov_time') !== false) $type = 'aov_time';
            if (strpos($operationId, 'MOCK_browser_share') !== false) $type = 'browser_share';

             return [
                'node' => [
                    'id' => $operationId,
                    'status' => 'COMPLETED',
                    'errorCode' => null,
                    'createdAt' => date('c'),
                    'completedAt' => date('c'),
                    'objectCount' => 10,
                    'fileSize' => 1024,
                    'url' => "mock://{$type}.jsonl",
                    'partialDataUrl' => null
                ]
            ];
        }

        $query = <<<GRAPHQL
            query {
                node(id: "{$operationId}") {
                    ... on BulkOperation {
                        id
                        status
                        errorCode
                        createdAt
                        completedAt
                        objectCount
                        fileSize
                        url
                        partialDataUrl
                    }
                }
            }
        GRAPHQL;

        return $this->graphql($query);
    }

    // ... (keep getOrders, getProducts, etc) ...

    public function downloadBulkOperationFile($url)
    {
        // Mock for Local Dev - DISABLED
        if (false && getenv('APP_ENV') === 'local' && strpos($url, 'mock://') === 0) {
            error_log("ShopifyService::downloadBulkOperationFile - Mocking: {$url}");
            $data = [];
            
            if (strpos($url, 'customers') !== false) {
                $data = [
                    ['id' => 'gid://shopify/Customer/1', 'displayName' => 'Gunjan Koladiya', 'email' => 'gunjan.codelock@gmail.com', 'acceptsMarketing' => false, 'createdAt' => '2025-06-19T10:00:00Z', 'ordersCount' => '23', 'totalSpent' => ['amount' => '57410.54', 'currencyCode' => 'INR'], 'defaultAddress' => ['country' => 'India']],
                    ['id' => 'gid://shopify/Customer/2', 'displayName' => 'Raymond Tremblay', 'email' => 'montuthakor1421@gmail.com', 'acceptsMarketing' => false, 'createdAt' => '2025-06-19T11:00:00Z', 'ordersCount' => '3', 'totalSpent' => ['amount' => '0.00', 'currencyCode' => 'INR'], 'defaultAddress' => ['country' => 'India']],
                    ['id' => 'gid://shopify/Customer/3', 'displayName' => 'Harsh Patel', 'email' => 'harsh@gmail.com', 'acceptsMarketing' => true, 'createdAt' => '2021-07-07T10:00:00Z', 'ordersCount' => '0', 'totalSpent' => ['amount' => '0.00', 'currencyCode' => 'INR'], 'defaultAddress' => ['country' => 'India']]
                ];
            } elseif (strpos($url, 'orders') !== false) {
                $data = [
                    ['id' => 'gid://shopify/Order/1', 'name' => '#1001', 'email' => 'buyer@example.com', 'createdAt' => '2025-01-15T12:00:00Z', 'totalPriceSet' => ['shopMoney' => ['amount' => '49.99', 'currencyCode' => 'USD']], 'financialStatus' => 'PAID', 'fulfillmentStatus' => 'FULFILLED', 'shippingAddress' => ['country' => 'United States']],
                    ['id' => 'gid://shopify/Order/2', 'name' => '#1002', 'email' => 'vip@example.com', 'createdAt' => '2025-01-16T14:30:00Z', 'totalPriceSet' => ['shopMoney' => ['amount' => '150.00', 'currencyCode' => 'USD']], 'financialStatus' => 'PAID', 'fulfillmentStatus' => 'UNFULFILLED', 'shippingAddress' => ['country' => 'Canada']]
                ];
            } elseif (strpos($url, 'products') !== false) {
                $data = [
                    ['id' => 'gid://shopify/Product/1', 'title' => 'Cool T-Shirt', 'vendor' => 'MyBrand', 'productType' => 'Apparel', 'status' => 'ACTIVE', 'totalInventory' => 50, 'createdAt' => '2024-12-01T09:00:00Z'],
                    ['id' => 'gid://shopify/Product/2', 'title' => 'Awesome Sneakers', 'vendor' => 'MyBrand', 'productType' => 'Footwear', 'status' => 'ACTIVE', 'totalInventory' => 20, 'createdAt' => '2025-01-05T10:00:00Z']
                ];
            } elseif (strpos($url, 'transactions') !== false) {
                $data = [
                     ['id' => 'gid://shopify/OrderTransaction/1', 'kind' => 'SALE', 'status' => 'SUCCESS', 'amountSet' => ['shopMoney' => ['amount' => '49.99', 'currencyCode' => 'USD']], 'gateway' => 'shopify_payments', 'createdAt' => '2025-01-15T12:05:00Z'],
                     ['id' => 'gid://shopify/OrderTransaction/2', 'kind' => 'REFUND', 'status' => 'SUCCESS', 'amountSet' => ['shopMoney' => ['amount' => '10.00', 'currencyCode' => 'USD']], 'gateway' => 'paypal', 'createdAt' => '2025-01-16T09:00:00Z']
                ];
            } elseif (strpos($url, 'inventory_levels') !== false) {
                $data = [
                     ['id' => 'gid://shopify/InventoryLevel/1?inventory_item_id=123&location_id=456', 'available' => 50, 'location' => ['id' => 'gid://shopify/Location/1', 'name' => 'New York Warehouse'], 'inventoryItem' => ['id' => 'gid://shopify/InventoryItem/123', 'sku' => 'SKU-001'], 'updatedAt' => '2025-01-20T10:00:00Z']
                ];
            } elseif (strpos($url, 'draft_orders') !== false) {
                $data = [
                    ['id' => 'gid://shopify/DraftOrder/1', 'name' => 'D1', 'email' => 'draft@example.com', 'createdAt' => '2025-01-18T10:00:00Z', 'totalPriceSet' => ['shopMoney' => ['amount' => '200.00', 'currencyCode' => 'USD']], 'status' => 'OPEN'],
                    ['id' => 'gid://shopify/DraftOrder/2', 'name' => 'D2', 'email' => 'draft2@example.com', 'createdAt' => '2025-01-19T11:00:00Z', 'totalPriceSet' => ['shopMoney' => ['amount' => '50.00', 'currencyCode' => 'USD']], 'status' => 'COMPLETED']
                ];
            } elseif (strpos($url, 'line_items') !== false) {
                // Bulk API returns Order and LineItem objects interspersed. 
                // We'll return some line items.
                $data = [
                    ['id' => 'gid://shopify/LineItem/1', 'title' => 'Sample Product A', 'quantity' => 2, 'sku' => 'SKU-A', 'vendor' => 'Vendor X', 'priceSet' => ['shopMoney' => ['amount' => '25.00', 'currencyCode' => 'USD']]],
                    ['id' => 'gid://shopify/LineItem/2', 'title' => 'Sample Product B', 'quantity' => 1, 'sku' => 'SKU-B', 'vendor' => 'Vendor Y', 'priceSet' => ['shopMoney' => ['amount' => '15.00', 'currencyCode' => 'USD']]]
                ];
            } elseif (strpos($url, 'sales_summary') !== false) {
                // Return exactly what's in the image
                $data = [
                    [
                        'total_orders' => 26,
                        'total_gross_sales' => ['amount' => '83840.00', 'currencyCode' => 'INR'],
                        'total_discounts' => ['amount' => '0.00', 'currencyCode' => 'INR'],
                        'total_refunds' => ['amount' => '35187.00', 'currencyCode' => 'INR'],
                        'total_net_sales' => ['amount' => '48653.00', 'currencyCode' => 'INR'],
                        'total_taxes' => ['amount' => '8757.54', 'currencyCode' => 'INR'],
                        'total_shipping' => ['amount' => '0.00', 'currencyCode' => 'INR'],
                        'total_sales' => ['amount' => '57410.54', 'currencyCode' => 'INR'],
                        'total_cost_of_goods_sold' => ['amount' => '44260.00', 'currencyCode' => 'INR'],
                        'total_gross_margin' => ['amount' => '4393.00', 'currencyCode' => 'INR']
                    ]
                ];
            } elseif (strpos($url, 'aov_time') !== false) {
                // Return data suitable for AOV time chart
                $data = [
                    ['date' => '2025-01-01', 'average_order_value' => '1250.00'],
                    ['date' => '2025-02-01', 'average_order_value' => '1500.00'],
                    ['date' => '2025-03-01', 'average_order_value' => '1100.00'],
                    ['date' => '2025-04-01', 'average_order_value' => '2300.00'],
                    ['date' => '2025-05-01', 'average_order_value' => '1800.00'],
                    ['date' => '2025-06-01', 'average_order_value' => '3200.00']
                ];
            } elseif (strpos($url, 'browser_share') !== false) {
                // Return data suitable for Browser Share chart
                $data = [
                    ['browser' => 'Chrome', 'sessions_count' => 1250],
                    ['browser' => 'Safari', 'sessions_count' => 850],
                    ['browser' => 'Firefox', 'sessions_count' => 320],
                    ['browser' => 'Edge', 'sessions_count' => 180],
                    ['browser' => 'Mobile Safari', 'sessions_count' => 2400],
                    ['browser' => 'Chrome Mobile', 'sessions_count' => 3100],
                    ['browser' => 'Other', 'sessions_count' => 95]
                ];
            }
            
            $lines = [];
            foreach ($data as $c) {
                $lines[] = json_encode($c);
            }
            return implode("\n", $lines);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return $data;
    }

    // ... (keep getOrders, getProducts, getCustomers, getTransactions as is or mock them if needed, but report uses bulk op) ...

    public function getOrders($limit = 50, $after = null)
    {
        // ... existing implementation ...
        $cursor = $after ? ", after: \"{$after}\"" : "";
        
        $query = <<<GRAPHQL
            query {
                orders(first: {$limit}{$cursor}) {
                    edges {
                        node {
                            id
                            name
                            email
                            createdAt
                            updatedAt
                            totalPriceSet {
                                shopMoney {
                                    amount
                                    currencyCode
                                }
                            }
                            financialStatus
                            fulfillmentStatus
                            customer {
                                id
                                firstName
                                lastName
                                email
                            }
                            shippingAddress {
                                country
                                city
                            }
                            lineItems(first: 250) {
                                edges {
                                    node {
                                        title
                                        quantity
                                        variant {
                                            id
                                            title
                                        }
                                        product {
                                            id
                                            title
                                        }
                                    }
                                }
                            }
                        }
                        cursor
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;

        return $this->graphql($query);
    }

    public function getProducts($limit = 50, $after = null)
    {
        // ... existing implementation ...
        $cursor = $after ? ", after: \"{$after}\"" : "";
        
        $query = <<<GRAPHQL
            query {
                products(first: {$limit}{$cursor}) {
                    edges {
                        node {
                            id
                            title
                            handle
                            vendor
                            productType
                            status
                            createdAt
                            updatedAt
                            totalInventory
                            variants(first: 250) {
                                edges {
                                    node {
                                        id
                                        title
                                        sku
                                        price
                                        inventoryQuantity
                                        inventoryItem {
                                            id
                                        }
                                    }
                                }
                            }
                        }
                        cursor
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;

        return $this->graphql($query);
    }

    public function getCustomers($limit = 50, $after = null)
    {
        // ... existing implementation ...
        $cursor = $after ? ", after: \"{$after}\"" : "";
        
        $query = <<<GRAPHQL
            query {
                customers(first: {$limit}{$cursor}) {
                    edges {
                        node {
                            id
                            firstName
                            lastName
                            email
                            phone
                            createdAt
                            updatedAt
                            ordersCount
                            totalSpent {
                                amount
                                currencyCode
                            }
                            defaultAddress {
                                country
                                city
                                province
                            }
                        }
                        cursor
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;

        return $this->graphql($query);
    }

    public function getTransactions($limit = 50, $after = null)
    {
        // ... existing implementation ...
        $cursor = $after ? ", after: \"{$after}\"" : "";
        
        $query = <<<GRAPHQL
            query {
                transactions(first: {$limit}{$cursor}) {
                    edges {
                        node {
                            id
                            kind
                            status
                            amount
                            currencyCode
                            createdAt
                            gateway
                            parentTransaction {
                                id
                            }
                            order {
                                id
                                name
                            }
                        }
                        cursor
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;

        return $this->graphql($query);
    }
}

