<?php

namespace App\Core;

class Controller
{
    protected $view;
    protected $db;
    protected $config;

    public function __construct()
    {
        $this->view = new View();
        $this->db = Database::getInstance();
        $this->config = require CONFIG_PATH . '/config.php';
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function getShop()
    {
        $session = $this->getSession();
        if (!$session || !isset($session['shop'])) {
            return null;
        }

        $shopModel = new \App\Models\Shop();
        return $shopModel->findByDomain($session['shop']);
    }

    protected function getSession()
    {
        session_start();
        return $_SESSION['shopify_session'] ?? null;
    }

    protected function setSession($data)
    {
        session_start();
        $_SESSION['shopify_session'] = $data;
    }

    protected function clearSession()
    {
        session_start();
        unset($_SESSION['shopify_session']);
    }

    protected function requireAuth()
    {
        $shop = $this->getShop();
        if (!$shop) {
            $this->redirect('/auth/install');
        }
        return $shop;
    }

    protected function validateHmac($query)
    {
        $config = $this->config['shopify'];
        $hmac = $query['hmac'] ?? '';
        unset($query['hmac']);
        
        ksort($query);
        $message = http_build_query($query);
        $calculatedHmac = hash_hmac('sha256', $message, $config['api_secret']);
        
        return hash_equals($hmac, $calculatedHmac);
    }
}

