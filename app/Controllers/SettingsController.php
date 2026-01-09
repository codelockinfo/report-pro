<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Shop;

class SettingsController extends Controller
{
    public function index()
    {
        $shop = $this->requireAuth();
        
        $settingsModel = new \App\Models\Settings();
        $settings = $settingsModel->findAll(['shop_id' => $shop['id']]);
        $settings = $settings[0] ?? null;

        $this->view->render('settings/index', [
            'shop' => $shop,
            'settings' => $settings,
            'config' => $this->config
        ]);
    }

    public function update()
    {
        $shop = $this->requireAuth();
        
        $weekStart = $_POST['week_start'] ?? 'sunday';
        $locale = $_POST['locale'] ?? 'en-US';
        $timezone = $_POST['timezone'] ?? 'UTC';

        $settingsModel = new \App\Models\Settings();
        $existing = $settingsModel->findAll(['shop_id' => $shop['id']]);

        $data = [
            'shop_id' => $shop['id'],
            'week_start' => $weekStart,
            'locale' => $locale,
            'timezone' => $timezone
        ];

        if (empty($existing)) {
            $settingsModel->create($data);
        } else {
            $settingsModel->update($existing[0]['id'], $data);
        }

        $this->json(['success' => true]);
    }
}

