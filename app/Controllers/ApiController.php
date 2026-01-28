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

    public function bulkOperationStatus($id = null)
    {
        // Handle ID from query param if not passed in path (or if path routing failed due to slashes)
        if (!$id && isset($_GET['id'])) {
            $id = $_GET['id'];
        }

        if (!$id) {
             $this->json(['error' => 'Operation ID is required'], 400);
        }

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

            $errorCode = $node['errorCode'] ?? null;
            $message = null;
            $debug = null;
            if (($node['status'] ?? null) === 'FAILED' && $errorCode === 'ACCESS_DENIED') {
                $message = 'Shopify denied access to one or more fields used in this report query.';

                // Diagnose whether this is missing scopes (token not reauthorized) vs protected data restrictions.
                $required = $this->config['shopify']['scopes'] ?? '';
                $requiredScopes = array_values(array_filter(array_map('trim', explode(',', (string)$required))));

                $grantedScopes = null;
                try {
                    $grantedScopes = $shopifyService->getGrantedAccessScopes();
                } catch (\Throwable $e) {
                    // Ignore; we’ll still return a helpful message.
                    error_log("ApiController::bulkOperationStatus - getGrantedAccessScopes error: " . $e->getMessage());
                }

                $missingScopes = null;
                if (is_array($grantedScopes)) {
                    $missingScopes = array_values(array_diff($requiredScopes, $grantedScopes));
                }

                if (is_array($missingScopes) && count($missingScopes) > 0) {
                    $message .= ' Your token is missing required app permissions. Please reauthorize the app to grant the latest scopes.';
                } else {
                    $message .= ' This usually means the store has restricted/protected customer data access, or your app needs to be reauthorized after scope changes.';
                }

                $reauthorizeUrl = '/auth/install?shop=' . urlencode($shop['shop_domain']);

                $debug = [
                    'required_scopes' => $requiredScopes,
                    'granted_scopes' => $grantedScopes,
                    'missing_scopes' => $missingScopes,
                    'reauthorize_url' => $reauthorizeUrl,
                ];
            }

            $this->json([
                'status' => $node['status'],
                'completed_at' => $node['completedAt'] ?? null,
                'url' => $node['url'] ?? null,
                'error_code' => $errorCode,
                // Helpful context for debugging/support
                'message' => $message,
                'operation_type' => $operation['operation_type'] ?? null,
                'debug' => $debug,
            ]);
        } else {
            $this->json(['error' => 'Failed to get status'], 500);
        }
    }
}

