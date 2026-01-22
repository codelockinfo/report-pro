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
        error_log("Headers: " . json_encode($headers));
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        error_log("Payload: " . $json);

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

        // Verify webhook
        if (!$this->verifyWebhook()) {
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
        // Handle GDPR data request
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->verifyWebhook()) {
            http_response_code(401);
            exit;
        }

        // Store data request and process it
        // This would typically be queued for processing
        
        http_response_code(200);
        exit;
    }

    public function customersRedact()
    {
        // Handle GDPR customer redaction
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->verifyWebhook()) {
            http_response_code(401);
            exit;
        }

        // Redact customer data
        // Remove or anonymize customer data
        
        http_response_code(200);
        exit;
    }

    public function shopRedact()
    {
        // Handle GDPR shop redaction
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->verifyWebhook()) {
            http_response_code(401);
            exit;
        }

        // Redact shop data
        
        http_response_code(200);
        exit;
    }

    private function verifyWebhook()
    {
        $headers = getallheaders();
        $hmac = $headers['X-Shopify-Hmac-Sha256'] ?? '';
        $data = file_get_contents('php://input');
        
        $config = $this->config['shopify'];
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $config['api_secret'], true));
        
        return hash_equals($hmac, $calculatedHmac);
    }
}

