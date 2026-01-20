<?php
$title = 'Reports';
$currentRoute = '/reports';
ob_start();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<style>
    .dashboard-container {
        padding: 0 20px 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .search-container {
        margin-bottom: 20px;
    }
    .search-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }
    
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        align-items: start;
    }
    
    @media (max-width: 1000px) {
        .reports-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 700px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }
    }

    .report-card {
        background: white;
        border-radius: 4px;
        box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);
        padding: 20px;
        margin-bottom: 20px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #212b36;
        margin: 0;
    }
    .card-action {
        background: #303030;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
    }
    .report-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .report-item {
        margin-bottom: 8px;
    }
    .report-link {
        color: #2c6ecb;
        text-decoration: none;
        font-size: 14px;
    }
    .report-link:hover {
        text-decoration: underline;
    }
</style>

<div class="dashboard-container">
    <!-- Search Bar -->
    <form action="/reports" method="GET" class="search-container">
        <input type="text" name="search" class="search-input" placeholder="Search by report name..." value="<?= htmlspecialchars($search ?? '') ?>">
    </form>

    <!-- 3-Column Grid -->
    <div class="reports-grid">
        <!-- Left Column -->
        <div class="grid-column">
            <!-- Custom Reports -->
            <div class="report-card">
                <div class="card-header">
                    <h3 class="card-title">Custom reports</h3>
                    <a href="/reports/create" class="card-action">Create custom report</a>
                </div>
                <div class="card-content">
                    <?php if (empty($reports)): ?>
                        <p style="color: #637381; font-size: 14px;">No custom reports yet.</p>
                    <?php else: ?>
                        <ul class="report-list">
                            <?php foreach ($reports as $report): ?>
                                <li class="report-item">
                                    <a href="/reports/<?= $report['id'] ?>" class="report-link"><?= htmlspecialchars($report['name']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories -->
            <?php if (isset($dashboardCategories['left_column'])): ?>
                <?php foreach ($dashboardCategories['left_column'] as $key => $category): ?>
                    <div class="report-card">
                        <h3 class="card-title" style="margin-bottom: 15px;"><?= htmlspecialchars($category['title']) ?></h3>
                        <ul class="report-list">
                            <?php foreach ($category['items'] as $item): ?>
                                <li class="report-item">
                                    <a href="<?= $item['url'] ?>" class="report-link"><?= htmlspecialchars($item['name']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Middle Column -->
        <div class="grid-column">
            <?php if (isset($dashboardCategories['middle_column'])): ?>
                <?php foreach ($dashboardCategories['middle_column'] as $key => $category): ?>
                    <div class="report-card">
                        <h3 class="card-title" style="margin-bottom: 15px;"><?= htmlspecialchars($category['title']) ?></h3>
                        <ul class="report-list">
                            <?php foreach ($category['items'] as $item): ?>
                                <li class="report-item">
                                    <a href="<?= $item['url'] ?>" class="report-link"><?= htmlspecialchars($item['name']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="grid-column">
            <?php if (isset($dashboardCategories['right_column'])): ?>
                <?php foreach ($dashboardCategories['right_column'] as $key => $category): ?>
                    <div class="report-card">
                        <h3 class="card-title" style="margin-bottom: 15px;"><?= htmlspecialchars($category['title']) ?></h3>
                        <ul class="report-list">
                            <?php foreach ($category['items'] as $item): ?>
                                <li class="report-item">
                                    <a href="<?= $item['url'] ?>" class="report-link"><?= htmlspecialchars($item['name']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
