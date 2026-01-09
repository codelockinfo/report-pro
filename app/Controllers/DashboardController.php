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
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $reports = $reportModel->findByShop($shop['id'], ['is_custom' => 1]);
        
        $scheduleModel = new Schedule();
        $schedules = $scheduleModel->findByShop($shop['id']);
        
        $exportModel = new Export();
        $recentExports = $exportModel->findByShop($shop['id'], 5);

        $this->view->render('dashboard/index', [
            'shop' => $shop,
            'reports' => $reports,
            'schedules' => $schedules,
            'recentExports' => $recentExports,
            'config' => $this->config
        ]);
    }
}

