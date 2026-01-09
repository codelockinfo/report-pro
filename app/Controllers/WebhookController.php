<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Shop;

class WebhookController extends Controller
{
    public function appUninstalled()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $shopDomain = $data['shop_domain'] ?? '';

        if (empty($shopDomain)) {
            http_response_code(400);
            exit;
        }

        // Verify webhook
        if (!$this->verifyWebhook()) {
            http_response_code(401);
            exit;
        }

        // Clean up shop data
        $shopModel = new Shop();
        $shop = $shopModel->findByDomain($shopDomain);
        
        if ($shop) {
            // Delete shop and all related data (cascade will handle it)
            $shopModel->delete($shop['id']);
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

