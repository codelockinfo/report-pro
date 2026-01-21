<style>
    .dashboard-header {
        background-color: #303030;
        color: white;
        padding: 0 20px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .header-nav {
        display: flex;
        gap: 20px;
        height: 100%;
    }
    .header-link {
        color: #e0e0e0;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        padding: 0 10px;
        height: 100%;
        border-bottom: 3px solid transparent;
    }
    .header-link.active {
        color: white;
        border-bottom-color: white;
    }
    .header-link:hover {
        color: white;
        text-decoration: none;
    }
</style>

<?php
// Preserve query parameters for navigation
$queryParams = $_GET;
// Remove route-specific params if any (like 'url' from router)
unset($queryParams['url']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';

// Get base URL for subdirectory support
$appUrl = getenv('APP_URL');
if ($appUrl === false) $appUrl = '';
$baseUrl = rtrim($appUrl, '/');
?>

<div class="dashboard-header">
    <div class="header-nav">
        <a href="<?= $baseUrl ?>/reports<?= $suffix ?>" class="header-link <?= ($currentRoute === '/reports') ? 'active' : '' ?>">Reports</a>
        <a href="<?= $baseUrl ?>/chart-analysis<?= $suffix ?>" class="header-link <?= ($currentRoute === '/chart-analysis') ? 'active' : '' ?>">Chart analysis</a>
        <a href="<?= $baseUrl ?>/schedule<?= $suffix ?>" class="header-link <?= ($currentRoute === '/schedule') ? 'active' : '' ?>">Schedule</a>
        <a href="<?= $baseUrl ?>/settings<?= $suffix ?>" class="header-link <?= ($currentRoute === '/settings') ? 'active' : '' ?>">Settings</a>
    </div>
    <div class="header-right">
        <a href="#" class="header-link">Docs</a>
    </div>
</div>
