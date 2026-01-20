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
        $dashboardCategories = $this->getDashboardCategories();

        $this->view->render('reports/index', [
            'shop' => $shop,
            'reports' => $reports,
            'dashboardCategories' => $dashboardCategories,
            'search' => $search,
            'config' => $this->config
        ]);
    }

    private function getDashboardCategories()
    {
        return [
            'agreement_lines' => [
                'title' => 'Agreement lines (formerly order lines)',
                'items' => [
                    ['name' => 'All-time sales', 'url' => '/reports/predefined/all_time_sales'],
                    ['name' => 'Items pending fulfillment', 'url' => '/reports/predefined/pending_fulfillment'],
                    ['name' => 'Line item details', 'url' => '/reports/predefined/line_items'],
                    ['name' => 'Monthly cohorts', 'url' => '/reports/predefined/monthly_cohorts'],
                    ['name' => 'Monthly sales', 'url' => '/reports/predefined/monthly_sales'],
                    ['name' => 'Monthly sales by channel', 'url' => '/reports/predefined/monthly_sales_channel'],
                    ['name' => 'Monthly sales by POS location', 'url' => '/reports/predefined/sales_pos_location'],
                    ['name' => 'Monthly sales by POS user', 'url' => '/reports/predefined/sales_pos_user'],
                    ['name' => 'Monthly sales by product', 'url' => '/reports/predefined/sales_product'],
                    ['name' => 'Monthly sales by product type', 'url' => '/reports/predefined/sales_type'],
                    ['name' => 'Monthly sales by product variant', 'url' => '/reports/predefined/sales_variant'],
                    ['name' => 'Monthly sales by shipping country, state', 'url' => '/reports/predefined/sales_shipping'],
                    ['name' => 'Monthly sales by SKU', 'url' => '/reports/predefined/sales_sku'],
                    ['name' => 'Monthly sales by vendor', 'url' => '/reports/predefined/sales_vendor'],
                    ['name' => 'Refunds', 'url' => '/reports/predefined/refunds'],
                    ['name' => 'Sales by channel', 'url' => '/reports/predefined/sales_channel'],
                    ['name' => 'Sales by customer', 'url' => '/reports/predefined/sales_customer'],
                    ['name' => 'Sales by discount code', 'url' => '/reports/predefined/sales_discount'],
                    ['name' => 'Sales by product', 'url' => '/reports/predefined/sales_by_product'],
                    ['name' => 'Sales by product type', 'url' => '/reports/predefined/sales_by_type'],
                    ['name' => 'Sales by product variant', 'url' => '/reports/predefined/sales_by_variant'],
                    ['name' => 'Sales by referring site', 'url' => '/reports/predefined/sales_ref_site'],
                    ['name' => 'Sales by SKU', 'url' => '/reports/predefined/sales_by_sku'],
                    ['name' => 'Sales by vendor', 'url' => '/reports/predefined/sales_by_vendor'],
                    ['name' => 'Sales over time', 'url' => '/reports/predefined/sales_over_time'],
                    ['name' => 'Sales over time - Gift cards', 'url' => '/reports/predefined/sales_gift_cards'],
                    ['name' => 'Sales over time by channel', 'url' => '/reports/predefined/sales_time_channel'],
                    ['name' => 'Sales over time by POS location', 'url' => '/reports/predefined/sales_time_pos'],
                    ['name' => 'Sales over time by POS user', 'url' => '/reports/predefined/sales_time_user'],
                    ['name' => 'Sales over time by product', 'url' => '/reports/predefined/sales_time_product'],
                    ['name' => 'Sales over time by product type', 'url' => '/reports/predefined/sales_time_type'],
                    ['name' => 'Sales over time by product variant', 'url' => '/reports/predefined/sales_time_variant'],
                    ['name' => 'Sales over time by referring site', 'url' => '/reports/predefined/sales_time_ref'],
                    ['name' => 'Sales over time by SKU', 'url' => '/reports/predefined/sales_time_sku'],
                    ['name' => 'Sales over time by UTM medium', 'url' => '/reports/predefined/sales_time_utm_medium'],
                    ['name' => 'Sales over time by UTM source', 'url' => '/reports/predefined/sales_time_utm_source'],
                    ['name' => 'Sales over time by vendor', 'url' => '/reports/predefined/sales_time_vendor'],
                    ['name' => 'Tax collected per month', 'url' => '/reports/predefined/tax_monthly']
                ]
            ],
            'orders' => [
                'title' => 'Orders',
                'items' => [
                    ['name' => 'Average order value over time', 'url' => '/reports/predefined/aov_time'],
                    ['name' => 'Browser share over time', 'url' => '/reports/predefined/browser_share'],
                    ['name' => 'Device type share over time', 'url' => '/reports/predefined/device_share'],
                    ['name' => 'First-time vs returning customer orders', 'url' => '/reports/predefined/new_vs_returning'],
                    ['name' => 'High-risk orders', 'url' => '/reports/predefined/risk_orders'],
                    ['name' => 'Order details', 'url' => '/reports/predefined/order_details'],
                    ['name' => 'Orders by channel', 'url' => '/reports/predefined/orders_channel'],
                    ['name' => 'Orders by country', 'url' => '/reports/predefined/orders_country'],
                    ['name' => 'Orders by POS location', 'url' => '/reports/predefined/orders_pos_loc'],
                    ['name' => 'Orders by POS user', 'url' => '/reports/predefined/orders_pos_user'],
                    ['name' => 'Orders by referring site', 'url' => '/reports/predefined/orders_ref_site'],
                    ['name' => 'Orders by UTM campaign', 'url' => '/reports/predefined/orders_utm_campaign'],
                    ['name' => 'Orders by UTM medium', 'url' => '/reports/predefined/orders_utm_medium'],
                    ['name' => 'Orders by UTM source', 'url' => '/reports/predefined/orders_utm_source'],
                    ['name' => 'Orders pending fulfillment', 'url' => '/reports/predefined/orders_pending'],
                    ['name' => 'Total order value by channel', 'url' => '/reports/predefined/total_value_channel'],
                    ['name' => 'Total order value by country', 'url' => '/reports/predefined/total_value_country'],
                    ['name' => 'Total order value by referring site', 'url' => '/reports/predefined/total_value_ref'],
                    ['name' => 'Total orders by day and hour', 'url' => '/reports/predefined/orders_day_hour']
                ]
            ],
            'transactions' => [
                'title' => 'Transactions',
                'items' => [
                    ['name' => 'All transactions', 'url' => '/reports/predefined/all_transactions'],
                    ['name' => 'Failed transactions', 'url' => '/reports/predefined/failed_transactions'],
                    ['name' => 'Gift card transactions', 'url' => '/reports/predefined/gift_card_transactions'],
                    ['name' => 'Gift card transactions over time', 'url' => '/reports/predefined/gift_card_trans_time'],
                    ['name' => 'Monthly transactions by payment gateway', 'url' => '/reports/predefined/trans_monthly_gateway'],
                    ['name' => 'Monthly transactions per user', 'url' => '/reports/predefined/trans_monthly_user'],
                    ['name' => 'PayPal reconciliation', 'url' => '/reports/predefined/paypal_recon'],
                    ['name' => 'Pending transactions', 'url' => '/reports/predefined/pending_trans'],
                    ['name' => 'Total transactions value over time', 'url' => '/reports/predefined/total_trans_value_time'],
                    ['name' => 'Total transactions value per gateway over time', 'url' => '/reports/predefined/total_trans_value_gateway'],
                    ['name' => 'Volume per payment gateway', 'url' => '/reports/predefined/volume_gateway']
                ]
            ],
            'product_variants' => [
                'title' => 'Product variants',
                'items' => [
                    ['name' => 'Inventory', 'url' => '/reports/predefined/inventory'],
                    ['name' => 'Inventory by product', 'url' => '/reports/predefined/inventory_product'],
                    ['name' => 'Inventory by product type', 'url' => '/reports/predefined/inventory_type'],
                    ['name' => 'Inventory by SKU', 'url' => '/reports/predefined/inventory_sku'],
                    ['name' => 'Inventory by variant', 'url' => '/reports/predefined/inventory_variant'],
                    ['name' => 'Inventory by vendor', 'url' => '/reports/predefined/inventory_vendor'],
                    ['name' => 'Pending fulfillments', 'url' => '/reports/predefined/pending_fulfillments_var'],
                    ['name' => 'Total inventory summary', 'url' => '/reports/predefined/total_inventory'],
                    ['name' => 'Variant costs', 'url' => '/reports/predefined/variant_costs'],
                    ['name' => 'Variants without cost', 'url' => '/reports/predefined/variants_no_cost']
                ]
            ],
            'disputes' => [
                'title' => 'Disputes',
                'items' => [
                    ['name' => 'Monthly disputes', 'url' => '/reports/predefined/monthly_disputes'],
                    ['name' => 'Pending disputes', 'url' => '/reports/predefined/pending_disputes']
                ]
            ],
            'users' => [
                'title' => 'Users',
                'items' => [
                    ['name' => 'Users', 'url' => '/reports/predefined/users_list']
                ]
            ],
            'payout_transactions' => [
                'title' => 'Payout transactions',
                'items' => [
                    ['name' => 'Payout details', 'url' => '/reports/predefined/payout_details'],
                    ['name' => 'Pending payout details', 'url' => '/reports/predefined/pending_payouts']
                ]
            ],
            'market_regions' => [
                'title' => 'Market regions',
                'items' => [
                    ['name' => 'Markets', 'url' => '/reports/predefined/markets']
                ]
            ],
            'customers' => [
                'title' => 'Customers',
                'items' => [
                    ['name' => 'Customers', 'url' => '/reports/predefined/customers'],
                    ['name' => 'Total customers per country', 'url' => '/reports/predefined/customers_country']
                ]
            ],
            'products' => [
                'title' => 'Products',
                'items' => [
                    ['name' => 'All products', 'url' => '/reports/predefined/all_products'],
                    ['name' => 'Total products by type', 'url' => '/reports/predefined/products_type'],
                    ['name' => 'Total products by vendor', 'url' => '/reports/predefined/products_vendor']
                ]
            ],
            'inventory_levels' => [
                'title' => 'Inventory levels',
                'items' => [
                    ['name' => 'Inventory by location', 'url' => '/reports/predefined/inv_location'],
                    ['name' => 'Inventory by location by product', 'url' => '/reports/predefined/inv_loc_prod'],
                    ['name' => 'Inventory by location by product type', 'url' => '/reports/predefined/inv_loc_type'],
                    ['name' => 'Inventory by location by variant', 'url' => '/reports/predefined/inv_loc_var'],
                    ['name' => 'Inventory by location by vendor', 'url' => '/reports/predefined/inv_loc_vendor'],
                    ['name' => 'Quantity by location by variant', 'url' => '/reports/predefined/qty_loc_var']
                ]
            ],
            'gift_cards' => [
                'title' => 'Gift cards',
                'items' => [
                    ['name' => 'Active gift cards', 'url' => '/reports/predefined/active_gift_cards'],
                    ['name' => 'Monthly issued gift cards by app', 'url' => '/reports/predefined/gift_cards_app'],
                    ['name' => 'Monthly issued gift cards by source', 'url' => '/reports/predefined/gift_cards_source'],
                    ['name' => 'Monthly issued gift cards by user', 'url' => '/reports/predefined/gift_cards_user'],
                    ['name' => 'Total value issued by user over time', 'url' => '/reports/predefined/gift_cards_val_user']
                ]
            ],
            'draft_order_lines' => [
                'title' => 'Draft order lines',
                'items' => [
                    ['name' => 'Pending draft orders', 'url' => '/reports/predefined/pending_drafts'],
                    ['name' => 'Pending draft orders by product variant', 'url' => '/reports/predefined/pending_drafts_var']
                ]
            ],
            'payouts' => [
                'title' => 'Payouts',
                'items' => [
                    ['name' => 'Payout summary', 'url' => '/reports/predefined/payout_summary']
                ]
            ],
            'line_item_attributed_staffs' => [
                'title' => 'Line item attributed staffs',
                'items' => [
                    ['name' => 'Monthly sales attribution by staff', 'url' => '/reports/predefined/sales_staff']
                ]
            ],
            'transaction_fees' => [
                'title' => 'Transaction fees',
                'items' => [
                    ['name' => 'Monthly transaction fees', 'url' => '/reports/predefined/monthly_fees'],
                    ['name' => 'Transaction fee details', 'url' => '/reports/predefined/fee_details']
                ]
            ],
            'collects' => [
                'title' => 'Collects',
                'items' => [
                    ['name' => 'Total Products by Collection', 'url' => '/reports/predefined/products_collection']
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
