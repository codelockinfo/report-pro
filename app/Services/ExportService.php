<?php

namespace App\Services;

use App\Models\Export;
use App\Models\Report;
use App\Models\ReportResult;

class ExportService
{
    private $config;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/config.php';
        $this->ensureExportDirectory();
    }

    private function ensureExportDirectory()
    {
        $exportPath = $this->config['exports_path'];
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }
    }

    public function exportToCSV($reportId, $shopId)
    {
        $reportModel = new Report();
        $report = $reportModel->find($reportId);

        if (!$report || $report['shop_id'] != $shopId) {
            throw new \Exception("Report not found");
        }

        $resultModel = new ReportResult();
        $result = $resultModel->findByReport($reportId);

        if (!$result) {
            throw new \Exception("No data to export");
        }

        $data = json_decode($result['result_data'], true);
        
        $fileName = "report_{$reportId}_" . date('Y-m-d_His') . ".csv";
        $filePath = $this->config['exports_path'] . '/' . $fileName;

        $fp = fopen($filePath, 'w');

        if (!empty($data)) {
            // Define Column Headers map (can be dynamic based on report config, but hardcoding for Customers report for now as requested)
            // Ideally, we fetch this from report config columns. 
            $config = json_decode($report['query_config'], true);
            $columns = $config['columns'] ?? array_keys($data[0]);

            // Map safe keys to Labels
            $headerLabels = [];
            foreach ($columns as $col) {
                switch ($col) {
                    case 'id': $headerLabels[] = 'Id'; break;
                    case 'created_at': $headerLabels[] = 'DAY Created at'; break;
                    case 'email': $headerLabels[] = 'Email'; break;
                    case 'accepts_marketing': $headerLabels[] = 'Accepts email marketing'; break;
                    case 'full_name': $headerLabels[] = 'Full name'; break;
                    case 'country': $headerLabels[] = 'Country'; break;
                    case 'orders_count': $headerLabels[] = '# Orders'; break;
                    case 'total_spent': $headerLabels[] = 'Total spent'; break;
                    case 'average_order_value': $headerLabels[] = 'Average order value'; break;
                    default: $headerLabels[] = ucwords(str_replace('_', ' ', $col));
                }
            }
            fputcsv($fp, $headerLabels);

            // Write data
            foreach ($data as $row) {
                $formattedRow = [];
                foreach ($columns as $col) {
                    $val = '';
                    // Handle key mapping if row keys differ (e.g. full_name vs displayName)
                    // The raw data from ShopifyService has keys: id, displayName, email, acceptsMarketing, createdAt, ordersCount, totalSpent, defaultAddress
                    // We need to map $col (config) to $row key.
                    
                    switch ($col) {
                        case 'id':
                            // Generic regex to strip any GID prefix
                            $rawId = $row['id'] ?? '';
                            $val = preg_replace('/^gid:\/\/shopify\/\w+\//', '', $rawId);
                            break;
                        case 'created_at':
                            $date = $row['createdAt'] ?? $row['created_at'] ?? '';
                            $val = $date ? date('M d, Y H:i', strtotime($date)) : '';
                            break;
                        
                        // Customer specific
                        case 'full_name':
                            $val = $row['displayName'] ?? $row['name'] ?? '';
                            break;
                        case 'email':
                            $val = $row['email'] ?? '';
                            break;
                        case 'accepts_marketing':
                            $raw = $row['acceptsMarketing'] ?? false;
                            $val = $raw ? 'Yes' : 'No';
                            break;
                        case 'country':
                            $addr = $row['defaultAddress'] ?? $row['shippingAddress'] ?? [];
                            $val = $addr['country'] ?? '';
                            break;
                        case 'orders_count':
                            $val = $row['ordersCount'] ?? 0;
                            break;
                        case 'total_spent':
                            $spent = $row['totalSpent'] ?? ['amount' => 0, 'currencyCode' => ''];
                            $amount = is_array($spent) ? ($spent['amount'] ?? 0) : $spent;
                            $curr = is_array($spent) ? ($spent['currencyCode'] ?? '') : '';
                            $val = ($curr ? $curr . ' ' : '') . number_format((float)$amount, 2);
                            break;
                        case 'average_order_value':
                            $spent = $row['totalSpent'] ?? ['amount' => 0];
                            $amount = is_array($spent) ? ($spent['amount'] ?? 0) : $spent;
                            $count = $row['ordersCount'] ?? 0;
                            $aov = $count > 0 ? ((float)$amount / $count) : 0;
                            $val = number_format($aov, 2);
                            break;

                        // Order specific
                        case 'total_price':
                            $priceSet = $row['totalPriceSet'] ?? [];
                            $money = $priceSet['shopMoney'] ?? ['amount' => 0, 'currencyCode' => ''];
                            $val = ($money['currencyCode'] ?? '') . ' ' . number_format((float)($money['amount'] ?? 0), 2);
                            break;
                        case 'financial_status':
                            $val = $row['financialStatus'] ?? '';
                            break;
                        case 'fulfillment_status':
                            $val = $row['fulfillmentStatus'] ?? '';
                            break;
                        case 'name':
                            $val = $row['name'] ?? '';
                            break;

                        // Product specific
                        case 'title':
                            $val = $row['title'] ?? '';
                            break;
                        case 'product_type':
                            $val = $row['productType'] ?? '';
                            break;
                        case 'vendor':
                            $val = $row['vendor'] ?? '';
                            break;
                        case 'status':
                            $val = $row['status'] ?? '';
                            break;
                        case 'total_inventory':
                            $val = $row['totalInventory'] ?? 0;
                            break;

                        // Transaction specific
                        case 'amount':
                            $set = $row['amountSet'] ?? [];
                            $money = $set['shopMoney'] ?? ['amount' => 0, 'currencyCode' => ''];
                            $val = number_format((float)($money['amount'] ?? 0), 2);
                            if ($money['currencyCode'] ?? false) $val = $money['currencyCode'] . ' ' . $val;
                            break;
                        case 'currency_code':
                            $set = $row['amountSet'] ?? [];
                            $money = $set['shopMoney'] ?? ['currencyCode' => ''];
                            $val = $money['currencyCode'] ?? '';
                            break;
                        case 'gateway':
                            $val = $row['gateway'] ?? '';
                            break;
                        case 'kind':
                            $val = $row['kind'] ?? '';
                            break;
                            
                        // Inventory specific
                        case 'available':
                            $val = $row['available'] ?? 0;
                            break;
                        case 'sku':
                            $item = $row['inventoryItem'] ?? [];
                            $val = $item['sku'] ?? '';
                            break;
                        case 'location_name':
                            $loc = $row['location'] ?? [];
                            $val = $loc['name'] ?? '';
                            break;
                        case 'updated_at':
                            $date = $row['updatedAt'] ?? $row['updated_at'] ?? '';
                            $val = $date ? date('M d, Y H:i', strtotime($date)) : '';
                            break;
                        
                        // Line item specific
                        case 'quantity':
                            $val = $row['quantity'] ?? 0;
                            break;
                        case 'price':
                            $set = $row['priceSet'] ?? [];
                            $money = $set['shopMoney'] ?? ['amount' => 0, 'currencyCode' => ''];
                            $val = number_format((float)($money['amount'] ?? 0), 2);
                            if ($money['currencyCode'] ?? false) $val = $money['currencyCode'] . ' ' . $val;
                            break;
                        
                        default:
                            // Fallback: try match standard/camelCase keys
                            $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $col))));
                            $val = $row[$col] ?? $row[$camelKey] ?? '';
                            if (is_array($val)) $val = json_encode($val);
                    }
                    $formattedRow[] = $val;
                }
                fputcsv($fp, $formattedRow);
            }
        }

        fclose($fp);

        return $this->saveExport($shopId, $reportId, 'csv', $fileName, $filePath);
    }

    public function exportToExcel($reportId, $shopId)
    {
        // For Excel, we'll use CSV format with .xlsx extension
        // In production, you'd use PhpSpreadsheet library
        return $this->exportToCSV($reportId, $shopId);
    }

    public function exportToPDF($reportId, $shopId)
    {
        // For PDF, you'd use TCPDF or FPDF library
        // For now, return CSV as placeholder
        return $this->exportToCSV($reportId, $shopId);
    }

    private function saveExport($shopId, $reportId, $format, $fileName, $filePath)
    {
        $exportModel = new Export();
        $token = $exportModel->generateToken();
        
        $expiresAt = date('Y-m-d H:i:s', time() + $this->config['export_token_expiry']);
        $fileSize = filesize($filePath);

        $exportId = $exportModel->create([
            'shop_id' => $shopId,
            'report_id' => $reportId,
            'export_type' => 'report',
            'format' => $format,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'status' => 'completed',
            'download_token' => $token,
            'expires_at' => $expiresAt
        ]);

        return [
            'id' => $exportId,
            'token' => $token,
            'file_name' => $fileName
        ];
    }

    public function getExportFile($token)
    {
        $exportModel = new Export();
        $export = $exportModel->findByToken($token);

        if (!$export) {
            return null;
        }

        if (!file_exists($export['file_path'])) {
            return null;
        }

        return $export;
    }
}

