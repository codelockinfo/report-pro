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

        // Handle type parameter for redirection to specific report
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            return $this->singleReport();
        }
        
        $reportModel = new Report();
        $search = $_GET['search'] ?? '';
        
        $filters = [];
        if ($search) {
            $filters['search'] = $search;
        }
        
        $reports = $reportModel->findByShop($shop['id'], $filters);
        $dashboardCategories = $this->getDashboardCategories();

        $this->view->render('reports/index', [
            'shop' => $shop,
            'reports' => $reports,
            'dashboardCategories' => $dashboardCategories,
            'search' => $search,
            'config' => $this->config,
            'host' => $_GET['host'] ?? ''
        ]);
    }

    public function singleReport()
    {
        $shop = $this->requireAuth();
        $type = $_GET['type'] ?? '';
        
        if (empty($type)) {
            $this->redirect('/reports');
        }

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
            $report = $reportModel->getWithColumns($reportId);
        } else {
            $reportId = $existing[0]['id'];
            
            // Auto-update config and basic info if it differs (to apply fixes/changes to predefined reports)
            $needsUpdate = false;
            $updateData = [];
            
            if ($existing[0]['query_config'] !== json_encode($reportConfig['config'])) {
                $updateData['query_config'] = json_encode($reportConfig['config']);
                $needsUpdate = true;
            }
            
            if ($existing[0]['name'] !== $reportConfig['name']) {
                $updateData['name'] = $reportConfig['name'];
                $needsUpdate = true;
            }

            if ($existing[0]['description'] !== $reportConfig['description']) {
                $updateData['description'] = $reportConfig['description'];
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                error_log("ReportController::singleReport - Updating predefined report {$type}");
                $reportModel->update($reportId, $updateData);
            }

            $report = $reportModel->getWithColumns($reportId);
        }

        // Update view counts and last viewed timestamp
        $reportModel->update($reportId, [
            'view_count' => ($report['view_count'] ?? 0) + 1,
            'last_viewed_at' => date('Y-m-d H:i:s')
        ]);

        // Get results
        $resultModel = new ReportResult();
        $result = $resultModel->findByReport($reportId);

        $this->view->render('reports/report', [
            'shop' => $shop,
            'report' => $report,
            'result' => $result,
            'config' => $this->config,
            'host' => $_GET['host'] ?? ''
        ]);
    }

    private function getDashboardCategories()
    {
        return [
            'agreement_lines' => [
                'title' => 'Sales',
                'items' => [
                    ['name' => 'All-time sales', 'url' => '/report?type=all_time_sales'],
                    ['name' => 'Items pending fulfillment', 'url' => '/report?type=pending_fulfillment'],
                    ['name' => 'Line item details', 'url' => '/report?type=line_items'],
                    ['name' => 'Monthly cohorts', 'url' => '/report?type=monthly_cohorts'],
                    ['name' => 'Monthly sales', 'url' => '/report?type=monthly_sales'],
                    ['name' => 'Monthly sales by channel', 'url' => '/report?type=monthly_sales_channel'],
                    ['name' => 'Monthly sales by POS location', 'url' => '/report?type=sales_pos_location'],
                    ['name' => 'Monthly sales by POS user', 'url' => '/report?type=sales_pos_user'],
                    ['name' => 'Monthly sales by product', 'url' => '/report?type=sales_product'],
                    ['name' => 'Monthly sales by product type', 'url' => '/report?type=sales_type'],
                    ['name' => 'Monthly sales by product variant', 'url' => '/report?type=sales_variant'],
                    ['name' => 'Monthly sales by shipping country, state', 'url' => '/report?type=sales_shipping'],
                    ['name' => 'Monthly sales by SKU', 'url' => '/report?type=sales_sku'],
                    ['name' => 'Monthly sales by vendor', 'url' => '/report?type=sales_vendor'],
                    ['name' => 'Refunds', 'url' => '/report?type=refunds'],
                    ['name' => 'Sales by channel', 'url' => '/report?type=sales_channel'],
                    ['name' => 'Sales by customer', 'url' => '/report?type=sales_customer'],
                    ['name' => 'Sales by discount code', 'url' => '/report?type=sales_discount'],
                    ['name' => 'Sales by product', 'url' => '/report?type=sales_by_product'],
                    ['name' => 'Sales by product type', 'url' => '/report?type=sales_by_type'],
                    ['name' => 'Sales by product variant', 'url' => '/report?type=sales_by_variant'],
                    ['name' => 'Sales by referring site', 'url' => '/report?type=sales_ref_site'],
                    ['name' => 'Sales by SKU', 'url' => '/report?type=sales_by_sku'],
                    ['name' => 'Sales by vendor', 'url' => '/report?type=sales_by_vendor'],
                    ['name' => 'Sales over time', 'url' => '/report?type=sales_over_time'],
                    ['name' => 'Sales over time - Gift cards', 'url' => '/report?type=sales_gift_cards'],
                    ['name' => 'Sales over time by channel', 'url' => '/report?type=sales_time_channel'],
                    ['name' => 'Sales over time by POS location', 'url' => '/report?type=sales_time_pos'],
                    ['name' => 'Sales over time by POS user', 'url' => '/report?type=sales_time_user'],
                    ['name' => 'Sales over time by product', 'url' => '/report?type=sales_time_product'],
                    ['name' => 'Sales over time by product type', 'url' => '/report?type=sales_time_type'],
                    ['name' => 'Sales over time by product variant', 'url' => '/report?type=sales_time_variant'],
                    ['name' => 'Sales over time by referring site', 'url' => '/report?type=sales_time_ref'],
                    ['name' => 'Sales over time by SKU', 'url' => '/report?type=sales_time_sku'],
                    ['name' => 'Sales over time by UTM medium', 'url' => '/report?type=sales_time_utm_medium'],
                    ['name' => 'Sales over time by UTM source', 'url' => '/report?type=sales_time_utm_source'],
                    ['name' => 'Sales over time by vendor', 'url' => '/report?type=sales_time_vendor'],
                    ['name' => 'Tax collected per month', 'url' => '/report?type=tax_monthly']
                ]
            ],
            'orders' => [
                'title' => 'Orders',
                'items' => [
                    ['name' => 'Average order value over time', 'url' => '/report?type=aov_time'],
                    ['name' => 'Browser share over time', 'url' => '/report?type=browser_share'],
                    ['name' => 'Device type share over time', 'url' => '/report?type=device_share'],
                    ['name' => 'First-time vs returning customer orders', 'url' => '/report?type=new_vs_returning'],
                    ['name' => 'High-risk orders', 'url' => '/report?type=risk_orders'],
                    ['name' => 'Order details', 'url' => '/report?type=order_details'],
                    ['name' => 'Orders by channel', 'url' => '/report?type=orders_channel'],
                    ['name' => 'Orders by country', 'url' => '/report?type=orders_country'],
                    ['name' => 'Orders by POS location', 'url' => '/report?type=orders_pos_loc'],
                    ['name' => 'Orders by POS user', 'url' => '/report?type=orders_pos_user'],
                    ['name' => 'Orders by referring site', 'url' => '/report?type=orders_ref_site'],
                    ['name' => 'Orders by UTM campaign', 'url' => '/report?type=orders_utm_campaign'],
                    ['name' => 'Orders by UTM medium', 'url' => '/report?type=orders_utm_medium'],
                    ['name' => 'Orders by UTM source', 'url' => '/report?type=orders_utm_source'],
                    ['name' => 'Orders pending fulfillment', 'url' => '/report?type=orders_pending'],
                    ['name' => 'Total order value by channel', 'url' => '/report?type=total_value_channel'],
                    ['name' => 'Total order value by country', 'url' => '/report?type=total_value_country'],
                    ['name' => 'Total order value by referring site', 'url' => '/report?type=total_value_ref'],
                    ['name' => 'Total orders by day and hour', 'url' => '/report?type=orders_day_hour']
                ]
            ],
            'transactions' => [
                'title' => 'Transactions',
                'items' => [
                    ['name' => 'All transactions', 'url' => '/report?type=all_transactions'],
                    ['name' => 'Failed transactions', 'url' => '/report?type=failed_transactions'],
                    ['name' => 'Gift card transactions', 'url' => '/report?type=gift_card_transactions'],
                    ['name' => 'Gift card transactions over time', 'url' => '/report?type=gift_card_trans_time'],
                    ['name' => 'Monthly transactions by payment gateway', 'url' => '/report?type=trans_monthly_gateway'],
                    ['name' => 'Monthly transactions per user', 'url' => '/report?type=trans_monthly_user'],
                    ['name' => 'PayPal reconciliation', 'url' => '/report?type=paypal_recon'],
                    ['name' => 'Pending transactions', 'url' => '/report?type=pending_trans'],
                    ['name' => 'Total transactions value over time', 'url' => '/report?type=total_trans_value_time'],
                    ['name' => 'Total transactions value per gateway over time', 'url' => '/report?type=total_trans_value_gateway'],
                    ['name' => 'Volume per payment gateway', 'url' => '/report?type=volume_gateway']
                ]
            ],
            'product_variants' => [
                'title' => 'Product variants',
                'items' => [
                    ['name' => 'Inventory', 'url' => '/report?type=inventory'],
                    ['name' => 'Inventory by product', 'url' => '/report?type=inventory_product'],
                    ['name' => 'Inventory by product type', 'url' => '/report?type=inventory_type'],
                    ['name' => 'Inventory by SKU', 'url' => '/report?type=inventory_sku'],
                    ['name' => 'Inventory by variant', 'url' => '/report?type=inventory_variant'],
                    ['name' => 'Inventory by vendor', 'url' => '/report?type=inventory_vendor'],
                    ['name' => 'Pending fulfillments', 'url' => '/report?type=pending_fulfillments_var'],
                    ['name' => 'Total inventory summary', 'url' => '/report?type=total_inventory'],
                    ['name' => 'Variant costs', 'url' => '/report?type=variant_costs'],
                    ['name' => 'Variants without cost', 'url' => '/report?type=variants_no_cost']
                ]
            ],
            'disputes' => [
                'title' => 'Disputes',
                'items' => [
                    ['name' => 'Monthly disputes', 'url' => '/report?type=monthly_disputes'],
                    ['name' => 'Pending disputes', 'url' => '/report?type=pending_disputes']
                ]
            ],
            'users' => [
                'title' => 'Users',
                'items' => [
                    ['name' => 'Users', 'url' => '/report?type=users_list']
                ]
            ],
            'payout_transactions' => [
                'title' => 'Payout transactions',
                'items' => [
                    ['name' => 'Payout details', 'url' => '/report?type=payout_details'],
                    ['name' => 'Pending payout details', 'url' => '/report?type=pending_payouts']
                ]
            ],
            'market_regions' => [
                'title' => 'Market regions',
                'items' => [
                    ['name' => 'Markets', 'url' => '/report?type=markets']
                ]
            ],
            'customers' => [
                'title' => 'Customers',
                'items' => [
                    ['name' => 'Customers', 'url' => '/report?type=customers'],
                    ['name' => 'Total customers per country', 'url' => '/report?type=customers_country']
                ]
            ],
            'products' => [
                'title' => 'Products',
                'items' => [
                    ['name' => 'All products', 'url' => '/report?type=all_products'],
                    ['name' => 'Total products by type', 'url' => '/report?type=products_type'],
                    ['name' => 'Total products by vendor', 'url' => '/report?type=products_vendor']
                ]
            ],
            'inventory_levels' => [
                'title' => 'Inventory levels',
                'items' => [
                    ['name' => 'Inventory by location', 'url' => '/report?type=inv_location'],
                    ['name' => 'Inventory by location by product', 'url' => '/report?type=inv_loc_prod'],
                    ['name' => 'Inventory by location by product type', 'url' => '/report?type=inv_loc_type'],
                    ['name' => 'Inventory by location by variant', 'url' => '/report?type=inv_loc_var'],
                    ['name' => 'Inventory by location by vendor', 'url' => '/report?type=inv_loc_vendor'],
                    ['name' => 'Quantity by location by variant', 'url' => '/report?type=qty_loc_var']
                ]
            ],
            'gift_cards' => [
                'title' => 'Gift cards',
                'items' => [
                    ['name' => 'Active gift cards', 'url' => '/report?type=active_gift_cards'],
                    ['name' => 'Monthly issued gift cards by app', 'url' => '/report?type=gift_cards_app'],
                    ['name' => 'Monthly issued gift cards by source', 'url' => '/report?type=gift_cards_source'],
                    ['name' => 'Monthly issued gift cards by user', 'url' => '/report?type=gift_cards_user'],
                    ['name' => 'Total value issued by user over time', 'url' => '/report?type=gift_cards_val_user']
                ]
            ],
            'draft_order_lines' => [
                'title' => 'Draft order lines',
                'items' => [
                    ['name' => 'Pending draft orders', 'url' => '/report?type=pending_drafts'],
                    ['name' => 'Pending draft orders by product variant', 'url' => '/report?type=pending_drafts_var']
                ]
            ],
            'payouts' => [
                'title' => 'Payouts',
                'items' => [
                    ['name' => 'Payout summary', 'url' => '/report?type=payout_summary']
                ]
            ],
            'line_item_attributed_staffs' => [
                'title' => 'Line item attributed staffs',
                'items' => [
                    ['name' => 'Monthly sales attribution by staff', 'url' => '/report?type=sales_staff']
                ]
            ],
            'transaction_fees' => [
                'title' => 'Transaction fees',
                'items' => [
                    ['name' => 'Monthly transaction fees', 'url' => '/report?type=monthly_fees'],
                    ['name' => 'Transaction fee details', 'url' => '/report?type=fee_details']
                ]
            ],
            'collects' => [
                'title' => 'Collects',
                'items' => [
                    ['name' => 'Total Products by Collection', 'url' => '/report?type=products_collection']
                ]
            ]
        ];
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

        // Update view counts and last viewed timestamp
        $reportModel->update($id, [
            'view_count' => ($report['view_count'] ?? 0) + 1,
            'last_viewed_at' => date('Y-m-d H:i:s')
        ]);

        $this->view->render('reports/report', [
            'shop' => $shop,
            'report' => $report,
            'result' => $result,
            'config' => $this->config
        ]);
    }

    public function run($id)
    {
        error_log("ReportController::run - Starting for report ID: {$id}");
        $shop = $this->requireAuth();
        error_log("ReportController::run - Authenticated shop: " . ($shop['shop_domain'] ?? 'NONE'));
        
        $reportModel = new Report();
        $report = $reportModel->find($id);

        if (!$report) {
            error_log("ReportController::run - Report {$id} not found in database");
            $this->json(['error' => 'Report not found'], 404);
        }

        if ($report['shop_id'] != $shop['id']) {
            error_log("ReportController::run - Shop mismatch: Report shop ID {$report['shop_id']} vs Authenticated shop ID {$shop['id']}");
            $this->json(['error' => 'Report not found'], 404);
        }

        try {
            error_log("ReportController::run - Creating ShopifyService");
            $shopifyService = new \App\Services\ShopifyService(
                $shop['shop_domain'],
                $shop['access_token']
            );

            error_log("ReportController::run - Creating ReportBuilderService");
            $reportBuilder = new ReportBuilderService($shopifyService, $shop['id']);
            
            error_log("ReportController::run - Executing report");
            $operationId = $reportBuilder->executeReport($id);
            error_log("ReportController::run - Report executed, Operation ID: {$operationId}");

            // Immediate processing for local dev (since no webhooks)
            if (getenv('APP_ENV') === 'local') {
                error_log("ReportController::run - Local environment detected, processing result immediately");
                $reportBuilder->processBulkOperationResult($operationId, $id);
                error_log("ReportController::run - Result processed");
            }

            $this->json([
                'success' => true,
                'operation_id' => $operationId,
                'message' => 'Report generation started'
            ]);
        } catch (\Exception $e) {
            error_log("ReportController::run - EXCEPTION: " . $e->getMessage());
            error_log("ReportController::run - TRACE: " . $e->getTraceAsString());
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
            // Update config in case it changed in code (like adding columns)
            $reportModel->update($reportId, [
                'name' => $reportConfig['name'],
                'description' => $reportConfig['description'],
                'query_config' => json_encode($reportConfig['config'])
            ]);
        }

        $this->redirect("/reports/{$reportId}");
    }

    private function getPredefinedReports()
    {
        $reports = [];

        // 1. Orders / Sales Reports (Dataset: orders)
        $orderReports = [
            'orders', 'monthly_cohorts', 'monthly_sales', 
            'monthly_sales_channel', 'sales_pos_location', 'sales_pos_user', 'sales_product', 'sales_type', 
            'sales_variant', 'sales_shipping', 'sales_sku', 'sales_vendor', 'refunds', 'sales_channel', 
            'sales_customer', 'sales_discount', 'sales_by_product', 'sales_by_type', 'sales_by_variant', 
            'sales_ref_site', 'sales_by_sku', 'sales_by_vendor', 'sales_over_time', 'sales_gift_cards', 
            'sales_time_channel', 'sales_time_pos', 'sales_time_user', 'sales_time_product', 'sales_time_type', 
            'sales_time_variant', 'sales_time_ref', 'sales_time_sku', 'sales_time_utm_medium', 'sales_time_utm_source', 
            'sales_time_vendor', 'tax_monthly', 'device_share', 'new_vs_returning', 
            'risk_orders', 'order_details', 'orders_channel', 'orders_country', 'orders_pos_loc', 'orders_pos_user', 
            'orders_ref_site', 'orders_utm_campaign', 'orders_utm_medium', 'orders_utm_source', 'orders_pending', 
            'total_value_channel', 'total_value_country', 'total_value_ref', 'orders_day_hour',
            'line_item_attributed_staffs', 'sales_staff'
        ];

        foreach ($orderReports as $type) {
            $reports[$type] = [
                'name' => ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . str_replace('_', ' ', $type),
                'config' => [
                    'dataset' => 'orders',
                    'columns' => ['id', 'name', 'created_at', 'total_price', 'financial_status', 'fulfillment_status', 'country']
                ]
            ];
        }

        // Specific Browser Share
        $reports['browser_share'] = [
            'name' => 'Browser Share',
            'description' => 'Breakdown of orders by customers\' web browser',
            'config' => [
                'dataset' => 'browser_share',
                'visual_type' => 'chart',
                'columns' => ['browser', 'sessions_count']
            ]
        ];

        // Specific AOV Report with Chart
        $reports['aov_time'] = [
            'name' => 'Average order value over time',
            'description' => 'Average order value over time',
            'config' => [
                'dataset' => 'aov_time',
                'visual_type' => 'chart',
                'columns' => ['date', 'average_order_value']
            ]
        ];

        // Specific All-time sales report
        $reports['all_time_sales'] = [
            'name' => 'All-time Sales',
            'description' => 'Summary of sales over all time',
            'config' => [
                'dataset' => 'sales_summary',
                'columns' => [
                    'total_orders', 
                    'total_gross_sales', 
                    'total_discounts', 
                    'total_refunds', 
                    'total_net_sales', 
                    'total_taxes', 
                    'total_shipping', 
                    'total_sales', 
                    'total_cost_of_goods_sold', 
                    'total_gross_margin'
                ]
            ]
        ];

        // Special Order Reports
        $reports['line_items'] = [
            'name' => 'Line Item Details',
            'description' => 'Detailed report of all order line items',
            'config' => [
                'dataset' => 'line_items',
                'columns' => ['id', 'title', 'quantity', 'price', 'sku', 'vendor']
            ]
        ];

        $reports['pending_fulfillment'] = [
            'name' => 'Pending Fulfillment',
            'description' => 'List of line items waiting for fulfillment',
            'config' => [
                'dataset' => 'line_items',
                'columns' => ['id', 'title', 'quantity', 'sku', 'vendor'],
                'filters' => [['field' => 'fulfillment_status', 'operator' => '!=', 'value' => 'fulfilled']]
            ]
        ];

        // 2. Products / Inventory Reports (Dataset: products)
        $productReports = [
            'all_products', 'products_type', 'products_vendor', 'products_collection', 'inventory', 
            'inventory_product', 'inventory_type', 'inventory_sku', 'inventory_variant', 'inventory_vendor', 
            'pending_fulfillments_var', 'total_inventory', 'variant_costs', 'variants_no_cost', 'inv_location', 
            'inv_loc_prod', 'inv_loc_type', 'inv_loc_var', 'inv_loc_vendor', 'qty_loc_var', 'pending_drafts_var'
        ];

        foreach ($productReports as $type) {
            $reports[$type] = [
                'name' => ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . str_replace('_', ' ', $type),
                'config' => [
                    'dataset' => 'products',
                    'columns' => ['id', 'title', 'product_type', 'vendor', 'created_at', 'total_inventory', 'status']
                ]
            ];
        }

        $reports['inventory_levels'] = [
            'name' => 'Inventory Levels',
            'description' => 'Detailed inventory levels by location',
            'config' => [
                'dataset' => 'inventory_levels',
                'columns' => ['id', 'location_name', 'sku', 'available', 'updated_at']
            ]
        ];

        // 3. Customers Reports (Dataset: customers)
        $customerReports = [
            'customers', 'customers_country', 'users_list', 'markets'
        ];

        foreach ($customerReports as $type) {
            $reports[$type] = [
                'name' => ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . str_replace('_', ' ', $type),
                'config' => [
                    'dataset' => 'customers',
                    'columns' => ['id', 'full_name', 'email', 'created_at', 'orders_count', 'total_spent', 'country', 'accepts_marketing']
                ]
            ];
        }

        // 4. Transactions / Financial Reports (Dataset: transactions)
        $transactionReports = [
            'all_transactions', 'failed_transactions', 'gift_card_transactions', 'gift_card_trans_time', 
            'trans_monthly_gateway', 'trans_monthly_user', 'paypal_recon', 'pending_trans', 
            'total_trans_value_time', 'total_trans_value_gateway', 'volume_gateway', 'monthly_disputes', 
            'pending_disputes', 'payout_details', 'pending_payouts', 'monthly_fees', 'fee_details',
            'active_gift_cards', 'gift_cards_app', 'gift_cards_source', 'gift_cards_user', 'gift_cards_val_user',
            'payout_summary'
        ];

        foreach ($transactionReports as $type) {
            $reports[$type] = [
                'name' => ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . str_replace('_', ' ', $type),
                'config' => [
                    'dataset' => 'transactions',
                    'columns' => ['id', 'created_at', 'amount', 'currency_code', 'gateway', 'status', 'kind']
                ]
            ];
        }

        // 5. Draft Orders
        $reports['pending_drafts'] = [
            'name' => 'Pending Draft Orders',
            'description' => 'List of draft orders',
            'config' => [
                'dataset' => 'draft_orders',
                'columns' => ['id', 'name', 'created_at', 'total_price', 'status', 'email']
            ]
        ];

        return $reports;
    }
}
