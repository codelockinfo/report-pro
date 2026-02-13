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

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Execute a REST API request
     */
    public function rest($method, $path, $params = [])
    {
        $url = "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/" . ltrim($path, '/');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // Capture headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $this->accessToken
        ]);
        
        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        
        $headerStr = substr($responseRaw, 0, $headerSize);
        $bodyStr = substr($responseRaw, $headerSize);
        
        if ($httpCode >= 400) {
            error_log("Shopify REST API Error ({$httpCode}): " . $bodyStr);
            return null;
        }
        
        $headers = $this->parseHeaders($headerStr);
        $body = json_decode($bodyStr, true);
        
        return [
            'headers' => $headers,
            'body' => $body
        ];
    }

    private function parseHeaders($headerStr)
    {
        $headers = [];
        foreach (explode("\r\n", $headerStr) as $i => $line) {
            if ($i === 0) continue; // Skip HTTP Status line
            if (empty($line)) continue;
            
            $parts = explode(': ', $line, 2);
            if (count($parts) === 2) {
                $headers[strtolower($parts[0])] = $parts[1];
            }
        }
        return $headers;
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

    /**
     * Fetch granted access scopes for the current token.
     * Useful for diagnosing ACCESS_DENIED errors (missing scopes vs protected data restrictions).
     */
    public function getGrantedAccessScopes()
    {
        $url = "https://{$this->shopDomain}/admin/oauth/access_scopes.json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $this->accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("ShopifyService::getGrantedAccessScopes - HTTP {$httpCode}: {$response}");
            return null;
        }

        $data = json_decode($response, true);
        $scopes = $data['access_scopes'] ?? [];

        $handles = [];
        foreach ($scopes as $s) {
            if (isset($s['handle'])) $handles[] = $s['handle'];
        }

        return $handles;
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

        error_log("ShopifyService::createBulkOperation - Raw Query: " . $query);
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
        error_log("ShopifyService::downloadBulkOperationFile - Downloading from URL: $url");
        $tempFile = tempnam(sys_get_temp_dir(), 'shopify_bulk_');
        $fp = fopen($tempFile, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode !== 200) {
            error_log("ShopifyService::downloadBulkOperationFile - Download failed. HTTP: $httpCode");
            if (file_exists($tempFile)) unlink($tempFile);
            return null;
        }

        return $tempFile;
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

