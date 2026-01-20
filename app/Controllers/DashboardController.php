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
            // Append current query parameters (shop, host, etc)
            $queryParams = $_GET;
            $queryString = http_build_query($queryParams);
            $url = '/reports' . ($queryString ? '?' . $queryString : '');
            
            $this->redirect($url);
        } catch (\Exception $e) {
            error_log("Dashboard Redirect Exception: " . $e->getMessage());
            // If auth fails, requireAuth will handle redirect usually, but safety net:
            $this->redirect('/auth/install');
        }
    }
}

