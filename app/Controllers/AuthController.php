<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Shop;

class AuthController extends Controller
{
    public function install()
    {
        $shop = $_GET['shop'] ?? '';
        
        if (empty($shop)) {
            die("Shop parameter is required");
        }

        // Validate shop domain
        if (!preg_match('/^[a-zA-Z0-9-]+\.myshopify\.com$/', $shop)) {
            die("Invalid shop domain");
        }

        $config = $this->config['shopify'];
        $scopes = $config['scopes'];
        $redirectUri = $config['redirect_uri'];
        $apiKey = $config['api_key'];

        // Generate state for CSRF protection
        $state = bin2hex(random_bytes(16));
        session_start();
        $_SESSION['oauth_state'] = $state;

        // Build OAuth URL
        $authUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
            'client_id' => $apiKey,
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => $state
        ]);

        header("Location: {$authUrl}");
        exit;
    }

    public function callback()
    {
        session_start();
        
        $code = $_GET['code'] ?? '';
        $shop = $_GET['shop'] ?? '';
        $state = $_GET['state'] ?? '';
        $hmac = $_GET['hmac'] ?? '';

        // Validate state
        if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
            die("Invalid state parameter");
        }

        // Validate HMAC
        if (!$this->validateHmac($_GET)) {
            die("Invalid HMAC");
        }

        // Exchange code for access token
        $config = $this->config['shopify'];
        $tokenUrl = "https://{$shop}/admin/oauth/access_token";

        $data = [
            'client_id' => $config['api_key'],
            'client_secret' => $config['api_secret'],
            'code' => $code
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            die("Failed to get access token");
        }

        $tokenData = json_decode($response, true);
        $accessToken = $tokenData['access_token'] ?? '';
        $scope = $tokenData['scope'] ?? '';

        if (empty($accessToken)) {
            die("Access token not received");
        }

        // Save shop and access token
        $shopModel = new Shop();
        $shopId = $shopModel->createOrUpdate($shop, [
            'access_token' => $accessToken,
            'scope' => $scope,
            'store_name' => $shop
        ]);

        // Set session
        $this->setSession([
            'shop' => $shop,
            'shop_id' => $shopId,
            'access_token' => $accessToken
        ]);

        // Redirect to app
        $appUrl = $this->config['app_url'] . "/dashboard?shop={$shop}";
        header("Location: {$appUrl}");
        exit;
    }

    public function logout()
    {
        $this->clearSession();
        $this->redirect('/auth/install');
    }
}

