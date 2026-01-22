<?php
$title = 'Reports';
$currentRoute = '/reports';
ob_start();

// Preserve query parameters for navigation
$queryParams = $_GET;
unset($queryParams['url']);
$queryString = http_build_query($queryParams);
$suffix = $queryString ? '?' . $queryString : '';

// Create query params without 'tab' for tab navigation links
$queryParamsWithoutTab = $queryParams;
unset($queryParamsWithoutTab['tab']);
$queryStringWithoutTab = http_build_query($queryParamsWithoutTab);

// Get base URL for subdirectory support
$appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
$baseUrl = rtrim($appUrl, '/');

// Get current tab from query parameter
$currentTab = $_GET['tab'] ?? '1'; // Default to tab 1 (All)

// Normalize tab values for backward compatibility
if ($currentTab === 'all') $currentTab = '1';
if ($currentTab === 'custom') $currentTab = '2';
if ($currentTab === 'favorites') $currentTab = '3';

// Flatten all reports into a single array for table display
$allReports = [];

// Add custom reports
if (!empty($reports)) {
    foreach ($reports as $report) {
        $allReports[] = [
            'id' => $report['id'],
            'name' => $report['name'],
            'category' => 'Custom',
            'url' => $baseUrl . '/reports/' . $report['id'] . $suffix,
            'is_custom' => true,
            'is_favorite' => false,
            'last_viewed' => 'about 18 hours ago',
            'views' => rand(1, 10)
        ];
    }
}

// Add predefined reports from categories
foreach ($dashboardCategories as $category) {
    foreach ($category['items'] as $item) {
        $sep = strpos($item['url'], '?') !== false ? '&' : '?';
        $fullUrl = $baseUrl . $item['url'] . ($queryString ? $sep . $queryString : '');
        
        $allReports[] = [
            'id' => null,
            'name' => $item['name'],
            'category' => $category['title'],
            'url' => $fullUrl,
            'is_custom' => false,
            'is_favorite' => false,
            'last_viewed' => 'about 18 hours ago',
            'views' => rand(1, 10)
        ];
    }
}

// Filter reports based on current tab
$filteredReports = $allReports;
if ($currentTab === '2') {
    $filteredReports = array_filter($allReports, fn($r) => $r['is_custom']);
} elseif ($currentTab === '3') {
    // Ideally this would filter by favorites, but for now empty or specific logic
    // $filteredReports = array_filter($allReports, fn($r) => $r['is_favorite']);
    // For demo purposes, let's just show favorites if we had that flag working
    $filteredReports = array_filter($allReports, fn($r) => $r['is_favorite']); 
}


$customCount = count(array_filter($allReports, fn($r) => $r['is_custom']));
$favoritesCount = count(array_filter($allReports, fn($r) => $r['is_favorite']));
?>

<!-- Immediate script to hide certain Shopify UI elements before they render -->
<script>
    (function() {
        // Create style element to hide specific Shopify UI elements immediately
        const style = document.createElement('style');
        style.textContent = `
            ui-loading, ui-loading-bar,
            .Polaris-Frame, .Polaris-TopBar, .Polaris-ActionMenu,
            .Polaris-Popover, .Polaris-Sheet, .Polaris-Backdrop,
            .Polaris-Portal, .Polaris-PositionedOverlay,
            [role="menu"], [role="dialog"], [aria-modal="true"],
            button[aria-haspopup="true"] {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
            }
        `;
        document.head.appendChild(style);
    })();
</script>

