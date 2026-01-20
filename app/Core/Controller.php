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
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Database connection failed - show error page
            $this->showDatabaseError($e->getMessage());
        }
        $this->config = require CONFIG_PATH . '/config.php';
    }

    protected function showDatabaseError($message)
    {
        http_response_code(500);
        $title = 'Database Connection Error';
        ob_start();
        ?>
        <div class="Polaris-Page">
            <div class="Polaris-Page__Content">
                <div class="Polaris-Card">
                    <div class="Polaris-Card__Section">
                        <div class="Polaris-EmptyState">
                            <div class="Polaris-EmptyState__Section">
                                <div class="Polaris-EmptyState__DetailsContainer">
                                    <p class="Polaris-EmptyState__Text" style="color: #d72c0d; font-weight: 600;">
                                        Database connection failed
                                    </p>
                                    <p class="Polaris-TextStyle--variationSubdued" style="margin-top: 1rem;">
                                        <?= htmlspecialchars($message) ?>
                                    </p>
                                    <div class="Polaris-EmptyState__Actions" style="margin-top: 1.5rem;">
                                        <p class="Polaris-TextStyle--variationSubdued">
                                            Please check your database configuration in the <code>.env</code> file on your server.
                                        </p>
                                        <p class="Polaris-TextStyle--variationSubdued" style="margin-top: 0.5rem;">
                                            See <code>PRODUCTION_SETUP.md</code> for setup instructions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // Try to load layout, but if it fails, just output content
        $layoutFile = VIEWS_PATH . '/layouts/app.php';
        if (file_exists($layoutFile)) {
            $config = require CONFIG_PATH . '/config.php';
            $shop = ['shop_domain' => $_GET['shop'] ?? ''];
            extract(['title' => $title, 'content' => $content, 'config' => $config, 'shop' => $shop]);
            include $layoutFile;
        } else {
            echo "<!DOCTYPE html><html><head><title>{$title}</title></head><body>{$content}</body></html>";
        }
        exit;
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
        error_log("Controller::requireAuth - Checking authentication");
        $shop = $this->getShop();
        
        if (!$shop) {
            error_log("Controller::requireAuth - No shop found in session");
            $shopParam = $_GET['shop'] ?? '';
            
            if ($shopParam) {
                error_log("Controller::requireAuth - Redirecting to install with shop: {$shopParam}");
                $this->redirect('/auth/install?shop=' . urlencode($shopParam));
            } else {
                error_log("Controller::requireAuth - No shop parameter, redirecting to install");
                $this->redirect('/auth/install');
            }
        }
        
        error_log("Controller::requireAuth - Shop authenticated: {$shop['shop_domain']}");
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

