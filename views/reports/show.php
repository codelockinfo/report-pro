<?php
$title = $report['name'];
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge"><?= htmlspecialchars($report['name']) ?></h1>
        </div>
        <div class="Polaris-Page__Actions">
            <div class="Polaris-ButtonGroup">
                <button onclick="runReport()" class="Polaris-Button Polaris-Button--primary">
                    <span class="Polaris-Button__Content">
                        <span>Run Report</span>
                    </span>
                </button>
                <button onclick="exportReport('csv')" class="Polaris-Button">
                    <span class="Polaris-Button__Content">
                        <span>Export CSV</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <?php if ($result): ?>
            <div class="Polaris-Card">
                <div class="Polaris-Card__Section">
                    <div class="Polaris-DataTable">
                        <table class="Polaris-DataTable__Table" id="report-table">
                            <thead>
                                <tr id="table-headers">
                                    <!-- Headers will be loaded dynamically -->
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="Polaris-Card">
                <div class="Polaris-Card__Section">
                    <div class="Polaris-EmptyState">
                        <div class="Polaris-EmptyState__Section">
                            <div class="Polaris-EmptyState__DetailsContainer">
                                <p class="Polaris-EmptyState__Text">No data available. Run the report to generate data.</p>
                                <div class="Polaris-EmptyState__Actions">
                                    <button onclick="runReport()" class="Polaris-Button Polaris-Button--primary">
                                        <span class="Polaris-Button__Content">
                                            <span>Run Report</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const reportId = <?= $report['id'] ?>;

function runReport() {
    fetch(`/reports/${reportId}/run`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report generation started. Please wait...');
            setTimeout(() => {
                window.location.reload();
            }, 5000);
        } else {
            alert('Error: ' + (data.error || 'Failed to run report'));
        }
    });
}

function exportReport(format) {
    fetch('/export/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            report_id: reportId,
            format: format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/export/${data.token}/download`;
        } else {
            alert('Error: ' + (data.error || 'Failed to export'));
        }
    });
}

// Load report data
fetch(`/reports/${reportId}/data`)
    .then(response => response.json())
    .then(data => {
        if (data.data && data.data.length > 0) {
            const headers = Object.keys(data.data[0]);
            const headerRow = document.getElementById('table-headers');
            headerRow.innerHTML = headers.map(h => `<th>${h.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</th>`).join('');
            
            const tbody = document.getElementById('table-body');
            tbody.innerHTML = data.data.map(row => {
                return '<tr>' + headers.map(h => `<td>${row[h] || ''}</td>`).join('') + '</tr>';
            }).join('');
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

