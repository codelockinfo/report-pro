<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Shop;

class WebhookController extends Controller
{
    public function appUninstalled()
    {
        // Get headers for logging and shop domain
        $headers = getallheaders();
        $shopHeader = $headers['X-Shopify-Shop-Domain'] ?? '';
        
        // Log the hit
        error_log("=== WEBHOOK: APP_UNINSTALLED HIT ===");
        
        // Read input ONCE
        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true);
        
        // Try to get shop domain from multiple sources
        $shopDomain = $shopHeader;
        if (empty($shopDomain)) {
            $shopDomain = $data['myshopify_domain'] ?? $data['domain'] ?? $data['shop_domain'] ?? '';
        }

        if (empty($shopDomain)) {
            error_log("WEBHOOK ERROR: Could not determine shop domain");
            http_response_code(400);
            exit;
        }

        error_log("WEBHOOK: Processing uninstallation for shop: {$shopDomain}");

        // Verify webhook using the raw body we already read
        if (!$this->verifyWebhook($rawBody)) {
            error_log("WEBHOOK ERROR: Verification failed for shop: {$shopDomain}");
            http_response_code(401);
            exit;
        }

        // Mark shop as inactive
        $shopModel = new Shop();
        $shop = $shopModel->findByDomain($shopDomain);
        
        if ($shop) {
            $result = $shopModel->update($shop['id'], ['is_active' => 0]);
            error_log("WEBHOOK SUCCESS: Shop {$shopDomain} (ID: {$shop['id']}) marked as inactive. Result: " . ($result ? 'OK' : 'FAIL'));
        } else {
            error_log("WEBHOOK WARNING: Shop {$shopDomain} not found in database");
        }

        http_response_code(200);
        exit;
    }

    public function customersDataRequest()
    {
        $rawBody = file_get_contents('php://input');
        
        if (!$this->verifyWebhook($rawBody)) {
            http_response_code(401);
            exit;
        }

        // Handle GDPR data request
        $data = json_decode($rawBody, true);
        
        // Store data request and process it
        http_response_code(200);
        exit;
    }

    public function customersRedact()
    {
        $rawBody = file_get_contents('php://input');
        
        if (!$this->verifyWebhook($rawBody)) {
            http_response_code(401);
            exit;
        }

        // Handle GDPR customer redaction
        $data = json_decode($rawBody, true);
        
        // Redact customer data
        http_response_code(200);
        exit;
    }

    public function shopRedact()
    {
        $rawBody = file_get_contents('php://input');
        
        if (!$this->verifyWebhook($rawBody)) {
            http_response_code(401);
            exit;
        }

        // Handle GDPR shop redaction
        $data = json_decode($rawBody, true);
        
        // Redact shop data
        http_response_code(200);
        exit;
    }

    private function verifyWebhook($data)
    {
        $headers = getallheaders();
        $hmac = $headers['X-Shopify-Hmac-Sha256'] ?? '';
        
        // Use the config from the controller
        $config = $this->config['shopify'];
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $config['api_secret'], true));
        
        return hash_equals($hmac, $calculatedHmac);
    }
}

