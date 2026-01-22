<?php
$title = 'ReportPro - Easy Report';
$currentRoute = '/reports';
ob_start();

// Preserve query parameters for navigation
$queryParams = $_GET;
unset($queryParams['url']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';

// Get base URL for subdirectory support
$appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
$baseUrl = rtrim($appUrl, '/');
?>



<style>
    .reports-container {
        padding: 0 20px 20px;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Masonry Layout */
    .reports-grid {
        column-count: 4;
        column-gap: 20px;
    }
    
    @media (max-width: 1600px) {
        .reports-grid { column-count: 4; }
    }
    @media (max-width: 1300px) {
        .reports-grid { column-count: 3; }
    }
    @media (max-width: 900px) {
        .reports-grid { column-count: 2; }
    }
    @media (max-width: 600px) {
        .reports-grid { column-count: 1; }
    }

    /* Cards */
    .report-card {
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        padding: 20px;
        margin-bottom: 20px;
        break-inside: avoid; /* Prevent card from splitting across columns */
        display: inline-block; /* Fix for break-inside support in some browsers */
        width: 100%;
        box-sizing: border-box;
    }

    /* Titles */
    .card-title {
        font-size: 22px;
        font-weight: 600;
        color: #212b36;
        margin: 0 0 15px 0;
    }
    
    /* Lists */
    .report-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .report-item {
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .report-item:last-child {
        margin-bottom: 0;
    }
    
    /* Links */
    .report-link {
        color: #2c6ecb;
        text-decoration: none;
        font-size: 16px;
    }
    .report-link:hover {
        text-decoration: underline;
    }

    /* Custom Report specific */
    .create-btn {
        background: #303030;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 15px;
        text-decoration: none;
        float: right;
    }
    .create-btn:hover {
        background: #101010;
        text-decoration: none;
    }
</style>

<div class="reports-container">
    <div class="reports-grid">
        
        <!-- Custom Reports Card -->
        <div class="report-card">
            <h3 class="card-title">
                Custom reports
                <a href="<?= $baseUrl ?>/reports/create<?= $suffix ?>" class="create-btn">Create custom report</a>
            </h3>
            <div class="card-content">
                <?php if (empty($reports)): ?>
                    <p style="color: #637381; font-size: 13px;">No custom reports yet.</p>
                <?php else: ?>
                    <ul class="report-list">
                        <?php foreach ($reports as $report): ?>
                            <li class="report-item">
                                <a href="<?= $baseUrl ?>/reports/<?= $report['id'] . $suffix ?>" class="report-link"><?= htmlspecialchars($report['name']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div style="margin-top: 10px;">
                     <a href="#" class="report-link">Copy of Orders by country</a>
                </div>
            </div>
        </div>

        <!-- Dynamic Categories -->
        <?php foreach ($dashboardCategories as $key => $category): ?>
            <div class="report-card">
                <h3 class="card-title"><?= htmlspecialchars($category['title']) ?></h3>
                <ul class="report-list">
                    <?php foreach ($category['items'] as $item): ?>
                        <li class="report-item">
                            <?php
                            $sep = strpos($item['url'], '?') !== false ? '&' : '?';
                            $fullUrl = $baseUrl . $item['url'] . ($queryString ? $sep . $queryString : '');
                            ?>
                            <a href="<?= $fullUrl ?>" class="report-link"><?= htmlspecialchars($item['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
