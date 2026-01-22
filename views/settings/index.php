<?php
$title = 'ReportPro - Easy Report';
$currentRoute = '/settings';
ob_start();
?>



<style>
    /* Settings Layout */
    .settings-layout {
        display: flex;
        gap: 30px;
        margin-top: 20px;
    }
    .settings-sidebar {
        width: 200px;
        flex-shrink: 0;
    }
    .settings-content {
        flex-grow: 1;
        background: white;
        border-radius: 4px;
        box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);
        padding: 20px;
    }

    /* Sidebar Navigation */
    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-item {
        margin-bottom: 5px;
    }
    .sidebar-link {
        display: block;
        padding: 8px 12px;
        border-radius: 4px;
        color: #2c6ecb;
        text-decoration: none;
        font-size: 14px;
    }
    .sidebar-link:hover {
        background-color: #f4f6f8;
        text-decoration: none;
    }
    .sidebar-link.active {
        background-color: #007ace;
        color: white;
    }

    /* Banner */
    .announcement-banner {
        background-color: #fff5ea;
        border: 1px solid #ffea8a;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .announcement-icon {
        font-size: 16px;
    }
    .announcement-text {
        font-size: 14px;
        color: #454f5b;
        line-height: 1.5;
    }
    .announcement-link {
        color: #2c6ecb;
        text-decoration: none;
    }
    .announcement-link:hover {
        text-decoration: underline;
    }

    /* Form Styles */
    .settings-section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #212b36;
    }
    .setting-group {
        margin-bottom: 25px;
    }
    .setting-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .setting-helper {
        margin-top: 5px;
        font-size: 13px;
        color: #637381;
    }
    .setting-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }
    .save-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #dfe3e8;
    }
</style>

<div class="Polaris-Page">
    <!-- Announcement Banner -->
    <div class="announcement-banner">
        <span class="announcement-icon">👉</span>
        <div class="announcement-text">
            <strong>Better Reports is making improvements!</strong><br>
            The <strong>Order lines</strong> table has been renamed to <strong>Agreement lines</strong>.<br>
            <a href="#" class="announcement-link">Read the full announcement of upcoming changes.</a>
        </div>
    </div>

    <div class="settings-layout">
<?php
// Preserve params for sidebar links
$queryParams = $_GET;
unset($queryParams['url']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';
?>

        <!-- Sidebar -->
        <div class="settings-sidebar">
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="/settings<?= $suffix ?>" class="sidebar-link active">General</a>
                </li>
                <li class="sidebar-item">
                    <a href="/settings/schedules<?= $suffix ?>" class="sidebar-link">Schedules</a>
                </li>
                <li class="sidebar-item">
                    <a href="/settings/integrations<?= $suffix ?>" class="sidebar-link">Integrations</a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="settings-content">
            <h2 class="settings-section-title">General settings</h2>
            
            <form id="settings-form">
                <div class="setting-group">
                    <label class="setting-label">Week</label>
                    <select name="week_start" class="setting-select">
                        <option value="sunday" <?= ($settings['week_start'] ?? 'sunday') === 'sunday' ? 'selected' : '' ?>>Sunday to Saturday</option>
                        <option value="monday" <?= ($settings['week_start'] ?? '') === 'monday' ? 'selected' : '' ?>>Monday to Sunday</option>
                    </select>
                    <p class="setting-helper">Used when segmenting (e.g. Total sales by week) and filtering (e.g. Last 4 weeks)</p>
                </div>

                <div class="setting-group">
                    <label class="setting-label">Locale</label>
                    <select name="locale" class="setting-select">
                        <option value="en-US" <?= ($settings['locale'] ?? 'en-US') === 'en-US' ? 'selected' : '' ?>>United States</option>
                        <option value="en-IN" <?= ($settings['locale'] ?? '') === 'en-IN' ? 'selected' : '' ?>>India</option>
                        <option value="en-GB" <?= ($settings['locale'] ?? '') === 'en-GB' ? 'selected' : '' ?>>United Kingdom</option>
                        <option value="en-CA" <?= ($settings['locale'] ?? '') === 'en-CA' ? 'selected' : '' ?>>Canada</option>
                        <option value="en-AU" <?= ($settings['locale'] ?? '') === 'en-AU' ? 'selected' : '' ?>>Australia</option>
                    </select>
                    <p class="setting-helper">For formatting dates and numbers</p>
                </div>

                <div class="save-actions">
                    <button type="submit" class="Polaris-Button Polaris-Button--primary">
                        <span class="Polaris-Button__Content">
                            <span>Save</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const btn = this.querySelector('button[type="submit"]');
    btn.classList.add('Polaris-Button--loading');
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        btn.classList.remove('Polaris-Button--loading');
        if (data.success) {
            // Shoipfy App Bridge Toast if available, otherwise alert
            if (window.app) {
                // var Toast = actions.Toast; // Need to ensure actions is available
                // Toast.create(app, { message: 'Settings saved' }).dispatch(Toast.Action.SHOW);
            }
            alert('Settings saved successfully');
        } else {
            alert('Error: ' + (data.error || 'Failed to save settings'));
        }
    })
    .catch(error => {
        btn.classList.remove('Polaris-Button--loading');
        alert('An error occurred');
        console.error(error);
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
