<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ShopifyService;

class ApiController extends Controller
{
    public function graphql()
    {
        $shop = $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $variables = $input['variables'] ?? [];

        if (empty($query)) {
            $this->json(['error' => 'Query is required'], 400);
        }

        $shopifyService = new ShopifyService(
            $shop['shop_domain'],
            $shop['access_token']
        );

        $result = $shopifyService->graphql($query, $variables);
        
        if ($result === null) {
            $this->json(['error' => 'GraphQL query failed'], 500);
        }

        $this->json($result);
    }

    public function bulkOperation()
    {
        $shop = $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';

        if (empty($query)) {
            $this->json(['error' => 'Query is required'], 400);
        }

        $shopifyService = new ShopifyService(
            $shop['shop_domain'],
            $shop['access_token']
        );

        $result = $shopifyService->createBulkOperation($query);
        
        if (!$result || !isset($result['bulkOperationRunQuery']['bulkOperation'])) {
            $this->json(['error' => 'Failed to create bulk operation'], 500);
        }

        $operation = $result['bulkOperationRunQuery']['bulkOperation'];
        
        // Save to database
        $bulkOpModel = new \App\Models\BulkOperation();
        $bulkOpModel->create([
            'shop_id' => $shop['id'],
            'operation_id' => $operation['id'],
            'operation_type' => 'custom',
            'status' => $operation['status'],
            'query' => $query
        ]);

        $this->json([
            'success' => true,
            'operation_id' => $operation['id'],
            'status' => $operation['status']
        ]);
    }

    public function bulkOperationStatus($id)
    {
        $shop = $this->requireAuth();
        
        $bulkOpModel = new \App\Models\BulkOperation();
        $operation = $bulkOpModel->findByOperationId($id);

        if (!$operation || $operation['shop_id'] != $shop['id']) {
            $this->json(['error' => 'Operation not found'], 404);
        }

        $shopifyService = new ShopifyService(
            $shop['shop_domain'],
            $shop['access_token']
        );

        $status = $shopifyService->getBulkOperationStatus($id);
        
        if ($status && isset($status['node'])) {
            $node = $status['node'];
            $bulkOpModel->update($operation['id'], ['status' => $node['status']]);
            
            $this->json([
                'status' => $node['status'],
                'completed_at' => $node['completedAt'] ?? null,
                'url' => $node['url'] ?? null
            ]);
        } else {
            $this->json(['error' => 'Failed to get status'], 500);
        }
    }
}

