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

            error_log("Dashboard::index - Found " . count($recentExports) . " recent exports");

            // Define Dashboard Categories (matching screenshot)
            $dashboardCategories = [
                'left_column' => [
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
                    'product_variants' => [
                        'title' => 'Product variants',
                        'items' => [
                            ['name' => 'Inventory', 'url' => '/reports/predefined/inventory'],
                            ['name' => 'Inventory by product', 'url' => '/reports/predefined/inventory_product'],
                            ['name' => 'Inventory by product type', 'url' => '/reports/predefined/inventory_type'],
                            ['name' => 'Inventory by SKU', 'url' => '/reports/predefined/inventory_sku']
                        ]
                    ]
                ],
                'middle_column' => [
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
                            ['name' => 'Sales by discount code', 'url' => '/reports/predefined/sales_discount']
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
                            ['name' => 'Orders by POS location', 'url' => '/reports/predefined/orders_pos'],
                            ['name' => 'Orders by referring site', 'url' => '/reports/predefined/orders_referrer'],
                            ['name' => 'Orders by UTM campaign', 'url' => '/reports/predefined/orders_utm_campaign'],
                            ['name' => 'Orders pending fulfillment', 'url' => '/reports/predefined/orders_pending'],
                            ['name' => 'Total order value by channel', 'url' => '/reports/predefined/value_channel'],
                            ['name' => 'Total order value by country', 'url' => '/reports/predefined/value_country'],
                            ['name' => 'Total orders by day and hour', 'url' => '/reports/predefined/orders_day_hour']
                        ]
                    ]
                ],
                'right_column' => [
                    'transactions' => [
                        'title' => 'Transactions',
                        'items' => [
                            ['name' => 'All transactions', 'url' => '/reports/predefined/all_transactions'],
                            ['name' => 'Failed transactions', 'url' => '/reports/predefined/failed_transactions'],
                            ['name' => 'Gift card transactions', 'url' => '/reports/predefined/gift_card'],
                            ['name' => 'Monthly transactions by payment gateway', 'url' => '/reports/predefined/trans_gateway'],
                            ['name' => 'Monthly transactions per user', 'url' => '/reports/predefined/trans_user'],
                            ['name' => 'PayPal reconciliation', 'url' => '/reports/predefined/paypal'],
                            ['name' => 'Pending transactions', 'url' => '/reports/predefined/pending_trans'],
                            ['name' => 'Total transactions value over time', 'url' => '/reports/predefined/trans_value_time'],
                            ['name' => 'Volume per payment gateway', 'url' => '/reports/predefined/volume_gateway']
                        ]
                    ],
                    'inventory' => [
                        'title' => 'Inventory levels',
                        'items' => [
                            ['name' => 'Inventory by location', 'url' => '/reports/predefined/inv_location'],
                            ['name' => 'Inventory by location by product', 'url' => '/reports/predefined/inv_loc_prod'],
                            ['name' => 'Inventory by location by product type', 'url' => '/reports/predefined/inv_loc_type'],
                            ['name' => 'Inventory by location by variant', 'url' => '/reports/predefined/inv_loc_variant'],
                            ['name' => 'Inventory by location by vendor', 'url' => '/reports/predefined/inv_loc_vendor']
                        ]
                    ]
                ]
            ];

            error_log("Dashboard::index - Rendering view");
            $this->view->render('dashboard/index', [
                'shop' => $shop,
                'reports' => $reports,
                'schedules' => $schedules,
                'recentExports' => $recentExports,
                'dashboardCategories' => $dashboardCategories,
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

