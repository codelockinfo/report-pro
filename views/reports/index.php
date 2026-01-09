<?php
$title = 'Reports';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Reports</h1>
        </div>
        <div class="Polaris-Page__Actions">
            <a href="/reports/create" class="Polaris-Button Polaris-Button--primary">
                <span class="Polaris-Button__Content">
                    <span>Create Report</span>
                </span>
            </a>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <div class="Polaris-Stack Polaris-Stack--vertical">
                    <div class="Polaris-Stack__Item">
                        <div class="Polaris-TextField">
                            <input type="text" id="search-input" class="Polaris-TextField__Input" 
                                   placeholder="Search reports..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="Polaris-Card" style="margin-top: 1rem;">
            <div class="Polaris-Card__Section">
                <?php if (empty($reports)): ?>
                    <div class="Polaris-EmptyState">
                        <div class="Polaris-EmptyState__Section">
                            <div class="Polaris-EmptyState__DetailsContainer">
                                <p class="Polaris-EmptyState__Text">No reports found</p>
                                <div class="Polaris-EmptyState__Actions">
                                    <a href="/reports/create" class="Polaris-Button Polaris-Button--primary">
                                        <span class="Polaris-Button__Content">
                                            <span>Create Your First Report</span>
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
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($report['name']) ?></td>
                                        <td><?= htmlspecialchars($report['category'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="Polaris-Badge Polaris-Badge--<?= $report['is_custom'] ? 'info' : 'success' ?>">
                                                <?= $report['is_custom'] ? 'Custom' : 'Predefined' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                                        <td>
                                            <div class="Polaris-ButtonGroup">
                                                <a href="/reports/<?= $report['id'] ?>" class="Polaris-Button Polaris-Button--plain">
                                                    View
                                                </a>
                                                <?php if ($report['is_custom']): ?>
                                                    <a href="/reports/<?= $report['id'] ?>/edit" class="Polaris-Button Polaris-Button--plain">
                                                        Edit
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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
</div>

<script>
document.getElementById('search-input').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        const search = this.value;
        window.location.href = '/reports?search=' + encodeURIComponent(search);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

