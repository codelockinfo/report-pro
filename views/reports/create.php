<?php
$title = 'Create Report';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Create Custom Report</h1>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <form id="report-form">
                    <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Report Name</label>
                            </div>
                            <div class="Polaris-TextField">
                                <input type="text" name="name" class="Polaris-TextField__Input" required>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Description</label>
                            </div>
                            <div class="Polaris-TextField">
                                <textarea name="description" class="Polaris-TextField__Input" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Dataset</label>
                            </div>
                            <div class="Polaris-Select">
                                <select name="dataset" id="dataset-select" class="Polaris-Select__Input" required>
                                    <option value="orders">Orders</option>
                                    <option value="products">Products</option>
                                    <option value="customers">Customers</option>
                                    <option value="transactions">Transactions</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label">Columns</label>
                            </div>
                            <div id="columns-container" class="Polaris-Stack Polaris-Stack--vertical">
                                <!-- Columns will be loaded dynamically -->
                            </div>
                        </div>
                        
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Stack Polaris-Stack--distributionTrailing">
                                <button type="submit" class="Polaris-Button Polaris-Button--primary">
                                    <span class="Polaris-Button__Content">
                                        <span>Create Report</span>
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
document.getElementById('dataset-select').addEventListener('change', function() {
    loadColumns(this.value);
});

function loadColumns(dataset) {
    fetch('/ajax/reports/columns', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'dataset=' + encodeURIComponent(dataset)
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('columns-container');
        container.innerHTML = '';
        
        data.columns.forEach(column => {
            const checkbox = document.createElement('div');
            checkbox.className = 'Polaris-Checkbox';
            checkbox.innerHTML = `
                <input type="checkbox" name="columns[]" value="${column.name}" id="col-${column.name}" class="Polaris-Checkbox__Input" checked>
                <label for="col-${column.name}" class="Polaris-Checkbox__Label">
                    <span class="Polaris-Checkbox__Backdrop"></span>
                    <span class="Polaris-Checkbox__Icon">
                        <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg">
                            <path d="M8.315 13.859l-3.182-3.417a.506.506 0 0 1 0-.684l.643-.684a.437.437 0 0 1 .642 0l2.22 2.393 4.942-5.327a.437.437 0 0 1 .642 0l.643.684a.504.504 0 0 1 0 .684l-5.91 6.351a.437.437 0 0 1-.642 0"></path>
                        </svg>
                    </span>
                    <span class="Polaris-Checkbox__LabelText">${column.label}</span>
                </label>
            `;
            container.appendChild(checkbox);
        });
    });
}

// Load initial columns
loadColumns('orders');

document.getElementById('report-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        if (data[key]) {
            if (Array.isArray(data[key])) {
                data[key].push(value);
            } else {
                data[key] = [data[key], value];
            }
        } else {
            data[key] = value;
        }
    });
    
    fetch('/reports/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/reports/' + data.report_id;
        } else {
            alert('Error: ' + (data.error || 'Failed to create report'));
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

