<?php
$title = 'Dashboard';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Dashboard</h1>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Layout">
            <div class="Polaris-Layout__Section">
                <div class="Polaris-Card">
                    <div class="Polaris-Card__Section">
                        <h2 class="Polaris-Heading">Custom Reports</h2>
                        <?php if (empty($reports)): ?>
                            <div class="Polaris-EmptyState">
                                <div class="Polaris-EmptyState__Section">
                                    <div class="Polaris-EmptyState__DetailsContainer">
                                        <p class="Polaris-EmptyState__Text">No custom reports yet</p>
                                        <div class="Polaris-EmptyState__Actions">
                                            <a href="/reports/create" class="Polaris-Button Polaris-Button--primary">
                                                <span class="Polaris-Button__Content">
                                                    <span>Create Report</span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="Polaris-DataTable">
                                <table class="Polaris-DataTable__Table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $report): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($report['name']) ?></td>
                                                <td><?= htmlspecialchars($report['category'] ?? 'N/A') ?></td>
                                                <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                                                <td>
                                                    <a href="/reports/<?= $report['id'] ?>" class="Polaris-Button Polaris-Button--plain">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="Polaris-Layout__Section Polaris-Layout__Section--secondary">
                <div class="Polaris-Card">
                    <div class="Polaris-Card__Section">
                        <h2 class="Polaris-Heading">Scheduled Reports</h2>
                        <?php if (empty($schedules)): ?>
                            <p class="Polaris-TextStyle--variationSubdued">No scheduled reports</p>
                        <?php else: ?>
                            <ul class="Polaris-List">
                                <?php foreach ($schedules as $schedule): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($schedule['report_name']) ?></strong><br>
                                        <span class="Polaris-TextStyle--variationSubdued">
                                            <?= ucfirst($schedule['frequency']) ?> - Next: <?= date('M d, Y H:i', strtotime($schedule['next_run_at'])) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

