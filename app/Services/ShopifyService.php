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
            'variables' => $variables
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $this->accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Shopify API Error: HTTP {$httpCode} - {$response}");
            return null;
        }

        $data = json_decode($response, true);
        
        if (isset($data['errors'])) {
            error_log("Shopify GraphQL Errors: " . json_encode($data['errors']));
            return null;
        }

        return $data['data'] ?? null;
    }

    public function createBulkOperation($query)
    {
        $mutation = <<<GRAPHQL
            mutation {
                bulkOperationRunQuery(
                    query: "{$query}"
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

    public function getOrders($limit = 50, $after = null)
    {
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

    public function downloadBulkOperationFile($url)
    {
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
}

