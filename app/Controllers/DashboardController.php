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
            error_log("=== Dashboard::index Started ===");
            
            $shop = $this->requireAuth();
            
            if (!$shop) {
                error_log("Dashboard::index - No shop found in session");
                $shopParam = $_GET['shop'] ?? '';
                if ($shopParam) {
                    error_log("Dashboard::index - Redirecting to install with shop: {$shopParam}");
                    $this->redirect('/auth/install?shop=' . urlencode($shopParam));
                } else {
                    error_log("Dashboard::index - No shop parameter, redirecting to install");
                    $this->redirect('/auth/install');
                }
                return;
            }
            
            error_log("Dashboard::index - Shop authenticated: {$shop['shop_domain']} (ID: {$shop['id']})");
            
            $reportModel = new Report();
            $reports = $reportModel->findByShop($shop['id'], ['is_custom' => 1]);
            error_log("Dashboard::index - Found " . count($reports) . " custom reports");
            
            $scheduleModel = new Schedule();
            $schedules = $scheduleModel->findByShop($shop['id']);
            error_log("Dashboard::index - Found " . count($schedules) . " schedules");
            
            $exportModel = new Export();
            $recentExports = $exportModel->findByShop($shop['id'], 5);
            error_log("Dashboard::index - Found " . count($recentExports) . " recent exports");

            error_log("Dashboard::index - Rendering view");
            $this->view->render('dashboard/index', [
                'shop' => $shop,
                'reports' => $reports,
                'schedules' => $schedules,
                'recentExports' => $recentExports,
                'config' => $this->config
            ]);
            
            error_log("=== Dashboard::index Completed ===");
            
        } catch (\Exception $e) {
            error_log("Dashboard::index Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Show user-friendly error
            http_response_code(500);
            die("An error occurred loading the dashboard: " . htmlspecialchars($e->getMessage()));
        }
    }
}