<style>
    /* CRITICAL: Hide ALL Shopify auto-generated UI elements */
    #app-bridge-host-container,
    .shopify-app-bridge-ui,
    ui-loading,
    ui-loading-bar,
    ui-modal,
    ui-save-bar,
    [data-polaris-layer],
    [data-polaris-overlay],
    .Polaris-Frame-Loading,
    .Polaris-Loading,
    .Polaris-Spinner,
    .Polaris-Frame,
    .Polaris-Frame-Navigation,
    .Polaris-Frame-TopBar,
    .Polaris-TopBar,
    .Polaris-ActionMenu,
    .Polaris-Popover,
    .Polaris-PositionedOverlay,
    .Polaris-Sheet,
    .Polaris-Backdrop,
    .Polaris-Portal,
    shopify-app-bridge-host,
    /* Hide any drawer/dropdown menus */
    [role="menu"],
    [role="dialog"],
    [aria-modal="true"],
    .Polaris-Select,
    .Polaris-OptionList,
    /* Hide three-dot menu buttons */
    button[aria-label*="Actions"],
    button[aria-label*="More"],
    button[aria-haspopup="true"],
    /* Hide any Shopify-generated containers */
    div[data-shopify],
    div[data-app-bridge] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
        pointer-events: none !important;
    }
    
    /* Force our container to be visible and take full space */
    #reports-page-container {
        display: block !important;
        width: 100% !important;
        min-height: 100vh !important;
        background: #f6f6f7 !important;
        position: relative !important;
        z-index: 9999 !important;
    }
    
    /* Override any Shopify styles */
    body {
        background: #f6f6f7 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
    }
    
    #app {
        background: #f6f6f7 !important;
        min-height: 100vh !important;
        display: block !important;
    }
    
    .Polaris-Page {
        background: #f6f6f7 !important;
        padding: 0 !important;
        max-width: 100% !important;
        margin: 0 !important;
        display: block !important;
    }
    
    
    /* Page Header */
    .Polaris-Page-Header {
        background: #F6F6F7;
        padding: 1rem 1.5rem;
    }
    
    .Polaris-Page-Header__Row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .Polaris-Header-Title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #202223;
        margin: 0;
    }
    
    .Polaris-ButtonGroup {
        display: flex;
        gap: 0.75rem;
    }
    
    .Polaris-Button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        font-size: 12px;
        font-weight: 500;
        border-radius: 0.375rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    
    .Polaris-Button--plain {
        background: white;
        color: #202223;
        border: 1px solid #c9cccf;
        padding: 6px 6px;
    }
    
    .Polaris-Button--plain:hover {
        background: #f6f6f7;
        border-color: #8c9196;
    }
    
    .Polaris-Button--primary {
        background: #202223;
        color: white;
        border: 1px solid #202223;
    }
    
    .Polaris-Button--primary:hover {
        background: #000000;
    }
    
    /* Tabs - Button Style */
    .Polaris-Tabs {
        background: white;
        border-bottom: 1px solid #e1e3e5;
        padding: 1rem 1.5rem 0.75rem;
    }
    
    .Polaris-Tabs__Wrapper {
        display: flex;
        gap: 0.5rem;
        border-bottom: none;
    }
    
    .Polaris-Tabs__Tab {
        padding: 0.5rem 0.75rem;
        color: #202223;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        background: #f6f6f7;
        border-radius: 0.5rem;
        transition: all 0.15s ease;
        display: inline-flex !important; /* Force inline flex */
        width: auto !important; /* Prevent stretching */
        flex: none !important; /* Prevent flex growth */
        align-items: center;
        gap: 0.375rem;
        white-space: nowrap;
        border: none;
        cursor: pointer;
    }
    
    .Polaris-Tabs__Tab:hover {
        background: #e4e5e7;
    }
    
    .Polaris-Tabs__Tab--selected {
        background: #e4e5e7;
        color: #202223;
    }
    
    .Polaris-Badge {
        background: transparent;
        color: #6d7175;
        padding: 0;
        border-radius: 0;
        font-size: 0.875rem;
        font-weight: 400;
    }
    
    .Polaris-Tabs__Tab--selected .Polaris-Badge {
        background: transparent;
        color: #202223;
    }
    
    /* Filters */
    .Polaris-Filters {
        background: white;
        padding: 8px;
        border-bottom: 1px solid #e1e3e5;
    }
    
    .Polaris-Filters__FiltersWrapper {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    
    .Polaris-TextField {
        flex: 1;
        position: relative;
    }
    
    .Polaris-TextField__Input {
        width: 100%;
        padding: 6px 12px 6px 3.5rem;
        border: 1px solid #c9cccf;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        color: #202223;
    }
    
    .Polaris-TextField__Input:focus {
        outline: none;
        border-color: #005bd3;
        box-shadow: 0 0 0 1px #005bd3;
    }
    
    .Polaris-TextField__Prefix {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6d7175;
        pointer-events: none;
    }
    
    .Polaris-Icon {
        width: 24px;
        height: 24px;
        display: block;
        fill: rgba(74, 74, 74, 1)!important;
        margin: 0px;
    }
    
    /* Index Table */
    .Polaris-IndexTable {
        background: white;
    }
    
    .Polaris-IndexTable__Table {
        width: 100%;
        border-collapse: collapse;
    }
    
    
    .Polaris-IndexTable__TableHeading {
        background: #f1f2f3; /* Slightly darker header background per screenshot */
        border-top: 1px solid #e1e3e5;
        border-bottom: 1px solid #e1e3e5;
    }
    
    .Polaris-IndexTable__TableHeading th {
        text-align: left;
        padding: 0.75rem 1rem; /* Adjusted padding */
        font-size: 12px;
        font-weight: 600;
        color: #202223; /* Darker text for headers */
        letter-spacing: normal; /* Removed letter spacing */
    }
    
    .Polaris-IndexTable__TableHeading th:first-child {
        width: 2.75rem; /* Slightly wider for star */
        padding-left: 1.5rem;
    }
    
    /* Table Rows */
    .Polaris-IndexTable__TableRow {
        background: white;
        border-bottom: 1px solid #e1e3e5;
        transition: background 0.1s ease;
    }
    
    .Polaris-IndexTable__TableRow:hover {
        background: #f7f8f9;
        cursor: pointer;
    }
    
    .Polaris-IndexTable__TableRow td {
        padding: 6px;
        font-size: 13px; /* 14px */
        color: #202223;
        vertical-align: middle;
    }
    
    .Polaris-IndexTable__TableRow td:first-child {
        padding-left: 1.5rem;
    }
    
    /* Star Icon */
    .star-icon {
        cursor: pointer;
        color: #8c9196; /* Lighter gray for inactive star */
        width: 1.25rem;
        height: 1.25rem;
        transition: all 0.15s ease;
        display: block;
    }
    
    .star-icon:hover {
        color: #5c5f62;
        transform: scale(1.1);
    }
    
    .star-icon.active {
        color: #ffc107; /* Yellow for active */
    }
    
    /* Link styling */
    .Polaris-Link {
        color: #202223;
        text-decoration: none;
        font-weight: 600; /* Bolder name */
        font-size: 13px;

    }
    
    /* .Polaris-Link:hover {
        color: #005bd3;
        text-decoration: underline;
    } */
    
    /* Badge for Category */
    .category-badge {
        background: #e4e5e7;
        color: #202223;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 400;
        display: inline-block;
        border: 1px solid #dcdedf;
    }
    
    /* Subdued Text (Dates, Views) */
    .Polaris-TextStyle--subdued {
        color: #6d7175;
    }
    
    /* Empty State */
    .Polaris-EmptyState {
        text-align: center;
        padding: 4rem 1.5rem;
    }
    
    .Polaris-EmptyState__Image {
        width: 3.75rem;
        height: 3.75rem;
        margin: 0 auto 1rem;
        color: #babfc3;
    }
    
    .Polaris-EmptyState__Content {
        max-width: 28rem;
        margin: 0 auto;
    }
    
    .Polaris-DisplayText {
        font-size: 1rem;
        font-weight: 600;
        color: #202223;
        margin: 0 0 0.5rem 0;
    }
    
    .Polaris-TextContainer p {
        font-size: 0.875rem;
        color: #6d7175;
        margin: 0;
    }
    
    /* Responsive */
    
    /* Layout Structure */
    .Polaris-Page {
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }

    .Polaris-Page__Content {
        padding: 1rem 1.5rem; /* Add padding around the card */
    }
    
    /* Card Container */
    .Polaris-Card {
        background: white;
        border-radius: 0.5rem; /* Rounded corners for the card */
        box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);
        overflow: hidden; /* Ensure content obeys rounded corners */
        border: 1px solid #e1e3e5;
    }
    
    /* Page Header */
    .Polaris-Page-Header {
        background: #F6F6F7;
        padding: 1rem 1.5rem;
        margin-bottom: 0;
    }
    
    /* Tabs inside Card */
    .Polaris-Tabs {
        background: white;
        border-bottom: 1px solid #e1e3e5;
        padding: 1rem 1.5rem 0.75rem;
    }
    
    /* Filters inside Card */
    /* .Polaris-Filters {
        background: white;
        padding: 8px;
        border-bottom: none;
    } */
    
    /* Index Table inside Card */
    .Polaris-IndexTable {
        background: white;
        border-radius: 0px;
    }

    @media (max-width: 768px) {
        .Polaris-Page-Header__Row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .Polaris-ButtonGroup {
            flex-direction: column;
        }
        
        .Polaris-Button {
            width: 100%;
        }
        
        .Polaris-Tabs__Wrapper {
            gap: 1.25rem;
        }
        
        .Polaris-Filters__FiltersWrapper {
            flex-direction: column;
        }
        
        .Polaris-IndexTable__TableHeading th{
            padding: 8px 6px;
            font-size: 12px;
        }

        .Polaris-IndexTable__TableRow td {
            padding: 6px;
            font-size: 13px;
        }
    }
