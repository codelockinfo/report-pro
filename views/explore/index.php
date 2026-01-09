<?php
$title = 'Explore Reports';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Explore Reports</h1>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <?php foreach ($categories as $key => $category): ?>
            <div class="Polaris-Card" style="margin-bottom: 1rem;">
                <div class="Polaris-Card__Section">
                    <h2 class="Polaris-Heading"><?= htmlspecialchars($category['name']) ?></h2>
                    <div class="Polaris-Stack Polaris-Stack--vertical" style="margin-top: 1rem;">
                        <?php foreach ($category['reports'] as $report): ?>
                            <div class="Polaris-Stack__Item">
                                <div class="Polaris-Card">
                                    <div class="Polaris-Card__Section">
                                        <h3 class="Polaris-Subheading"><?= htmlspecialchars($report['name']) ?></h3>
                                        <p class="Polaris-TextStyle--variationSubdued"><?= htmlspecialchars($report['description']) ?></p>
                                        <div style="margin-top: 0.5rem;">
                                            <a href="/reports/predefined/<?= $key ?>" class="Polaris-Button Polaris-Button--plain">
                                                View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

