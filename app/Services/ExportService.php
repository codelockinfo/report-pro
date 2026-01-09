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
            // Write headers
            $headers = array_keys($data[0]);
            fputcsv($fp, $headers);

            // Write data
            foreach ($data as $row) {
                fputcsv($fp, $row);
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