</style>

<div id="reports-page-container">
<div class="Polaris-Page">
    <!-- Page Header (Full Width) -->
    <div class="Polaris-Page-Header">
        <div class="Polaris-Page-Header__Row">
            <h1 class="Polaris-Header-Title">Reports</h1>
            <div class="Polaris-ButtonGroup">
                <a href="#" class="Polaris-Button Polaris-Button--plain">Request custom report</a>
                <a href="<?= $baseUrl ?>/reports/create<?= $suffix ?>" class="Polaris-Button Polaris-Button--primary">Create report</a>
            </div>
        </div>
    </div>
    
    <!-- Main Content with Card -->
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <!-- Tabs -->
            <div class="Polaris-Tabs">
                <div class="Polaris-Tabs__Wrapper">
                    <a href="<?= $baseUrl ?>/reports?tab=1<?= $queryStringWithoutTab ? '&' . $queryStringWithoutTab : '' ?>" 
                       class="Polaris-Tabs__Tab <?= $currentTab === '1' ? 'Polaris-Tabs__Tab--selected' : '' ?>">
                        All reports
                        <span class="Polaris-Badge"><?= count($allReports) ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/reports?tab=2<?= $queryStringWithoutTab ? '&' . $queryStringWithoutTab : '' ?>" 
                       class="Polaris-Tabs__Tab <?= $currentTab === '2' ? 'Polaris-Tabs__Tab--selected' : '' ?>">
                        Custom reports
                        <span class="Polaris-Badge"><?= $customCount ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/reports?tab=3<?= $queryStringWithoutTab ? '&' . $queryStringWithoutTab : '' ?>" 
                       class="Polaris-Tabs__Tab <?= $currentTab === '3' ? 'Polaris-Tabs__Tab--selected' : '' ?>">
                        My favorites
                        <span class="Polaris-Badge"><?= $favoritesCount ?></span>
                    </a>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="Polaris-Filters">
                <div class="Polaris-Filters__FiltersWrapper">
                    <div class="Polaris-TextField">
                        <span class="Polaris-TextField__Prefix">
                            <svg class="Polaris-Icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/>
                            </svg>
                        </span>
                        <span class="Polaris-TextField__Input" style="border: none; padding: 0;">
                        <input 
                            type="text" 
                            class="Polaris-TextField__Input" 
                            placeholder="Search across <?= count($allReports) ?> reports..."
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            id="reportSearch"
                        >
                        </span>
                    </div>
                    <button class="Polaris-Button Polaris-Button--plain" style="margin-right: 1px;">
                        <svg viewBox="0 0 20 20" class="Polaris-Icon" focusable="false" aria-hidden="true"><path d="M3 6a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5h-12.5a.75.75 0 0 1-.75-.75Z"></path><path d="M6.75 14a.75.75 0 0 1 .75-.75h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75Z"></path><path d="M5.5 9.25a.75.75 0 0 0 0 1.5h9a.75.75 0 0 0 0-1.5h-9Z"></path></svg>
                    </button>
                </div>
            </div>
    
    <!-- Index Table -->
    <div class="Polaris-IndexTable">
        <?php if (empty($filteredReports)): ?>
            <div class="Polaris-EmptyState">
                <div class="Polaris-EmptyState__Image">
                    <svg viewBox="0 0 60 60" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="10" y="15" width="40" height="30" rx="2"/>
                        <line x1="15" y1="22" x2="25" y2="22"/>
                        <line x1="15" y1="27" x2="30" y2="27"/>
                        <line x1="15" y1="32" x2="27" y2="32"/>
                        <line x1="15" y1="37" x2="33" y2="37"/>
                    </svg>
                </div>
                <div class="Polaris-EmptyState__Content">
                    <h2 class="Polaris-DisplayText">No reports found</h2>
                    <div class="Polaris-TextContainer">
                        <p>
                            <?php if ($currentTab === 'favorites'): ?>
                                Star your favorite reports to see them here.
                            <?php elseif ($currentTab === 'custom'): ?>
                                Create your first custom report to get started.
                            <?php else: ?>
                                Try adjusting your search or filters.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <table class="Polaris-IndexTable__Table">
                <thead class="Polaris-IndexTable__TableHeading">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Last viewed</th>
                        <th>Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredReports as $report): ?>
                        <tr class="Polaris-IndexTable__TableRow">
                            <td>
                                <svg class="star-icon <?= $report['is_favorite'] ? 'active' : '' ?>" 
                                     viewBox="0 0 20 20" 
                                     fill="<?= $report['is_favorite'] ? 'currentColor' : 'none' ?>"
                                     stroke="currentColor"
                                     stroke-width="1.5"
                                     data-report-id="<?= $report['id'] ?? '' ?>"
                                     onclick="toggleFavorite(this)">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </td>
                            <td>
                                <a href="<?= $report['url'] ?>" class="Polaris-Link">
                                    <?= htmlspecialchars($report['name']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="category-badge"><?= htmlspecialchars($report['category']) ?></span>
                            </td>
                            <td>
                                <span class="Polaris-TextStyle--subdued"><?= htmlspecialchars($report['last_viewed']) ?></span>
                            </td>
                            <td>
                                <span class="Polaris-TextStyle--subdued"><?= $report['views'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
        </div>
    </div>
</div>
</div>

<script>
    // CRITICAL: Aggressively remove any Shopify-injected UI elements
    function removeShopifyUIElements() {
        const elementsToRemove = [
            '#app-bridge-host-container',
            '.shopify-app-bridge-ui',
            'ui-loading',
            'ui-loading-bar',
            'ui-modal',
            'ui-save-bar',
            '[data-polaris-layer]',
            '[data-polaris-overlay]',
            '.Polaris-Frame-Loading',
            '.Polaris-Loading',
            '.Polaris-Spinner',
            '.Polaris-Frame',
            '.Polaris-Frame-Navigation',
            '.Polaris-Frame-TopBar',
            '.Polaris-TopBar',
            '.Polaris-ActionMenu',
            '.Polaris-Popover',
            '.Polaris-PositionedOverlay',
            '.Polaris-Sheet',
            '.Polaris-Backdrop',
            '.Polaris-Portal',
            'shopify-app-bridge-host',
            '[role="menu"]',
            '[role="dialog"]',
            '[aria-modal="true"]',
            '.Polaris-Select',
            '.Polaris-OptionList',
            'button[aria-label*="Actions"]',
            'button[aria-label*="More"]',
            'button[aria-haspopup="true"]',
            'div[data-shopify]',
            'div[data-app-bridge]'
        ];
        
        elementsToRemove.forEach(selector => {
            try {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    // Don't remove elements inside our reports container
                    if (el && !el.closest('#reports-page-container')) {
                        if (el.parentNode) {
                            try {
                                el.parentNode.removeChild(el);
                            } catch (e) {
                                // Ignore errors
                            }
                        }
                    }
                });
            } catch (e) {
                // Ignore selector errors
            }
        });
        
        // Also remove any elements with Shopify-specific attributes
        try {
            const shopifyElements = document.querySelectorAll('[data-shopify], [data-app-bridge]');
            shopifyElements.forEach(el => {
                if (el && el.id !== 'reports-page-container' && !el.closest('#reports-page-container')) {
                    try {
                        if (el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    } catch (e) {
                        // Ignore errors
                    }
                }
            });
        } catch (e) {
            // Ignore errors
        }
    }
    
    // Run immediately (before DOM ready)
    removeShopifyUIElements();
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', removeShopifyUIElements);
    } else {
        removeShopifyUIElements();
    }
    
    // Run periodically to catch dynamically added elements (more frequently)
    setInterval(removeShopifyUIElements, 50);
    
    // Also run on any DOM mutations
    const observer = new MutationObserver(removeShopifyUIElements);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Search functionality
    const searchInput = document.getElementById('reportSearch');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = e.target.value;
                const currentUrl = new URL(window.location.href);
                
                if (searchValue) {
                    currentUrl.searchParams.set('search', searchValue);
                } else {
                    currentUrl.searchParams.delete('search');
                }
                
                window.location.href = currentUrl.toString();
            }, 500);
        });
    }
    
    // Toggle favorite functionality
    function toggleFavorite(element) {
        const reportId = element.getAttribute('data-report-id');
        const isActive = element.classList.contains('active');
        
        // Toggle visual state immediately
        if (isActive) {
            element.classList.remove('active');
            element.setAttribute('fill', 'none');
        } else {
            element.classList.add('active');
            element.setAttribute('fill', 'currentColor');
        }
        
        // TODO: Send AJAX request to save favorite state
        console.log('Toggle favorite for report:', reportId, 'New state:', !isActive);
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
