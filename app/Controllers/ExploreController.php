<?php

namespace App\Controllers;

use App\Core\Controller;

class ExploreController extends Controller
{
    public function index()
    {
        $shop = $this->requireAuth();
        
        $categories = [
            'orders' => [
                'name' => 'Orders',
                'reports' => [
                    ['id' => 'orders-over-time', 'name' => 'Orders Over Time', 'description' => 'View orders over time'],
                    ['id' => 'orders-by-country', 'name' => 'Orders by Country', 'description' => 'Orders grouped by country'],
                    ['id' => 'orders-by-channel', 'name' => 'Orders by Channel', 'description' => 'Orders by sales channel'],
                    ['id' => 'average-order-value', 'name' => 'Average Order Value', 'description' => 'Calculate average order value'],
                ]
            ],
            'customers' => [
                'name' => 'Customers',
                'reports' => [
                    ['id' => 'total-customers', 'name' => 'Total Customers', 'description' => 'Total customer count'],
                    ['id' => 'customers-by-country', 'name' => 'Customers by Country', 'description' => 'Customers grouped by country'],
                    ['id' => 'new-vs-returning', 'name' => 'New vs Returning', 'description' => 'Compare new and returning customers'],
                ]
            ],
            'products' => [
                'name' => 'Products',
                'reports' => [
                    ['id' => 'all-products', 'name' => 'All Products', 'description' => 'Complete product list'],
                    ['id' => 'products-by-vendor', 'name' => 'Products by Vendor', 'description' => 'Products grouped by vendor'],
                    ['id' => 'products-by-type', 'name' => 'Products by Type', 'description' => 'Products grouped by type'],
                ]
            ],
        ];

        $this->view->render('explore/index', [
            'shop' => $shop,
            'categories' => $categories,
            'config' => $this->config
        ]);
    }

    public function category($category)
    {
        $shop = $this->requireAuth();
        
        $this->view->render('explore/category', [
            'shop' => $shop,
            'category' => $category,
            'config' => $this->config
        ]);
    }
}

