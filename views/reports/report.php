<?php
$title = 'ReportPro - Easy Report';
$currentRoute = '/reports';
ob_start();

// Preserve query parameters for nav (excluding those that trigger redirection back to this report)
$queryParams = $_GET;
unset($queryParams['url']);
unset($queryParams['type']);
unset($queryParams['id']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';

// Get base URL for subdirectory support
$appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
$baseUrl = rtrim($appUrl, '/');
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
        /* border-bottom: 1px solid #dfe3e8; */
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
        margin: 0 auto;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        color: #212b36;
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
        margin-bottom: 0;
        padding: 15px 0;
        /* border-bottom: 1px solid #dfe3e8; */
    }
    
    .add-filter-btn {
        padding: 6px 12px;
        background: #f4f6f8;
        border: 1px solid #c4cdd5;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
    }
    
    .sort-bar {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px 0;
        border-top: 1px solid #dfe3e8;
        border-bottom: 1px solid #dfe3e8;
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
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
    }
    .dropdown-icon {
        width: 16px;
        height: 16px;
        fill: currentColor;
        margin-left: 8px;
    }
    .sort-dropdown.secondary {
        border-style: dashed;
        color: #919eab;
        background: white;
        min-width: 150px;
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
    .action-bar {
        margin-bottom: 15px;
    }
    .edit-btn {
        background: #2c6ecb;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
    }
    .edit-btn:hover { background: #1f5199; }

    /* Table */
    .table-container {
        overflow-x: auto;
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
        background-color: #008060; /* Shopify Green for first data row */
        color: white;
        font-weight: 500;
    }
    
    .footer-count {
        text-align: right;
        margin-top: 10px;
        font-size: 12px;
        color: #637381;
    }
</style>

<div class="report-details-container">
    <a href="<?= $baseUrl ?>/reports<?= $suffix ?>" class="back-link">« Back to reports</a>
    
    <!-- Top Header with Title and Global Actions -->
    <div class="report-header">
        <div class="header-left">
            <?php 
            $config = json_decode($report['query_config'], true);
            $dataset = $config['dataset'] ?? 'Report';
            $badge = $dataset === 'aov_time' ? 'Orders' : ucwords(str_replace('_', ' ', $dataset));
            ?>
            <span class="report-badge"><?= htmlspecialchars($badge) ?></span>
            <div class="report-title-center"><?= htmlspecialchars($report['name']) ?></div>
        </div>
        
        <div class="header-actions">
            <!-- Visible on POS Dropdown -->
            <div class="dropdown-container">
                <button class="btn dropdown-trigger" onclick="toggleDropdown('posDropdown')">
                    Visible on POS (location) 
                    <svg viewBox="0 0 20 20" class="dropdown-icon" focusable="false" aria-hidden="true"><path d="m5 8 5 5 5-5H5z"></path></svg>
                </button>
                <div id="posDropdown" class="dropdown-content wide">
                    <div class="pos-option">
                        <div class="pos-title">Hidden on POS</div>
                        <div class="pos-desc">This report is only visible on the web admin</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="pos-option">
                        <div class="pos-title">Visible on POS</div>
                        <div class="pos-desc">This report is visible on both the web admin and POS apps</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="pos-option">
                        <div class="pos-title">Visible on POS (location)</div>
                        <div class="pos-desc">Visible on POS. Shows data relevant for the currently set location.</div>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary" onclick="runReport()">Run Report</button>
            <button class="btn" onclick="exportReport('csv')">Export</button>
            <button class="btn btn-primary" onclick="alert('Save as clicked')">Save as</button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-bar">
        <div class="dropdown-container">
            <button class="add-filter-btn dropdown-trigger" onclick="toggleDropdown('filterDropdown')">Add filter</button>
            <div id="filterDropdown" class="dropdown-content">
                <div class="dropdown-search">
                    <input type="text" placeholder="Search..." id="filterSearch" onkeyup="filterFunction()">
                </div>
                <div class="scrollable-menu" id="filterDropdownList">
                    <?php 
                    $columns = json_decode($report['query_config'], true)['columns'] ?? [];
                    // Mock grouping for display
                    echo '<div class="filter-group-title">Columns</div>';
                    foreach ($columns as $col) {
                        $niceName = ucwords(str_replace('_', ' ', $col));
                        echo '<div class="dropdown-item">' . $niceName . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sort and View Options -->
    <div class="sort-bar">
        <div class="sort-control">
            Sort by 
            
            <!-- Primary Sort Dropdown -->
            <div class="dropdown-container">
                <button class="sort-dropdown dropdown-trigger" onclick="toggleDropdown('sortDropdown')">
                    DESCENDING MONTH Date 
                    <svg viewBox="0 0 20 20" class="dropdown-icon" focusable="false" aria-hidden="true"><path d="m5 8 5 5 5-5H5z"></path></svg>
                </button>
                <div id="sortDropdown" class="dropdown-content narrow">
                    <div class="dropdown-item">Remove</div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item">DESCENDING</div>
                    <div class="dropdown-item">ASCENDING</div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item">Show subtotal for YEAR</div>
                    <div class="dropdown-item">Show subtotal for QUARTER</div>
                    <div class="dropdown-item">Show subtotal for MONTH</div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item">Show all</div>
                    <div class="dropdown-item">First</div>
                    <div class="dropdown-item">First 2</div>
                    <div class="dropdown-item">First 3</div>
                    <div class="dropdown-item">First 5</div>
                    <div class="dropdown-item">First 10</div>
                    <div class="dropdown-item">First 20</div>
                    <div class="dropdown-item">First 50</div>
                    <div class="dropdown-item">First 100</div>
                </div>
            </div>

            <!-- Secondary Sort Dropdown -->
            <div class="dropdown-container">
                <button class="sort-dropdown secondary dropdown-trigger" onclick="toggleDropdown('thenByDropdown')">
                    Then by... 
                    <svg viewBox="0 0 20 20" class="dropdown-icon" focusable="false" aria-hidden="true"><path d="m5 8 5 5 5-5H5z"></path></svg>
                </button>
                <div id="thenByDropdown" class="dropdown-content">
                    <div class="scrollable-menu">
                        <?php 
                        foreach ($columns as $col) {
                            $niceName = ucwords(str_replace('_', ' ', $col));
                            echo '<div class="dropdown-item">' . $niceName . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <label class="grand-total-check">
            <input type="checkbox"> Show grand total
        </label>
    </div>

    <!-- Edit Action -->
    <div class="action-bar">
        <button class="edit-btn">Edit</button>
    </div>

    <!-- Chart Visualization -->
    <div id="chart-wrap" style="display: none; background: white; border: 1px solid #dfe3e8; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <canvas id="reportChart" height="100"></canvas>
    </div>

    <!-- Data Table -->
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

<style>
    /* ... (previous styles) ... */
    
    /* Dropdown Styles */
    .dropdown-container {
        position: relative;
        display: inline-block;
    }
    
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 240px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        z-index: 1000;
        top: 100%;
        margin-top: 5px;
        text-align: left;
    }
    
    .dropdown-content.show {
        display: block;
    }
    
    /* Specific widths */
    .dropdown-content.wide { min-width: 300px; }
    .dropdown-content.narrow { min-width: 200px; }
    
    /* Dropdown Items */
    .dropdown-item {
        padding: 10px 16px;
        font-size: 13px;
        color: #212b36;
        cursor: pointer;
        display: block;
        text-decoration: none;
    }
    .dropdown-item:hover { background-color: #f4f6f8; }
    
    .dropdown-divider {
        height: 1px;
        background-color: #dfe3e8;
        margin: 4px 0;
    }
    
    /* Visible on POS specific */
    .pos-option {
        padding: 12px 16px;
    }
    .pos-option:hover { background-color: #f4f6f8; }
    .pos-title { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
    .pos-desc { color: #637381; font-size: 12px; line-height: 1.4; }
    
    /* Search Box in Dropdown */
    .dropdown-search {
        padding: 10px;
        border-bottom: 1px solid #dfe3e8;
    }
    .dropdown-search input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #c4cdd5;
        border-radius: 4px;
        font-size: 13px;
        box-sizing: border-box;
    }
    
    /* Filter functionality styles */
    .filter-group-title {
        padding: 8px 16px;
        font-weight: 600;
        font-size: 12px;
        color: #637381;
        background: #f9fafb;
    }
    
    /* Scrollable area */
    .scrollable-menu {
        max-height: 300px;
        overflow-y: auto;
    }
</style>

<script>
    <?php
    $appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
    $baseUrl = rtrim($appUrl, '/');
    $queryParams = $_GET;
    unset($queryParams['url']);
    $host = $_GET['host'] ?? '';
    ?>
    const reportId = <?= $report['id'] ?>;
    const baseUrl = "<?= $baseUrl ?>";
    const reportShopDomain = "<?= htmlspecialchars($shop['shop_domain']) ?>";
    const reportHost = "<?= htmlspecialchars($host) ?>";
    const reportColumns = <?php 
        $config = json_decode($report['query_config'], true);
        echo json_encode($config['columns'] ?? []); 
    ?>;
    const visualType = "<?= $config['visual_type'] ?? 'table' ?>";

    // Helper for fetch with shop and host params
    function shopFetch(url, options = {}) {
        const separator = url.includes('?') ? '&' : '?';
        let fullUrl = `${url}${separator}shop=${reportShopDomain}`;
        if (reportHost) {
            fullUrl += `&host=${reportHost}`;
        }
        return fetch(fullUrl, options);
    }
    
    // Toggle Dropdown
    function toggleDropdown(id) {
        // Close others first
        document.querySelectorAll('.dropdown-content').forEach(d => {
            if (d.id !== id) d.classList.remove('show');
        });
        document.getElementById(id).classList.toggle('show');
    }

    // Close when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.btn') && !event.target.matches('.dropdown-trigger') && !event.target.closest('.dropdown-trigger')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show') && !openDropdown.contains(event.target)) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    // Filter Search Logic
    function filterFunction() {
        var input, filter, div, a, i;
        input = document.getElementById("filterSearch");
        filter = input.value.toUpperCase();
        div = document.getElementById("filterDropdownList");
        a = div.getElementsByTagName("div");
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
    }
    
    // Core Report Functions
    function runReport() {
        shopFetch(`${baseUrl}/reports/${reportId}/run`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report generation started. Please wait...');
                setTimeout(() => window.location.reload(), 3000);
            } else {
                alert('Error: ' + (data.error || 'Failed to run report'));
            }
        })
        .catch(err => {
            console.error('Run Report Error:', err);
            alert('Failed to run report. Check console for details.');
        });
    }

    function exportReport(format) {
        shopFetch(`${baseUrl}/export/generate`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({report_id: reportId, format: format})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let downloadUrl = `${baseUrl}/export/${data.token}/download?shop=${reportShopDomain}`;
                if (reportHost) downloadUrl += `&host=${reportHost}`;
                window.location.href = downloadUrl;
            } else {
                alert('Error: ' + (data.error || 'Failed to export'));
            }
        });
    }

    // Load report data
    shopFetch(`${baseUrl}/reports/${reportId}/data`)
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                // Handle chart visualization
                if (visualType === 'chart') {
                    document.getElementById('chart-wrap').style.display = 'block';
                    renderChart(data.data);
                }
                
                const configColumns = reportColumns; // From PHP
                
                // Column definitions with formatters
                // Column definitions with formatters
                const columnDefs = {
                    'id': { label: 'Id', formatter: val => formatId(val) },
                    'created_at': { label: 'DAY Created at', key: 'createdAt', formatter: val => formatDate(val) },
                    'email': { label: 'Email' },
                    'accepts_marketing': { label: 'Accepts email marketing', key: 'acceptsMarketing', formatter: val => val ? 'Yes' : 'No' },
                    'full_name': { label: 'Full name', key: 'displayName' },
                    'country': { label: 'Country', key: 'defaultAddress', formatter: val => val?.country || (typeof val === 'string' ? val : '') }, // defaultAddress or shippingAddress??
                    // Wait, shippingAddress format is similar. row.shippingAddress.country.
                    // But key 'defaultAddress' won't work for Orders?
                    // Orders use `shippingAddress`.
                    // We need a smart key selector or formatter that checks both.
                    // Let's use a formatter that looks at row.
                    
                    'orders_count': { label: '# Orders', key: 'ordersCount' },
                    'total_spent': { label: 'Total spent', key: 'totalSpent', formatter: val => formatMoney(val) },
                    'average_order_value': { label: 'Average order value', formatter: (val, row) => calculateAOV(row) },
                    
                    // Orders formatters
                    'name': { label: 'Name' }, 
                    'total_price': { label: 'Total Price', key: 'totalPriceSet', formatter: val => formatMoney(val?.shopMoney) },
                    'financial_status': { label: 'Financial Status', key: 'financialStatus' },
                    'fulfillment_status': { label: 'Fulfillment Status', key: 'fulfillmentStatus' },
                    
                    // Products formatters
                    'title': { label: 'Title' },
                    'product_type': { label: 'Product Type', key: 'productType' },
                    'total_inventory': { label: 'Inventory', key: 'totalInventory' },
                    'status': { label: 'Status', formatter: val => val },
                    
                    // Transactions
                    'amount': { label: 'Amount', key: 'amountSet', formatter: val => formatMoney(val?.shopMoney) },
                    'currency_code': { label: 'Currency', key: 'amountSet', formatter: val => val?.shopMoney?.currencyCode || '' },
                    'kind': { label: 'Kind' },
                    'gateway': { label: 'Gateway' },
                    
                    // Inventory Levels
                    'available': { label: 'Available', key: 'available' },
                    'location_name': { label: 'Location', key: 'location', formatter: val => val?.name || '' },
                    'sku': { label: 'SKU', key: 'inventoryItem', formatter: val => val?.sku || '' },
                    'updated_at': { label: 'Updated At', key: 'updatedAt', formatter: val => formatDate(val) },
                    
                    // Line Items
                    'quantity': { label: 'Quantity', key: 'quantity' },
                    'price': { label: 'Price', key: 'priceSet', formatter: val => formatMoney(val?.shopMoney) },

                    // Sales Summary formatters
                    'total_orders': { label: 'Total orders' },
                    'total_gross_sales': { label: 'Total gross sales', formatter: val => formatMoney(val) },
                    'total_discounts': { label: 'Total discounts', formatter: val => formatMoney(val) },
                    'total_refunds': { label: 'Total refunds', formatter: val => formatMoney(val) },
                    'total_net_sales': { label: 'Total net sales', formatter: val => formatMoney(val) },
                    'total_taxes': { label: 'Total taxes', formatter: val => formatMoney(val) },
                    'total_shipping': { label: 'Total shipping', formatter: val => formatMoney(val) },
                    'total_sales': { label: 'Total sales', formatter: val => formatMoney(val) },
                    'total_cost_of_goods_sold': { label: 'Total cost of goods sold', formatter: val => formatMoney(val) },
                    'total_gross_margin': { label: 'Total gross margin', formatter: val => formatMoney(val) },

                    // AOV Time specific
                    'date': { label: 'Date', formatter: val => formatDate(val) },
                    'average_order_value': { label: 'Average order value', formatter: val => typeof val === 'object' ? calculateAOV(val) : formatMoney(val) },

                    // Browser Share specific
                    'browser': { label: 'Browser' },
                    'sessions_count': { label: 'Sessions', formatter: val => val ? val.toLocaleString() : '0' }
                };

                // Map PHP config columns to keys in data and display labels
                const activeColumns = configColumns.map(col => {
                    const def = columnDefs[col] || {};
                    return {
                        id: col,
                        key: def.key || col, 
                        label: def.label || formatColumnName(col),
                        formatter: def.formatter || (v => v)
                    };
                });

                // Header
                const headerRow = document.getElementById('table-headers');
                headerRow.innerHTML = activeColumns.map(col => `<th>${col.label}</th>`).join('');
                
                // Body
                const tbody = document.getElementById('table-body');
                tbody.innerHTML = data.data.map(row => {
                    return '<tr>' + activeColumns.map(col => {
                        let val;
                        // Manual fallbacks for mixed keys (orders vs customers)
                        if (col.id === 'country') {
                             val = row.defaultAddress || row.shippingAddress;
                        } else if (col.id === 'created_at') {
                             val = row.createdAt || row.created_at;
                        } else if (col.id === 'updated_at') {
                             val = row.updatedAt || row.updated_at;
                        } else {
                             val = row[col.key];
                        }
                        return `<td>${col.formatter(val ?? (col.id.includes('total') ? 0 : '-'), row)}</td>`;
                    }).join('') + '</tr>';
                }).join('');
                
                document.getElementById('record-count').innerText = `${data.data.length} records`;
            }
        });
        
    function formatColumnName(name) {
        return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    let reportChartInstance = null;
    function renderChart(data) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        
        if (reportChartInstance) {
            reportChartInstance.destroy();
        }

        // Get dataset type from logic
        const datasetType = "<?= $config['dataset'] ?? '' ?>";

        if (datasetType === 'browser_share') {
            const labels = data.map(row => row.browser);
            const values = data.map(row => parseInt(row.sessions_count));
            const colors = ['#5c6ac4', '#47c1bf', '#f49342', '#3f4eae', '#007ece', '#ffea8a', '#9c6ade'];

            reportChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Default Line Chart (e.g. AOV)
            const labels = data.map(row => formatDate(row.date));
            const values = data.map(row => parseFloat(row.average_order_value));

            reportChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average order value',
                        data: values,
                        borderColor: '#2c6ecb',
                        backgroundColor: 'rgba(44, 110, 203, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function formatId(id) {
        if (!id) return '';
        // Generic strip
        return id.replace(/^gid:\/\/shopify\/\w+\//, '');
    }

    function formatDate(dateString) {
        /* formatter remains same */
        if (!dateString) return '';
        const d = new Date(dateString);
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatMoney(amountObj) {
        if (!amountObj) return '0.00';
        let amount = typeof amountObj === 'object' ? amountObj.amount : amountObj;
        let currency = typeof amountObj === 'object' ? amountObj.currencyCode : '';
        
        try {
            return new Intl.NumberFormat('en-IN', {
                style: currency ? 'currency' : 'decimal',
                currency: currency || 'INR',
                minimumFractionDigits: 2
            }).format(amount);
        } catch (e) {
            return (currency ? currency + ' ' : '') + parseFloat(amount).toFixed(2);
        }
    }
    
    function calculateAOV(row) {
        /* calc remains same */
        let total = 0;
        if (row.totalSpent && row.totalSpent.amount) total = parseFloat(row.totalSpent.amount);
        
        let count = parseInt(row.ordersCount || 0);
        if (count === 0) return formatMoney(0);
        
        return formatMoney(total / count);
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
