<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportFilter;
use App\Models\ReportResult;
use App\Services\ShopifyService;
use App\Services\ReportBuilderService;

class ReportController extends Controller
{
    public function index()
    {
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $search = $_GET['search'] ?? '';
        
        $filters = [];
        if ($search) {
            $filters['search'] = $search;
        }
        
        $reports = $reportModel->findByShop($shop['id'], $filters);

        $this->view->render('reports/index', [
            'shop' => $shop,
            'reports' => $reports,
            'search' => $search,
            'config' => $this->config
        ]);
    }

    public function create()
    {
        $shop = $this->requireAuth();
        
        $this->view->render('reports/create', [
            'shop' => $shop,
            'config' => $this->config
        ]);
    }

    public function store()
    {
        $shop = $this->requireAuth();
        
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? null;
        $description = $_POST['description'] ?? '';
        $dataset = $_POST['dataset'] ?? 'orders';
        $columns = $_POST['columns'] ?? [];
        $filters = $_POST['filters'] ?? [];
        $groupBy = $_POST['group_by'] ?? null;
        $aggregations = $_POST['aggregations'] ?? [];

        if (empty($name)) {
            $this->json(['error' => 'Report name is required'], 400);
        }

        $queryConfig = [
            'dataset' => $dataset,
            'columns' => $columns,
            'filters' => $filters,
            'group_by' => $groupBy,
            'aggregations' => $aggregations
        ];

        $reportModel = new Report();
        $reportId = $reportModel->create([
            'shop_id' => $shop['id'],
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'query_config' => json_encode($queryConfig),
            'is_custom' => 1
        ]);

        // Save columns
        if (!empty($columns)) {
            $columnModel = new ReportColumn();
            $columnData = [];
            foreach ($columns as $index => $column) {
                $columnData[] = [
                    'name' => $column,
                    'label' => ucwords(str_replace('_', ' ', $column)),
                    'type' => 'string',
                    'visible' => 1
                ];
            }
            $columnModel->createMultiple($reportId, $columnData);
        }

        // Save filters
        if (!empty($filters)) {
            $filterModel = new ReportFilter();
            $filterModel->createMultiple($reportId, $filters);
        }

        $this->json(['success' => true, 'report_id' => $reportId]);
    }

    public function show($id)
    {
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->getWithColumns($id);

        if (!$report || $report['shop_id'] != $shop['id']) {
            http_response_code(404);
            die("Report not found");
        }

        $resultModel = new ReportResult();
        $result = $resultModel->findByReport($id);

        $this->view->render('reports/show', [
            'shop' => $shop,
            'report' => $report,
            'result' => $result,
            'config' => $this->config
        ]);
    }

    public function run($id)
    {
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->find($id);

        if (!$report || $report['shop_id'] != $shop['id']) {
            $this->json(['error' => 'Report not found'], 404);
        }

        try {
            $shopifyService = new \App\Services\ShopifyService(
                $shop['shop_domain'],
                $shop['access_token']
            );

            $reportBuilder = new ReportBuilderService($shopifyService, $shop['id']);
            $operationId = $reportBuilder->executeReport($id);

            $this->json([
                'success' => true,
                'operation_id' => $operationId,
                'message' => 'Report generation started'
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getData($id)
    {
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->find($id);

        if (!$report || $report['shop_id'] != $shop['id']) {
            $this->json(['error' => 'Report not found'], 404);
        }

        $resultModel = new ReportResult();
        $result = $resultModel->findByReport($id);

        if (!$result) {
            $this->json(['data' => [], 'total' => 0]);
        }

        $data = json_decode($result['result_data'], true);
        
        $this->json([
            'data' => $data,
            'total' => $result['total_records']
        ]);
    }

    public function predefined($type)
    {
        $shop = $this->requireAuth();
        
        $predefinedReports = $this->getPredefinedReports();
        
        if (!isset($predefinedReports[$type])) {
            http_response_code(404);
            die("Predefined report not found");
        }

        $reportConfig = $predefinedReports[$type];
        
        // Check if report exists, if not create it
        $reportModel = new Report();
        $existing = $reportModel->findAll([
            'shop_id' => $shop['id'],
            'category' => $type,
            'is_custom' => 0
        ]);

        if (empty($existing)) {
            $reportId = $reportModel->create([
                'shop_id' => $shop['id'],
                'name' => $reportConfig['name'],
                'category' => $type,
                'description' => $reportConfig['description'],
                'query_config' => json_encode($reportConfig['config']),
                'is_custom' => 0
            ]);
        } else {
            $reportId = $existing[0]['id'];
        }

        $this->redirect("/reports/{$reportId}");
    }

    private function getPredefinedReports()
    {
        return [
            'orders' => [
                'name' => 'Orders Over Time',
                'description' => 'View orders over time',
                'config' => [
                    'dataset' => 'orders',
                    'columns' => ['id', 'name', 'created_at', 'total_price', 'financial_status']
                ]
            ],
            // Add more predefined reports here
        ];
    }
}

