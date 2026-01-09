<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ExportService;

class ExportController extends Controller
{
    public function generate()
    {
        $shop = $this->requireAuth();
        
        $reportId = $_POST['report_id'] ?? null;
        $format = $_POST['format'] ?? 'csv';

        if (!$reportId) {
            $this->json(['error' => 'Report ID is required'], 400);
        }

        try {
            $exportService = new ExportService();
            
            switch ($format) {
                case 'csv':
                    $result = $exportService->exportToCSV($reportId, $shop['id']);
                    break;
                case 'excel':
                    $result = $exportService->exportToExcel($reportId, $shop['id']);
                    break;
                case 'pdf':
                    $result = $exportService->exportToPDF($reportId, $shop['id']);
                    break;
                default:
                    $this->json(['error' => 'Invalid format'], 400);
            }

            $this->json([
                'success' => true,
                'token' => $result['token'],
                'file_name' => $result['file_name']
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function download($token)
    {
        $exportService = new ExportService();
        $export = $exportService->getExportFile($token);

        if (!$export) {
            http_response_code(404);
            die("Export not found or expired");
        }

        $filePath = $export['file_path'];
        $fileName = $export['file_name'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File not found");
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    public function history()
    {
        $shop = $this->requireAuth();
        
        $exportModel = new \App\Models\Export();
        $exports = $exportModel->findByShop($shop['id']);

        $this->view->render('exports/history', [
            'shop' => $shop,
            'exports' => $exports,
            'config' => $this->config
        ]);
    }
}

