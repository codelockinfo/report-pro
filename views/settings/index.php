<?php
$title = 'Settings';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Settings</h1>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <form id="settings-form">
                    <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Week Start</label>
                            </div>
                            <div class="Polaris-Select">
                                <select name="week_start" class="Polaris-Select__Input">
                                    <option value="sunday" <?= ($settings['week_start'] ?? 'sunday') === 'sunday' ? 'selected' : '' ?>>Sunday</option>
                                    <option value="monday" <?= ($settings['week_start'] ?? '') === 'monday' ? 'selected' : '' ?>>Monday</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Locale</label>
                            </div>
                            <div class="Polaris-TextField">
                                <input type="text" name="locale" class="Polaris-TextField__Input" 
                                       value="<?= htmlspecialchars($settings['locale'] ?? 'en-US') ?>">
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Timezone</label>
                            </div>
                            <div class="Polaris-TextField">
                                <input type="text" name="timezone" class="Polaris-TextField__Input" 
                                       value="<?= htmlspecialchars($settings['timezone'] ?? 'UTC') ?>">
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Stack Polaris-Stack--distributionTrailing">
                                <button type="submit" class="Polaris-Button Polaris-Button--primary">
                                    <span class="Polaris-Button__Content">
                                        <span>Save Settings</span>
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
document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
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
        if (data.success) {
            alert('Settings saved successfully');
        } else {
            alert('Error: ' + (data.error || 'Failed to save settings'));
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

