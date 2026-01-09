<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;

class AjaxController extends Controller
{
    public function searchReports()
    {
        $shop = $this->requireAuth();
        
        $search = $_POST['search'] ?? '';
        
        $reportModel = new Report();
        $reports = $reportModel->findByShop($shop['id'], ['search' => $search]);

        $this->json(['reports' => $reports]);
    }

    public function getColumns()
    {
        $dataset = $_POST['dataset'] ?? 'orders';
        
        $columns = $this->getDatasetColumns($dataset);
        
        $this->json(['columns' => $columns]);
    }

    public function previewReport($id)
    {
        $shop = $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->find($id);

        if (!$report || $report['shop_id'] != $shop['id']) {
            $this->json(['error' => 'Report not found'], 404);
        }

        $resultModel = new \App\Models\ReportResult();
        $result = $resultModel->findByReport($id);

        if (!$result) {
            $this->json(['data' => [], 'total' => 0]);
        }

        $data = json_decode($result['result_data'], true);
        
        $this->json([
            'data' => array_slice($data, 0, 10), // First 10 rows
            'total' => $result['total_records']
        ]);
    }

    private function getDatasetColumns($dataset)
    {
        $columns = [
            'orders' => [
                ['name' => 'id', 'label' => 'Order ID', 'type' => 'string'],
                ['name' => 'name', 'label' => 'Order Name', 'type' => 'string'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'string'],
                ['name' => 'created_at', 'label' => 'Created At', 'type' => 'date'],
                ['name' => 'total_price', 'label' => 'Total Price', 'type' => 'number'],
                ['name' => 'financial_status', 'label' => 'Financial Status', 'type' => 'string'],
                ['name' => 'fulfillment_status', 'label' => 'Fulfillment Status', 'type' => 'string'],
                ['name' => 'country', 'label' => 'Country', 'type' => 'string'],
            ],
            'products' => [
                ['name' => 'id', 'label' => 'Product ID', 'type' => 'string'],
                ['name' => 'title', 'label' => 'Title', 'type' => 'string'],
                ['name' => 'vendor', 'label' => 'Vendor', 'type' => 'string'],
                ['name' => 'product_type', 'label' => 'Type', 'type' => 'string'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'string'],
                ['name' => 'total_inventory', 'label' => 'Total Inventory', 'type' => 'number'],
            ],
            'customers' => [
                ['name' => 'id', 'label' => 'Customer ID', 'type' => 'string'],
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'string'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'string'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'string'],
                ['name' => 'orders_count', 'label' => 'Orders Count', 'type' => 'number'],
                ['name' => 'total_spent', 'label' => 'Total Spent', 'type' => 'number'],
                ['name' => 'country', 'label' => 'Country', 'type' => 'string'],
            ],
            'transactions' => [
                ['name' => 'id', 'label' => 'Transaction ID', 'type' => 'string'],
                ['name' => 'kind', 'label' => 'Kind', 'type' => 'string'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'string'],
                ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
                ['name' => 'currency_code', 'label' => 'Currency', 'type' => 'string'],
                ['name' => 'gateway', 'label' => 'Gateway', 'type' => 'string'],
                ['name' => 'created_at', 'label' => 'Created At', 'type' => 'date'],
            ],
        ];

        return $columns[$dataset] ?? [];
    }
}

