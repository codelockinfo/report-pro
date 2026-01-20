<?php
$title = 'Scheduled Reports';
$currentRoute = '/schedule';
ob_start();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Scheduled Reports</h1>
        </div>
        <div class="Polaris-Page__Actions">
            <button onclick="showScheduleModal()" class="Polaris-Button Polaris-Button--primary">
                <span class="Polaris-Button__Content">
                    <span>Create Schedule</span>
                </span>
            </button>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <?php if (empty($schedules)): ?>
                    <div class="Polaris-EmptyState">
                        <div class="Polaris-EmptyState__Section">
                            <div class="Polaris-EmptyState__DetailsContainer">
                                <p class="Polaris-EmptyState__Text">No scheduled reports</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="Polaris-DataTable">
                        <table class="Polaris-DataTable__Table">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Frequency</th>
                                    <th>Next Run</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($schedule['report_name']) ?></td>
                                        <td><?= ucfirst($schedule['frequency']) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($schedule['next_run_at'])) ?></td>
                                        <td>
                                            <span class="Polaris-Badge Polaris-Badge--<?= $schedule['enabled'] ? 'success' : 'info' ?>">
                                                <?= $schedule['enabled'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="toggleSchedule(<?= $schedule['id'] ?>)" class="Polaris-Button Polaris-Button--plain">
                                                <?= $schedule['enabled'] ? 'Disable' : 'Enable' ?>
                                            </button>
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

<div id="schedule-modal" style="display: none;">
    <div class="Polaris-Modal">
        <div class="Polaris-Modal__Container">
            <div class="Polaris-Modal__Header">
                <h2 class="Polaris-Modal__Title">Create Schedule</h2>
            </div>
            <div class="Polaris-Modal__Body">
                <form id="schedule-form">
                    <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Report</label>
                            </div>
                            <div class="Polaris-Select">
                                <select name="report_id" class="Polaris-Select__Input" required>
                                    <option value="">Select a report</option>
                                    <?php foreach ($reports as $report): ?>
                                        <option value="<?= $report['id'] ?>"><?= htmlspecialchars($report['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Frequency</label>
                            </div>
                            <div class="Polaris-Select">
                                <select name="frequency" class="Polaris-Select__Input" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Stack Polaris-Stack--distributionTrailing">
                                <button type="submit" class="Polaris-Button Polaris-Button--primary">
                                    <span class="Polaris-Button__Content">
                                        <span>Create Schedule</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleModal() {
    document.getElementById('schedule-modal').style.display = 'block';
}

function toggleSchedule(id) {
    fetch(`/schedule/${id}/toggle`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    });
}

document.getElementById('schedule-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/schedule/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to create schedule'));
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

