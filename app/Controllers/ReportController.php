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
            
            // Also sanitize columns for datasets where Shopify may block protected customer data.
            if ($existing[0]['query_config'] !== json_encode($reportConfig['config'])) {
                $updateData['query_config'] = json_encode($reportConfig['config']);
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



    public function toggleFavorite()
    {
        $shop = $this->requireAuth();
        $reportModel = new Report();
        
        $json = json_decode(file_get_contents('php://input'), true);
        $id = $json['id'] ?? null;
        $type = $json['type'] ?? null;
        
        $reportId = null;
        $report = null;

        if ($id) {
            $report = $reportModel->find($id);
            if ($report && $report['shop_id'] == $shop['id']) {
                $reportId = $id;
            }
        } elseif ($type) {
            // Check if predefined report exists in DB
            $existing = $reportModel->findAll([
                'shop_id' => $shop['id'],
                'category' => $type,
                'is_custom' => 0
            ]);
            
            if (!empty($existing)) {
                $report = $existing[0];
                $reportId = $report['id'];
            } else {
                // Create it first
                $predefinedReports = $this->getPredefinedReports();
                if (isset($predefinedReports[$type])) {
                    $config = $predefinedReports[$type];
                    $reportId = $reportModel->create([
                        'shop_id' => $shop['id'],
                        'name' => $config['name'],
                        'category' => $type,
                        'description' => $config['description'],
                        'query_config' => json_encode($config['config']),
                        'is_custom' => 0,
                        'is_favorite' => 0 // Will toggle below
                    ]);
                    $report = ['is_favorite' => 0]; // Mock for toggle logic
                }
            }
        }

        if (!$reportId) {
            return $this->json(['success' => false, 'error' => 'Report not found'], 404);
        }
        
        $isFavorite = $report['is_favorite'] ? 0 : 1;
        $result = $reportModel->update($reportId, ['is_favorite' => $isFavorite]);
        
        if ($result) {
            return $this->json(['success' => true, 'is_favorite' => $isFavorite, 'report_id' => $reportId]);
        } else {
            return $this->json(['success' => false, 'error' => 'Failed to update favorite status'], 500);
        }
    }

    private function getDashboardCategories()
    {
        return [
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
            'payouts' => [
                'title' => 'Payouts',
                'items' => [
                    ['name' => 'Payout summary', 'url' => '/report?type=payout_summary']
                ]
            ],
            'disputes' => [
                'title' => 'Disputes',
                'items' => [
                    ['name' => 'Monthly disputes', 'url' => '/report?type=monthly_disputes'],
                    ['name' => 'Pending disputes', 'url' => '/report?type=pending_disputes']
                ]
            ],
            'market_regions' => [
                'title' => 'Market regions',
                'items' => [
                    ['name' => 'Markets', 'url' => '/report?type=markets']
                ]
            ],
            'sales' => [
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
            'transaction_fees' => [
                'title' => 'Transaction fees',
                'items' => [
                    ['name' => 'Monthly transaction fees', 'url' => '/report?type=monthly_fees'],
                    ['name' => 'Transaction fee details', 'url' => '/report?type=fee_details']
                ]
            ],
            'users' => [
                'title' => 'Users',
                'items' => [
                    ['name' => 'Users', 'url' => '/report?type=users_list']
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
            'draft_order_lines' => [
                'title' => 'Draft order lines',
                'items' => [
                    ['name' => 'Pending draft orders', 'url' => '/report?type=pending_drafts'],
                    ['name' => 'Pending draft orders by product variant', 'url' => '/report?type=pending_drafts_var']
                ]
            ],
            'payout_transactions' => [
                'title' => 'Payout transactions',
                'items' => [
                    ['name' => 'Payout details', 'url' => '/report?type=payout_details'],
                    ['name' => 'Pending payout details', 'url' => '/report?type=pending_payouts']
                ]
            ],
            'line_item_attributed_staffs' => [
                'title' => 'Line item attributed staffs',
                'items' => [
                    ['name' => 'Monthly sales attribution by staff', 'url' => '/report?type=sales_staff']
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

        // AUTO-PATCH: Ensure customers dataset has updated_at column
        error_log("ReportController::run - Processing report: {$report['name']} (ID: {$id}), Category: {$report['category']}");
        
        $config = json_decode($report['query_config'], true) ?: [];
        if (isset($config['dataset']) && $config['dataset'] === 'customers') {
            $columns = $config['columns'] ?? [];
            if (!in_array('updated_at', $columns)) {
                error_log("ReportController::run - Auto-patching report {$id} to include updated_at");
                $columns[] = 'updated_at';
                $config['columns'] = $columns;
                $reportModel->update($id, ['query_config' => json_encode($config)]);
                // Reload report to get fresh config for execution
                $report = $reportModel->find($id); 
            }
        }

        // AUTO-PATCH: Ensure products dataset has price column
        if (isset($config['dataset']) && $config['dataset'] === 'products') {
            $columns = $config['columns'] ?? [];
            if (!in_array('price', $columns)) {
                error_log("ReportController::run - Auto-patching report {$id} to include price");
                // Insert after title if possible, otherwise append
                $found = array_search('title', $columns);
                if ($found !== false) {
                     array_splice($columns, $found + 1, 0, 'price');
                } else {
                     $columns[] = 'price';
                }
                $config['columns'] = $columns;
                $reportModel->update($id, ['query_config' => json_encode($config)]);
                // Reload report to get fresh config for execution
                $report = $reportModel->find($id); 
            }
        }

        // AUTO-PATCH: Ensure products/inventory dataset has image column
        if (isset($config['dataset']) && ($config['dataset'] === 'products' || $config['dataset'] === 'inventory_levels')) {
            $columns = $config['columns'] ?? [];
            if (!in_array('image', $columns)) {
                error_log("ReportController::run - Auto-patching report {$id} to include image");
                // Insert after id if possible, otherwise prepend
                $found = array_search('id', $columns);
                if ($found !== false) {
                     array_splice($columns, $found + 1, 0, 'image');
                } else {
                     array_unshift($columns, 'image');
                }
                $config['columns'] = $columns;
                $reportModel->update($id, ['query_config' => json_encode($config)]);
                // Reload report to get fresh config for execution
                $report = $reportModel->find($id); 
            }
        }

        // AUTO-PATCH: Fix Inventory by Product report to use correct dataset and columns
        if ($report['category'] === 'inventory_product' && ($config['dataset'] !== 'inventory_by_product' || !in_array('total_variants', $config['columns']))) {
             error_log("ReportController::run - Auto-patching Inventory by Product report {$id}");
             $config['dataset'] = 'inventory_by_product';
             $config['columns'] = ['product_title', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Products by Type / Inventory by Type report
        if (($report['category'] === 'products_type' || $report['category'] === 'inventory_type') && ($config['dataset'] !== 'products_by_type' || !in_array('total_variants', $config['columns']))) {
             error_log("ReportController::run - Auto-patching Inventory/Products by Type report {$id}");
             $config['dataset'] = 'products_by_type';
             $config['columns'] = ['product_type', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Inventory by SKU report
        if (($report['category'] === 'inventory_sku' || $report['name'] === 'Inventory by SKU') && ($config['dataset'] !== 'inventory_by_sku' || !in_array('sku', $config['columns']))) {
             error_log("ReportController::run - Auto-patching Inventory by SKU report {$id}");
             $config['dataset'] = 'inventory_by_sku';
             $config['columns'] = ['product_title', 'sku', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Inventory by Variant report
        if (($report['category'] === 'inventory_variant' || $report['name'] === 'Inventory by variant') && ($config['dataset'] !== 'inventory_by_sku' || !in_array('variant_title', $config['columns']))) {
             error_log("ReportController::run - Auto-patching Inventory by Variant report {$id}");
             $config['dataset'] = 'inventory_by_sku'; // Re-use inventory_by_sku dataset as it provides per-variant data
             $config['columns'] = ['product_title', 'variant_title', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Inventory by Vendor report
        if (($report['category'] === 'inventory_vendor' || $report['name'] === 'Inventory by vendor') && ($config['dataset'] !== 'inventory_by_vendor' || !in_array('vendor', $config['columns']))) {
             error_log("ReportController::run - Auto-patching Inventory by Vendor report {$id}");
             $config['dataset'] = 'inventory_by_vendor'; 
             $config['columns'] = ['vendor', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Monthly Sales by Channel report
        if (($report['category'] === 'monthly_sales_channel' || $report['name'] === 'Monthly sales by channel') && ($config['dataset'] !== 'monthly_sales_channel')) {
             error_log("ReportController::run - Auto-patching Monthly sales by channel report {$id}");
             $config['dataset'] = 'monthly_sales_channel';
             $config['columns'] = [
                 'month_date', 
                 'channel',
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
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Monthly sales by shipping country, state report
        if (($report['category'] === 'sales_shipping' || $report['name'] === 'Monthly sales by shipping country, state') && ($config['dataset'] !== 'monthly_sales_shipping')) {
             error_log("ReportController::run - Auto-patching Monthly sales by shipping country, state report {$id}");
             $config['dataset'] = 'monthly_sales_shipping';
             $config['columns'] = [
                 'month_date',
                 'order_shipping_country',
                 'order_shipping_state',
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
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Monthly Sales by POS location
        if (($report['category'] === 'monthly_sales_pos_location' || $report['name'] === 'Monthly sales by POS location') && ($config['dataset'] !== 'monthly_sales_pos_location')) {
             error_log("ReportController::run - Auto-patching Monthly sales by POS location report {$id}");
             $config['dataset'] = 'monthly_sales_pos_location';
             $config['columns'] = [
                 'month_date', 
                 'pos_location_name',
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
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }



        // AUTO-PATCH: Fix Monthly Sales by POS user
        if (($report['category'] === 'monthly_sales_pos_user' || $report['name'] === 'Monthly sales by POS user') && ($config['dataset'] !== 'monthly_sales_pos_user')) {
             error_log("ReportController::run - Auto-patching Monthly sales by POS user report {$id}");
             $config['dataset'] = 'monthly_sales_pos_user';
             // ... columns ...
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }



        // DEBUG: Log Report Details
        error_log("ReportController::run - Report ID: {$id}, Category: {$report['category']}, Name: {$report['name']}, Current Dataset: " . ($config['dataset'] ?? 'N/A'));

        // AUTO-PATCH: Fix Monthly Sales by Product report
        $isProductReport = ($report['category'] === 'monthly_sales_product' || stripos($report['name'], 'Monthly sales by product') !== false);
        
        // Exclude "Product type" and "Product variant" reports from this check to let their specific patches handle them
        if (stripos($report['name'], 'by product type') !== false || stripos($report['name'], 'by product variant') !== false) {
             $isProductReport = false;
        }
        
        if ($isProductReport && ($config['dataset'] !== 'monthly_sales_product')) {
             error_log("ReportController::run - Auto-patching Monthly sales by product report {$id} - UPDATING DB");
             $config['dataset'] = 'monthly_sales_product';
             $config['columns'] = [
                 'month_date', 
                 'product_title',
                 'total_quantity',
                 'total_orders', 
                 'total_gross_sales', 
                 'total_discounts', 
                 'total_refunds', 
                 'total_net_sales', 
                 'total_taxes', 
                 'total_sales', 
                 'total_cost_of_goods_sold', 
                 'total_gross_margin'
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Monthly Sales by Product TYPE report
        $isProductTypeReport = ($report['category'] === 'monthly_sales_product_type' || stripos($report['name'], 'Monthly sales by product type') !== false);
        
        if ($isProductTypeReport && ($config['dataset'] !== 'monthly_sales_product_type')) {
             error_log("ReportController::run - Auto-patching Monthly sales by product TYPE report {$id} - UPDATING DB");
             $config['dataset'] = 'monthly_sales_product_type';
             $config['columns'] = [
                 'month_date', 
                 'product_type',
                 'total_gross_sales', 
                 'total_discounts', 
                 'total_refunds', 
                 'total_net_sales', 
                 'total_taxes', 
                 'total_sales', 
                 'total_cost_of_goods_sold', 
                 'total_gross_margin'
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Monthly sales by product variant report
        $isProductVariantReport = ($report['category'] === 'monthly_sales_product_variant' || $report['category'] === 'is_variant_report' || stripos($report['name'], 'Monthly sales by product variant') !== false);
        
        if ($isProductVariantReport && ($config['dataset'] !== 'monthly_sales_product_variant')) {
             error_log("ReportController::run - Auto-patching Monthly sales by product variant report {$id} - UPDATING DB");
             $config['dataset'] = 'monthly_sales_product_variant';
             $config['columns'] = [
                 'month_date', 
                 'product_title',
                 'variant_title',
                 'total_quantity',
                 'total_orders', 
                 'total_gross_sales', 
                 'total_discounts', 
                 'total_refunds', 
                 'total_net_sales', 
                 'total_taxes', 
                 'total_sales', 
                 'total_cost_of_goods_sold', 
                 'total_gross_margin'
             ];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }

        // AUTO-PATCH: Fix Pending Fulfillments report
        if (($report['category'] === 'pending_fulfillments_var' || $report['name'] === 'Pending fulfillments') && ($config['dataset'] !== 'pending_fulfillment_by_variant')) {
             error_log("ReportController::run - Auto-patching Pending Fulfillments report {$id}");
             $config['dataset'] = 'pending_fulfillment_by_variant';
             $config['columns'] = ['product_title', 'variant_title', 'inventory_policy', 'inventory_quantity', 'quantity_pending_fulfillment'];
             $reportModel->update($id, ['query_config' => json_encode($config)]);
             $report = $reportModel->find($id);
        }


        // AUTO-PATCH: Rename 'Pending Fulfillment' to 'Items pending fulfillment'
        if ($report['category'] === 'pending_fulfillment' && $report['name'] === 'Pending Fulfillment') {
             error_log("ReportController::run - Auto-patching report name for items pending fulfillment");
             $reportModel->update($id, ['name' => 'Items pending fulfillment']);
             $report['name'] = 'Items pending fulfillment';
        }

        // AUTO-PATCH: Fix Line Item Details report to remove image column and ensure correct columns
        if (($report['category'] === 'line_items' || $report['name'] === 'Line Item Details') && $config['dataset'] === 'line_items') {
             $expectedColumns = ['order_date', 'order_name', 'customer_name', 'product_name', 'variant_name', 'quantity', 'total_gross_sales', 'total_discounts', 'total_refunds', 'total_net_sales'];
             $currentColumns = $config['columns'] ?? [];
             
             // Check if columns need updating (has 'image' or doesn't match expected)
             if (in_array('image', $currentColumns) || $currentColumns !== $expectedColumns) {
                  error_log("ReportController::run - Auto-patching Line Item Details report {$id} to remove image column");
                  $config['columns'] = $expectedColumns;
                  $reportModel->update($id, ['query_config' => json_encode($config)]);
                  $report = $reportModel->find($id);
             }
        }

        try {
            error_log("ReportController::run - Creating ShopifyService");
            $shopifyService = new \App\Services\ShopifyService(
                $shop['shop_domain'],
                $shop['access_token']
            );

            error_log("ReportController::run - Creating ReportBuilderService");
            $reportBuilder = new ReportBuilderService($shopifyService, $shop['id']);
            
            // FINAL FORCE OVERRIDE: If report name is "Inventory by SKU", ensure dataset is correct.
            // This overrides any previous auto-patches or legacy configs.
            $config = json_decode($report['query_config'], true) ?? [];
            if ($report['name'] === 'Inventory by SKU') {
                 error_log("ReportController::run - FINAL FORCE: Setting dataset to inventory_by_sku");
                 $config['dataset'] = 'inventory_by_sku';
                 $config['columns'] = ['product_title', 'sku', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
                 // We don't necessarily need to save to DB every time, but we MUST use this config for execution.
                 // Let's save it to be safe.
                 $reportModel->update($id, ['query_config' => json_encode($config)]);
                 $report['query_config'] = json_encode($config); // Update local var for query builder (though executeReport reads from DB or passed config?)
                 // executeReport reads from DB. So update matches.
            }
            
            // FINAL FORCE OVERRIDE: If report name is "Variant costs", use inventory_by_sku dataset (same as Inventory by variant)
            if ($report['name'] === 'Variant costs') {
                 error_log("ReportController::run - FINAL FORCE: Setting Variant costs to use inventory_by_sku dataset");
                 $config['dataset'] = 'inventory_by_sku'; // Same dataset as Inventory by variant
                 $config['columns'] = ['product_title', 'variant_title', 'price', 'cost', 'unit_margin', 'unit_margin_percent'];
                 $reportModel->update($id, ['query_config' => json_encode($config)]);
                 $report['query_config'] = json_encode($config);
            }

            // FINAL FORCE OVERRIDE: If report name is "Variants without cost", use inventory_by_sku dataset and filter for missing costs
            if ($report['name'] === 'Variants without cost') {
                 error_log("ReportController::run - FINAL FORCE: Setting Variants without cost to use inventory_by_sku dataset");
                 $config['dataset'] = 'inventory_by_sku';
                 $config['columns'] = ['image', 'product_title', 'variant_title', 'price'];
                 $config['filters'] = [['field' => 'cost', 'operator' => '=', 'value' => '-']];
                 $reportModel->update($id, ['query_config' => json_encode($config)]);
                 $report['query_config'] = json_encode($config);
            }
            
            // Read runtime config from request body
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $runtimeConfig = [];
            
            // Check for filters in request body
            if (isset($input['filters'])) {
                $runtimeConfig['filters'] = $input['filters'];
            }
            
            // CRITICAL FIX: Convert start_date/end_date from query params to filters
            // EXCEPTION: For pending fulfillment reports, ignore date filters
            // These reports should show ALL currently pending items regardless of order creation date
            $dataset = $config['dataset'] ?? '';
            $isPendingFulfillmentReport = ($dataset === 'pending_fulfillment_by_variant') && 
                                           ($report['category'] === 'pending_fulfillment' || $report['name'] === 'Items pending fulfillment');
            
            // Accept date range from GET or POST so filters work when Run report is used with a date range
            $startDate = $_GET['start_date'] ?? $input['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? $input['end_date'] ?? null;
            if ($startDate !== null && $endDate !== null && !$isPendingFulfillmentReport && empty($runtimeConfig['filters'])) {
                error_log("ReportController::run - Converting date params: start={$startDate}, end={$endDate}");
                $runtimeConfig['filters'] = [
                    [
                        'field' => 'created_at',
                        'operator' => '>=',
                        'value' => $startDate
                    ],
                    [
                        'field' => 'created_at',
                        'operator' => '<=',
                        'value' => $endDate
                    ]
                ];
            } elseif ($isPendingFulfillmentReport) {
                error_log("ReportController::run - Skipping date filters for pending fulfillment report");
            }
            
            if (isset($_GET['filters']) && is_array($_GET['filters']) && empty($runtimeConfig['filters'])) {
                 $runtimeConfig['filters'] = $_GET['filters'];
            }

            error_log("ReportController::run - Runtime Config: " . json_encode($runtimeConfig));
            error_log("ReportController::run - Executing report");
            $operationId = $reportBuilder->executeReport($id, $runtimeConfig);
            error_log("ReportController::run - Report executed, Operation ID: {$operationId}");

            // Poll for completion (max 20 seconds) to avoid PHP timeouts
            $status = 'PENDING';
            // DIRECT/REST mode completes immediately (no bulk operation/polling)
            if (is_string($operationId) && (strpos($operationId, 'DIRECT:') === 0 || strpos($operationId, 'REST:') === 0)) {
                $status = 'COMPLETED';
                $this->json([
                    'success' => true,
                    'operation_id' => $operationId,
                    'status' => $status,
                    'message' => 'Report generated'
                ]);
            }
            for ($i = 0; $i < 20; $i++) {
                sleep(1); // Wait 1s
                $isComplete = $reportBuilder->processBulkOperationResult($operationId, $id);
                if ($isComplete) {
                    error_log("ReportController::run - Operation completed and processed within timeout.");
                    $status = 'COMPLETED';
                    break;
                }
            }


            $this->json([
                'success' => true,
                'operation_id' => $operationId,
                'status' => $status,
                'message' => 'Report generation started'
            ]);
        } catch (\Throwable $e) {
            error_log("ReportController::run - EXCEPTION: " . $e->getMessage());
            error_log("ReportController::run - TRACE: " . $e->getTraceAsString());
            
            // Clean output buffer if started
            if (ob_get_level()) ob_end_clean();
            
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

        $resultModel = new \App\Models\ReportResult();
        $result = $resultModel->findByReport($id);

        $config = json_decode($report['query_config'], true) ?? [];
        $columns = $config['columns'] ?? [];
        
        // FORCE OVERRIDE: Ensure Variant costs has correct columns
        if ($report['name'] === 'Variant costs') {
            $config['dataset'] = 'variant_costs';
            $config['columns'] = ['product_title', 'variant_title', 'price', 'cost', 'unit_margin', 'unit_margin_percent'];
            $columns = $config['columns'];
            // Update database to persist the fix
            $reportModel->update($id, ['query_config' => json_encode($config)]);
        }

        // FALLBACK FIX: For Inventory by SKU, force columns if they look wrong
        if (($report['category'] === 'inventory_sku' || $report['name'] === 'Inventory by SKU') && !in_array('sku', $columns)) {
             $columns = ['product_title', 'sku', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost'];
        }
        
        // FALLBACK FIX: For Variant costs, force columns if they look wrong
        if (($report['category'] === 'variant_costs' || $report['name'] === 'Variant costs') && !in_array('variant_title', $columns)) {
             $columns = ['product_title', 'variant_title', 'price', 'cost', 'unit_margin', 'unit_margin_percent'];
        }

        if (!$result) {
            $this->json(['data' => [], 'total' => 0, 'columns' => $columns]);
        }

        $data = json_decode($result['result_data'], true) ?? [];
        
        // FINAL SAFETY FILTER: For Pending Fulfillments, strictly remove 0 values at read time
        if ($report['category'] === 'pending_fulfillments_var' || $report['name'] === 'Pending fulfillments') {
             $filtered = [];
             foreach ($data as $row) {
                 $val = (int)($row['quantity_pending_fulfillment'] ?? 0);
                 if ($val > 0) {
                     $filtered[] = $row;
                 }
             }
             $data = $filtered;
        }

        // Enforce Single Row for Summary Reports
        if (($config['dataset'] ?? '') === 'total_inventory_summary' && count($data) > 1) {
            $data = array_slice($data, 0, 1);
        }

        // Safety filter for monthly_cohorts
        if (($config['dataset'] ?? '') === 'monthly_cohorts') {
            $data = array_values(array_filter($data, function($row) {
                return !empty($row['month_first_order_date']) && $row['month_first_order_date'] !== 'Unknown' && ($row['total_customers'] ?? 0) > 0;
            }));
        }

        $this->json([
            'data' => $data,
            'total' => count($data),
            'columns' => $columns
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

        $names = $this->getReportNamesMapping();

        foreach ($orderReports as $type) {
            $reports[$type] = [
                'name' => $names[$type] ?? ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . ($names[$type] ?? str_replace('_', ' ', $type)),
                'config' => [
                    'dataset' => 'orders',
                    'columns' => ['id', 'name', 'created_at', 'total_price', 'financial_status', 'fulfillment_status']
                ]
            ];
        }

        // Specific monthly cohorts report
        $reports['monthly_cohorts'] = [
            'name' => 'Monthly cohorts',
            'description' => 'Customer retention and value by first order month',
            'config' => [
                'dataset' => 'monthly_cohorts',
                'columns' => ['month_first_order_date', 'total_customers', 'total_orders', 'average_orders_per_customer', 'total_sales', 'average_spend_per_customer']
            ]
        ];

        // Specific Monthly Sales report
        $reports['monthly_sales'] = [
            'name' => 'Monthly sales',
            'description' => 'Monthly sales breakdown with detailed metrics',
            'config' => [
                'dataset' => 'monthly_sales',
                'columns' => [
                    'month_date', 
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

        // Monthly sales by channel report
        $reports['monthly_sales_channel'] = [
            'name' => 'Monthly sales by channel',
            'description' => 'Monthly sales breakdown by channel',
            'config' => [
                'dataset' => 'monthly_sales_channel',
                'columns' => [
                    'month_date', 
                    'channel',
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

        // Monthly sales by shipping country, state report
        $reports['sales_shipping'] = [
            'name' => 'Monthly sales by shipping country, state',
            'description' => 'Monthly sales breakdown by order shipping country and state',
            'config' => [
                'dataset' => 'monthly_sales_shipping',
                'columns' => [
                    'month_date',
                    'order_shipping_country',
                    'order_shipping_state',
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

        // Monthly sales by POS location report
        $reports['monthly_sales_pos_location'] = [
            'name' => 'Monthly sales by POS location',
            'description' => 'Monthly sales breakdown by POS location',
            'config' => [
                'dataset' => 'monthly_sales_pos_location',
                'columns' => [
                    'month_date', 
                    'pos_location_name',
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

        // Monthly sales by POS user report
        $reports['monthly_sales_pos_user'] = [
            'name' => 'Monthly sales by POS user',
            'description' => 'Monthly sales breakdown by POS user',
            'config' => [
                'dataset' => 'monthly_sales_pos_user',
                'columns' => [
                    'month_date', 
                    'user_name',
                    'total_orders', 
                    'total_gross_sales', 
                    'total_discounts', 
                    'total_refunds', 
                    'total_net_sales', 
                    'total_taxes', 
                    'total_shipping', 
                    'total_sales'
                ]
            ]
        ];

        // Monthly sales by product report
        $reports['monthly_sales_product'] = [
            'name' => 'Monthly sales by product',
            'description' => 'Monthly sales breakdown by product',
            'config' => [
                'dataset' => 'monthly_sales_product',
                'columns' => [
                    'month_date', 
                    'product_title',
                    'total_quantity',
                    'total_orders', 
                    'total_gross_sales', 
                    'total_discounts', 
                    'total_refunds', 
                    'total_net_sales', 
                    'total_taxes', 
                    'total_sales', 
                    'total_cost_of_goods_sold', 
                    'total_gross_margin'
                ]
            ]
        ];

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
                'columns' => ['order_date', 'order_name', 'customer_name', 'product_name', 'variant_name', 'quantity', 'total_gross_sales', 'total_discounts', 'total_refunds', 'total_net_sales']
            ]
        ];

        $reports['pending_fulfillment'] = [
            'name' => 'Items pending fulfillment',
            'description' => 'List of line items waiting for fulfillment',
            'config' => [
                'dataset' => 'pending_fulfillment_by_variant',
                'columns' => ['order_date', 'order_name', 'vendor', 'product_title', 'variant_title', 'inventory_quantity', 'quantity_pending_fulfillment'],
                'filters' => []
            ]
        ];
        // 2. Products / Inventory Reports (Dataset: products)
        $productReports = [
            'all_products', 'products_collection', 'inventory', 
            'all_products', 'products_collection', 'inventory', 
             'inventory_variant', 'inventory_vendor', 
            'total_inventory', 'variants_no_cost', 'inv_location', 
            'inv_loc_prod', 'inv_loc_type', 'inv_loc_var', 'inv_loc_vendor', 'qty_loc_var', 'pending_drafts_var'
        ];

        $names = $this->getReportNamesMapping();
        foreach ($productReports as $type) {
            $reports[$type] = [
                'name' => $names[$type] ?? ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . ($names[$type] ?? str_replace('_', ' ', $type)),
                'config' => [
                    'dataset' => 'products',
                    'columns' => ['id', 'image', 'title', 'price', 'product_type', 'vendor', 'created_at', 'total_inventory', 'status']
                ]
            ];
        }

        $reports['pending_fulfillments_var'] = [
            'name' => 'Pending fulfillments',
            'description' => 'Pending fulfillments by variant',
            'config' => [
                'dataset' => 'pending_fulfillment_by_variant',
                'columns' => ['product_title', 'variant_title', 'inventory_policy', 'inventory_quantity', 'quantity_pending_fulfillment']
            ]
        ];

        $reports['products_vendor'] = [
            'name' => 'Total products by vendor',
            'description' => 'Report for Total products by vendor',
            'config' => [
                'dataset' => 'products_vendor',
                'columns' => ['vendor', 'total_products']
            ]
        ];

        $reports['products_type'] = [
            'name' => 'Total products by type',
            'description' => 'Report for Total products by type',
            'config' => [
                'dataset' => 'products_by_type',
                'columns' => ['product_type', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost']
            ]
        ];

        $reports['inventory_type'] = [
            'name' => 'Inventory by product type',
            'description' => 'Inventory details aggregated by product type',
            'config' => [
                'dataset' => 'products_by_type',
                'columns' => ['product_type', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost']
            ]
        ];

        $reports['inventory_sku'] = [
            'name' => 'Inventory by SKU',
            'description' => 'Inventory details per SKU',
            'config' => [
                'dataset' => 'inventory_by_sku',
                'columns' => ['product_title', 'sku', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost']
            ]
        ];

        $reports['inventory_product'] = [
            'name' => 'Inventory by product',
            'description' => 'Inventory details aggregated by product',
            'config' => [
                'dataset' => 'inventory_by_product',
                'columns' => ['product_title', 'image', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost']
            ]
        ];

        $reports['inventory_levels'] = [
            'name' => 'Inventory Levels',
            'description' => 'Detailed inventory levels by location',
            'config' => [
                'dataset' => 'inventory_levels',
                'columns' => ['id', 'image', 'location_name', 'sku', 'available', 'updated_at']
            ]
        ];

        // Specific override for Total Inventory Summary
        $reports['total_inventory'] = [
            'name' => 'Total inventory summary',
            'description' => 'Summary of total inventory value and quantity',
            'config' => [
                'dataset' => 'total_inventory_summary',
                'columns' => ['total_products', 'total_variants', 'total_quantity', 'total_inventory_value', 'total_inventory_cost']
            ]
        ];

        // Variant Costs Report - Show margin calculations, display "-" for products without cost
        $reports['variant_costs'] = [
            'name' => 'Variant costs',
            'description' => 'Product variant pricing with cost and margin analysis',
            'config' => [
                'dataset' => 'variant_costs',
                'columns' => ['product_title', 'variant_title', 'price', 'cost', 'unit_margin', 'unit_margin_percent']
            ]
        ];

        // 3. Customers Reports (Dataset: customers)
        $customerReports = [
            'customers', 'users_list', 'markets'
        ];

        $names = $this->getReportNamesMapping();
        foreach ($customerReports as $type) {
            $reports[$type] = [
                'name' => $names[$type] ?? ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . ($names[$type] ?? str_replace('_', ' ', $type)),
                'config' => [
                    'dataset' => 'customers',
                    'columns' => ['id', 'full_name', 'email', 'created_at', 'updated_at', 'orders_count', 'total_spent', 'accepts_marketing']
                ]
            ];
        }

        // Specific Report: Total customers per country
        $reports['customers_country'] = [
            'name' => 'Total customers per country',
            'description' => 'Aggregated count of customers by country',
            'config' => [
                'dataset' => 'customers_by_country',
                'columns' => ['country', 'total_customers']
            ]
        ];

        // 4. Transactions / Financial Reports (Dataset: transactions)
        $transactionReports = [
            'all_transactions', 'failed_transactions', 'gift_card_transactions', 'gift_card_trans_time', 
            'trans_monthly_gateway', 'trans_monthly_user', 'paypal_recon', 'pending_trans', 
            'total_trans_value_time', 'total_trans_value_gateway', 'volume_gateway', 
            'payout_details', 'pending_payouts', 'monthly_fees', 'fee_details',
            'active_gift_cards', 'gift_cards_app', 'gift_cards_source', 'gift_cards_user', 'gift_cards_val_user',
            'payout_summary'
        ];

        $names = $this->getReportNamesMapping();
        foreach ($transactionReports as $type) {
            $reports[$type] = [
                'name' => $names[$type] ?? ucwords(str_replace('_', ' ', $type)),
                'description' => 'Report for ' . ($names[$type] ?? str_replace('_', ' ', $type)),
                'config' => [
                    'dataset' => 'transactions',
                    'columns' => ['id', 'created_at', 'amount', 'currency_code', 'gateway', 'status', 'kind']
                ]
            ];
        }

        // Specific Payout Summary Report
        $reports['payout_summary'] = [
            'name' => 'Payout summary',
            'description' => 'Summary of payouts including gross, fee, and net amounts',
            'config' => [
                'dataset' => 'payouts',
                'columns' => ['date', 'id', 'currency', 'status', 'total_gross', 'total_fee', 'total_net']
            ]
        ];

        // Specific Monthly Disputes Report
        $reports['monthly_disputes'] = [
            'name' => 'Monthly disputes',
            'description' => 'Disputes grouped by month, status, type and reason',
            'config' => [
                'dataset' => 'monthly_disputes',
                'columns' => ['month_initiated_at', 'status', 'type', 'reason', 'total_disputes', 'total_amount']
            ]
        ];

        // Specific Pending Disputes Report (Detail View)
        $reports['pending_disputes'] = [
            'name' => 'Pending disputes',
            'description' => 'Detailed list of disputes with order and evidence status',
            'config' => [
                'dataset' => 'pending_disputes', // uses same query builder as monthly_disputes but different processing
                'columns' => ['initiated_at', 'id', 'status', 'type', 'reason', 'evidence_due_by', 'evidence_sent_on', 'order_name', 'order_date', 'email', 'customer_name', 'total_amount']
            ]
        ];

        // 5. Draft Orders
        $reports['pending_drafts'] = [
            'name' => 'Pending Draft Orders',
            'description' => 'List of draft orders',
            'config' => [
                'dataset' => 'draft_orders',
                'columns' => ['id', 'name', 'created_at', 'total_price', 'status', 'email']
            ]
        ];

        // 6. Markets
        $reports['markets'] = [
            'name' => 'Markets',
            'description' => 'List of markets and their regions/countries',
            'config' => [
                'dataset' => 'markets',
                'columns' => ['market_name', 'is_primary', 'is_enabled', 'region', 'country_code']
            ]
        ];

        return $reports;
    }

    private function getReportNamesMapping()
    {
        return [
            'customers' => 'Customers',
            'markets' => 'Markets',
            'customers_country' => 'Total customers per country',
            'all_products' => 'All products',
            'products_type' => 'Total products by type',
            'products_vendor' => 'Total products by vendor',
            'inventory' => 'Inventory',
            'inventory_product' => 'Inventory by product',
            'inventory_type' => 'Inventory by product type',
            'inventory_sku' => 'Inventory by SKU',
            'inventory_variant' => 'Inventory by variant',
            'inventory_vendor' => 'Inventory by vendor',
            'pending_fulfillments_var' => 'Pending fulfillments',
            'total_inventory' => 'Total inventory summary',
            'variant_costs' => 'Variant costs',
            'variants_no_cost' => 'Variants without cost',
            'payout_summary' => 'Payout summary',
            'monthly_disputes' => 'Monthly disputes',
            'pending_disputes' => 'Pending disputes',
            'markets' => 'Markets',
            'all_time_sales' => 'All-time sales',
            'pending_fulfillment' => 'Items pending fulfillment',
            'line_items' => 'Line item details',
            'monthly_cohorts' => 'Monthly cohorts',
            'monthly_sales' => 'Monthly sales',
            'monthly_sales_channel' => 'Monthly sales by channel',
            'sales_pos_location' => 'Monthly sales by POS location',
            'sales_pos_user' => 'Monthly sales by POS user',
            'sales_product' => 'Monthly sales by product',
            'sales_type' => 'Monthly sales by product type',
            'sales_variant' => 'Monthly sales by product variant',
            'sales_shipping' => 'Monthly sales by shipping country, state',
            'sales_sku' => 'Monthly sales by SKU',
            'sales_vendor' => 'Monthly sales by vendor',
            'refunds' => 'Refunds',
            'sales_channel' => 'Sales by channel',
            'sales_customer' => 'Sales by customer',
            'sales_discount' => 'Sales by discount code',
            'sales_by_product' => 'Sales by product',
            'sales_by_type' => 'Sales by product type',
            'sales_by_variant' => 'Sales by product variant',
            'sales_ref_site' => 'Sales by referring site',
            'sales_by_sku' => 'Sales by SKU',
            'sales_by_vendor' => 'Sales by vendor',
            'sales_over_time' => 'Sales over time',
            'sales_gift_cards' => 'Sales over time - Gift cards',
            'sales_time_channel' => 'Sales over time by channel',
            'sales_time_pos' => 'Sales over time by POS location',
            'sales_time_user' => 'Sales over time by POS user',
            'sales_time_product' => 'Sales over time by product',
            'sales_time_type' => 'Sales over time by product type',
            'sales_time_variant' => 'Sales over time by product variant',
            'sales_time_ref' => 'Sales over time by referring site',
            'sales_time_sku' => 'Sales over time by SKU',
            'sales_time_utm_medium' => 'Sales over time by UTM medium',
            'sales_time_utm_source' => 'Sales over time by UTM source',
            'sales_time_vendor' => 'Sales over time by vendor',
            'tax_monthly' => 'Tax collected per month',
            'aov_time' => 'Average order value over time',
            'browser_share' => 'Browser share over time',
            'device_share' => 'Device type share over time',
            'new_vs_returning' => 'First-time vs returning customer orders',
            'risk_orders' => 'High-risk orders',
            'order_details' => 'Order details',
            'orders_channel' => 'Orders by channel',
            'orders_country' => 'Orders by country',
            'orders_pos_loc' => 'Orders by POS location',
            'orders_pos_user' => 'Orders by POS user',
            'orders_ref_site' => 'Orders by referring site',
            'orders_utm_campaign' => 'Orders by UTM campaign',
            'orders_utm_medium' => 'Orders by UTM medium',
            'orders_utm_source' => 'Orders by UTM source',
            'orders_pending' => 'Orders pending fulfillment',
            'total_value_channel' => 'Total order value by channel',
            'total_value_country' => 'Total order value by country',
            'total_value_ref' => 'Total order value by referring site',
            'orders_day_hour' => 'Total orders by day and hour',
            'active_gift_cards' => 'Active gift cards',
            'gift_cards_app' => 'Monthly issued gift cards by app',
            'gift_cards_source' => 'Monthly issued gift cards by source',
            'gift_cards_user' => 'Monthly issued gift cards by user',
            'gift_cards_val_user' => 'Total value issued by user over time',
            'monthly_fees' => 'Monthly transaction fees',
            'fee_details' => 'Transaction fee details',
            'users_list' => 'Users',
            'all_transactions' => 'All transactions',
            'failed_transactions' => 'Failed transactions',
            'gift_card_transactions' => 'Gift card transactions',
            'gift_card_trans_time' => 'Gift card transactions over time',
            'trans_monthly_gateway' => 'Monthly transactions by payment gateway',
            'trans_monthly_user' => 'Monthly transactions per user',
            'paypal_recon' => 'PayPal reconciliation',
            'pending_trans' => 'Pending transactions',
            'total_trans_value_time' => 'Total transactions value over time',
            'total_trans_value_gateway' => 'Total transactions value per gateway over time',
            'volume_gateway' => 'Volume per payment gateway',
            'inv_location' => 'Inventory by location',
            'inv_loc_prod' => 'Inventory by location by product',
            'inv_loc_type' => 'Inventory by location by product type',
            'inv_loc_var' => 'Inventory by location by variant',
            'inv_loc_vendor' => 'Inventory by location by vendor',
            'qty_loc_var' => 'Quantity by location by variant',
            'pending_drafts' => 'Pending draft orders',
            'pending_drafts_var' => 'Pending draft orders by product variant',
            'payout_details' => 'Payout details',
            'pending_payouts' => 'Pending payout details',
            'sales_staff' => 'Monthly sales attribution by staff',
            'products_collection' => 'Total Products by Collection'
        ];
    }
}
