<?php
$title = 'Scheduled Reports';
$currentRoute = '/schedule';
ob_start();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<style>
    .schedule-container {
        padding: 0 20px 20px;
        max-width: 1200px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, "San Francisco", "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
    }

    /* Announcement Banner */
    .announcement-banner {
        background-color: #fff5ea;
        border: 1px solid #ffea8a;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13px;
        color: #202223;
    }
    .announcement-icon {
        font-size: 16px;
    }
    .announcement-link {
        color: #2c6ecb;
        text-decoration: none;
    }
    .announcement-link:hover {
        text-decoration: underline;
    }

    .usage-limit {
        font-size: 13px;
        color: #6d7175;
        margin-bottom: 20px;
    }
    .usage-link {
        color: #2c6ecb;
        text-decoration: none;
    }
    .usage-link:hover {
        text-decoration: underline;
    }

    /* Empty State Card */
    .empty-state-card {
        background: white;
        border: 1px solid #dfe3e8;
        border-radius: 8px; /* Slightly larger radius */
        padding: 80px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 500px;
        box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);
    }

    .icon-container {
        width: 120px;
        height: 120px;
        background-color: #8ce1ff; /* Light blue circle */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        position: relative;
    }
    
    /* CSS Calendar Icon to match screenshot */
    .calendar-icon {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 4px;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .calendar-header {
        height: 15px;
        background: #ff5230; /* Orange/Red header */
        border-radius: 4px 4px 0 0;
        width: 100%;
    }
    .calendar-body {
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        color: #212b36;
    }
    .calendar-binding {
        position: absolute;
        top: -6px;
        width: 6px;
        height: 12px;
        background: #454f5b;
        border-radius: 3px;
    }
    .binding-left { left: 12px; }
    .binding-right { right: 12px; }

    .empty-state-title {
        font-size: 16px;
        font-weight: 600;
        color: #202223;
        margin-bottom: 8px;
    }

    .empty-state-subtitle {
        font-size: 14px;
        color: #6d7175;
        margin-bottom: 30px;
    }

    .button-group {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .btn-secondary {
        background: white;
        border: 1px solid #babfc3;
        color: #202223;
    }
    .btn-secondary:hover {
        background: #f4f6f8;
    }

    .btn-primary {
        background: #303030;
        border: 1px solid #303030;
        color: white;
    }
    .btn-primary:hover {
        background: #101010;
    }

    /* Modal Styles (kept from previous code but styled simpler) */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .modal-header {
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: 600;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-label {
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
        font-weight: 500;
    }
    .form-select {
        width: 100%;
        padding: 8px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-size: 14px;
    }
</style>

<div class="schedule-container">
    <!-- Announcement Banner -->
    <div class="announcement-banner">
        <span class="announcement-icon">👉</span>
        <div class="text">
            <strong>Better Reports is making improvements!</strong><br>
            The <strong>Order lines</strong> table has been renamed to <strong>Agreement lines</strong>.<br>
            <a href="#" class="announcement-link">Read the full announcement of upcoming changes.</a>
        </div>
    </div>

    <!-- Usage Limit -->
    <div class="usage-limit">
        Estimated monthly scheduled runs: 0 of 1,000 <a href="#" class="usage-link">Learn more</a>
    </div>

    <!-- Content Area -->
    <?php if (empty($schedules)): ?>
        <!-- Empty State (Matches Screenshot) -->
        <div class="empty-state-card">
            <div class="icon-container">
                <div class="calendar-icon">
                    <div class="calendar-binding binding-left"></div>
                    <div class="calendar-binding binding-right"></div>
                    <div class="calendar-header"></div>
                    <div class="calendar-body">12</div>
                </div>
            </div>
            
            <h2 class="empty-state-title">Manage your schedules</h2>
            <p class="empty-state-subtitle">Create schedules to send reports via email or Google Sheets.</p>
            
            <div class="button-group">
                <button class="btn btn-secondary">Learn more</button>
                <button onclick="showScheduleModal()" class="btn btn-primary">Create Schedule</button>
            </div>
        </div>
    <?php else: ?>
        <!-- Existing Table Layout (Preserved for when schedules exist) -->
        <div class="empty-state-card" style="display: block; padding: 20px; min-height: auto; text-align: left;">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="empty-state-title">Your Schedules</h2>
                <button onclick="showScheduleModal()" class="btn btn-primary">Create Schedule</button>
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #dfe3e8; text-align: left;">
                        <th style="padding: 10px;">Report</th>
                        <th style="padding: 10px;">Frequency</th>
                        <th style="padding: 10px;">Next Run</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr style="border-bottom: 1px solid #f4f6f8;">
                            <td style="padding: 10px;"><?= htmlspecialchars($schedule['report_name']) ?></td>
                            <td style="padding: 10px;"><?= ucfirst($schedule['frequency']) ?></td>
                            <td style="padding: 10px;"><?= date('M d, Y H:i', strtotime($schedule['next_run_at'])) ?></td>
                            <td style="padding: 10px;">
                                <span style="background: <?= $schedule['enabled'] ? '#e4e8eb' : '#fff4f4' ?>; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                                    <?= $schedule['enabled'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td style="padding: 10px;">
                                <button onclick="toggleSchedule(<?= $schedule['id'] ?>)" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">
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

<!-- Modal (Preserved Logic) -->
<div id="schedule-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            Create Schedule
        </div>
        <form id="schedule-form">
            <div class="form-group">
                <label class="form-label">Report</label>
                <select name="report_id" class="form-select" required>
                    <option value="">Select a report</option>
                    <?php foreach ($reports as $report): ?>
                        <option value="<?= $report['id'] ?>"><?= htmlspecialchars($report['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Frequency</label>
                <select name="frequency" class="form-select" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('schedule-modal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Schedule</button>
            </div>
        </form>
    </div>
</div>

<script>
function showScheduleModal() {
    document.getElementById('schedule-modal').style.display = 'flex';
}

function toggleSchedule(id) {
    // Note: Append suffix logic might be needed here or handled by cookies/session if URL params are crucial for AJAX
    // For now assuming session works or referer is used. 
    // Appending window.location.search to ensure query params are passed if backend needs them
    const searchParams = new URLSearchParams(window.location.search);
    
    fetch(`/schedule/${id}/toggle` + window.location.search, {
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
    
    fetch('/schedule/store' + window.location.search, {
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

// Close modal when clicking outside
document.getElementById('schedule-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
