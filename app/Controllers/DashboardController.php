<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Export;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            error_log("=== Dashboard::index Redirecting to /reports ===");
            
            // Validate auth (optional, but good practice before redirecting inside app)
            $shop = $this->requireAuth();
            
            // Redirect to /reports to match the desired URL structure
            // Ensure we preserve ALL query parameters from Shopify (host, shop, hmac, timestamp)
            $queryParams = $_GET;
            
            // Explicitly ensure report-pro specific params don't interfere if present
            unset($queryParams['url']); // Remove the router url param if present
            
            $queryString = http_build_query($queryParams);
            $url = '/reports' . ($queryString ? '?' . $queryString : '');
            
            error_log("Dashboard: Redirecting to {$url}");
            
            // Use native header redirect to be absolutely sure
            header("Location: " . $url);
            exit;
        } catch (\Exception $e) {
            error_log("Dashboard Redirect Exception: " . $e->getMessage());
            // If auth fails, requireAuth will handle redirect usually, but safety net:
            $this->redirect('/auth/install');
        }
    }
}

