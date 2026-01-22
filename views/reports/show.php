<?php
$title = $report['name'];
$currentRoute = '/reports';
ob_start();

// Preserve query parameters for nav (excluding those that trigger redirection back to this report)
$queryParams = $_GET;
unset($queryParams['url']);
unset($queryParams['type']);
unset($queryParams['id']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';
?>



<style>
    .report-details-container {
        padding: 0 20px 20px;
        background: white;
        min-height: calc(100vh - 60px);
    }
    
    /* Back Link */
    .back-link {
        display: inline-block;
        padding: 15px 0;
        color: #2c6ecb;
        text-decoration: none;
        font-size: 18px;
        margin-bottom: 10px;
    }
    .back-link:hover { text-decoration: underline; }
    
    /* Header Area */
    .report-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        border-bottom: 1px solid #dfe3e8;
        padding-bottom: 20px;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
        flex: 1;
    }
    
    .report-badge {
        background: #e4e8eb;
        color: #454f5b;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
    }
    
    .report-title-center {
        font-size: 16px;
        font-weight: 600;
        margin: 0 auto; /* Centers it if flex space allows */
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        padding: 7px 16px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        border: 1px solid #c4cdd5;
        background: white;
        color: #212b36;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn:hover { background: #f9fafb; }
    .btn-primary {
        background: #2c6ecb;
        border-color: #2c6ecb;
        color: white;
    }
    .btn-primary:hover { background: #1f5199; }
    
    /* Filter Bar */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px 0;
        border-bottom: 1px solid #dfe3e8;
    }
    
    .add-filter-btn {
        padding: 6px 12px;
        background: #f4f6f8;
        border: 1px solid #c4cdd5;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .sort-control {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #637381;
    }
    .sort-dropdown {
        padding: 6px 12px;
        border: 1px solid #c4cdd5;
        border-radius: 4px;
        background: linear-gradient(to bottom, #fff, #f9fafb);
        color: #212b36;
        min-width: 250px;
        text-align: left;
    }
    .sort-dropdown.secondary {
        border-style: dashed;
        color: #919eab;
        background: white;
    }
    
    .grand-total-check {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #212b36;
    }

    /* Edit Button */
    .edit-btn {
        background: #2c6ecb;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        margin-bottom: 20px;
        cursor: pointer;
    }
    .edit-btn:hover { background: #1f5199; }

    /* Table */
    .table-container {
        overflow-x: auto;
        margin-top: 20px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table th {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid #dfe3e8;
        font-weight: 600;
        color: #212b36;
    }
    .data-table td {
        padding: 10px;
        border-bottom: 1px solid #dfe3e8;
        color: #212b36;
    }
    .data-table tr:first-child td {
        /* Green highlight for first row as seen in screenshot */
        background-color: #e3f1df; 
    }
    
    .footer-count {
        text-align: right;
        margin-top: 10px;
        font-size: 12px;
        color: #637381;
    }
</style>

<div class="report-details-container">
    <a href="/reports<?= $suffix ?>" class="back-link">« Back to reports</a>
    
    <div class="report-header">
        <div class="header-left">
            <span class="report-badge"><?= htmlspecialchars($report['name']) ?></span>
            <div class="report-title-center"><?= htmlspecialchars($report['name']) ?></div>
        </div>
        
        <div class="header-actions">
            <button class="btn">Visible on POS ⌄</button>
            <button class="btn" onclick="window.location.reload()">Refresh</button>
            <button class="btn" onclick="exportReport('csv')">Export</button>
            <button class="btn btn-primary" onclick="alert('Save as clicked')">Save as</button>
        </div>
    </div>

    <div class="filter-bar">
        <button class="add-filter-btn">Add filter</button>
    </div>
    
    <div class="filter-bar" style="border-bottom: 1px solid #dfe3e8; padding-top: 0;">
        <div class="sort-control">
            Sort by 
            <button class="sort-dropdown">DESCENDING DAY Created at ⌄</button>
            <button class="sort-dropdown secondary">Then by... ⌄</button>
        </div>
        
        <label class="grand-total-check">
            <input type="checkbox"> Show grand total
        </label>
    </div>

    <button class="edit-btn">Edit</button>

    <div class="table-container">
        <?php if ($result): ?>
            <table class="data-table">
                <thead>
                    <tr id="table-headers">
                        <!-- Loaded dynamically -->
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
            <div class="footer-count" id="record-count"></div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #637381;">
                <p>No data available. Run the report to generate data.</p>
                <button onclick="runReport()" class="btn btn-primary" style="margin-top: 10px;">Run Report</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const reportId = <?= $report['id'] ?>;

function runReport() {
    fetch(`/reports/${reportId}/run`, { method: 'POST' })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report generation started. Please wait...');
            setTimeout(() => window.location.reload(), 3000);
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
            
            document.getElementById('record-count').innerText = `${data.data.length} records`;
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
