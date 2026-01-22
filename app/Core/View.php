<?php

namespace App\Core;

class View
{
    public function render($view, $data = [])
    {
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering for view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Include layout if it exists and the view didn't already handle it
        $layoutFile = VIEWS_PATH . '/layouts/app.php';
        if (file_exists($layoutFile) && stripos($content, '<!DOCTYPE html>') === false) {
            // Extract data again for layout
            extract($data);
            include $layoutFile;
        } else {
            // View already included the layout or no layout exists
            echo $content;
        }
    }

    public function renderPartial($view, $data = [])
    {
        extract($data);
        
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        include $viewFile;
    }
}

