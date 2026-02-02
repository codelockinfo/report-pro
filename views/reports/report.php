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
        padding: 0;
        background: #f6f6f7;
        min-height: calc(100vh - 60px);
    }
    
    /* Top Header Bar - FIRST RED BOX */
    .top-header {
        background: #f6f6f7 !important;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .header-left-section {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    /* Back Link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #202223;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .back-link:hover {
        color: #000000;
    }
    .back-link:hover .back-arrow {
        transform: translateX(-3px);
    }
    .back-arrow {
        width: 18px;
        height: 18px;
        transition: transform 0.2s ease;
    }
    
    .report-title {
        font-size: 16px;
        font-weight: 600;
        color: #202223;
        margin: 0;
    }
    
    .title-icons {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .icon-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #6d7175;
        display: flex;
        align-items: center;
    }
    .icon-btn:hover {
        color: #202223;
    }
    .icon-btn svg {
        width: 16px;
        height: 16px;
    }
    
    /* Header Actions */
    .header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .rp-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 10px;
        padding: 5px 10px 7px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
        text-decoration: none;
    }

    /* Secondary Button (White/Outline based on image intent, but using user CSS structure if requested. 
       User provided: background #D4D4D4. 
       Image shows: White background, gray border.
       I will adapt the user's CSS to match the IMAGE as requested "with mared button on image" 
       while keeping the provided class structure. */
    .rp-btn-secondary {
        background: #D4D4D4;
        color: #202223;
        border-color: #D4D4D4;
        box-shadow: 0 1px 0 rgba(0,0,0,0.05);
    }

    .rp-btn-secondary:hover {
        background:rgb(196, 196, 196);
        border-color: rgb(196, 196, 196);
    }
    
    .rp-btn-secondary svg {
        fill: rgb(74 74 74); /* Blue icons like the image */
        width: 16px;
        height: 16px;
    }

    .rp-btn-primary {
        background: rgb(32, 34, 35); /* Dark like image */
        /* background: rgb(65 65 65); */
        color: #ffffff;
        border-color:rgb(0, 0, 0);
        border-bottom: 2px solid rgba(10, 10, 10, 1);
        border-right: 1.5px solid rgba(10, 10, 10, 1);
    }
    .rp-btn-primary:active,
    .rp-btn-primary:hover {
        background: rgb(0, 0, 0);
    }
    
    /* Content Card - SECOND RED BOX */
    .content-card {
        background: white;
        margin: 20px;
        border-radius: 12px;
        overflow: visible !important;
    }
    
    .report-details-container {
        overflow: visible !important;
        padding: 0px 10px 500px 10px; /* Hack to ensure scroll space for popover */

    }

    /* Date Picker Safe Scroll */
    .dateRangePicker__Wrapper--lMP7K {
        max-height: 80vh;
        overflow-y: auto;
    }
    
    /* Description Banner */
    .description-banner {
        color: #5700d1;
        padding: 8px 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border-bottom: 1px solid #e1e3e5;
        align-items: center;
    }
    .description-banner svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .description-text {
        font-size: 11px;
        line-height: 1.5;
    }
    
    /* Control Bar */
    .control-bar {
        background: white;
        border-bottom: 1px solid #e1e3e5;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    
    .date-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #202223;
    }
    .date-selector svg {
        width: 16px;
        height: 16px;
    }
    .date-range {
        padding: 6px 12px;
        border: 1px solid #c9cccf;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 13px;
    }
    .date-range:hover {
        border-color: #8c9196;
    }
    
    .processing-status {
        font-size: 13px;
        color: #6d7175;
    }
    
    .control-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .control-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 13px;
        color: #202223;
        border-radius: 6px;
        transition: background 0.15s ease;
    }
    .control-btn:hover {
        background: #f6f6f7;
    }
    .control-btn svg {
        width: 16px;
        height: 16px;
    }
    
    /* Table Container with Border Radius */
    .table-wrapper {
        background: white;
        margin: 0;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table th {
        text-align: left;
        padding: 12px 16px;
        border-bottom: 1px solid #e1e3e5;
        font-weight: 600;
        color: #202223;
        background: #fafbfb;
        position: relative;
    }
    .data-table th:hover {
        background: #f6f6f7;
    }
    .column-header {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .column-header svg {
        width: 12px;
        height: 12px;
        color: #8c9196;
    }
    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e1e3e5;
        color: #202223;
    }
    .data-table tbody tr:hover {
        background: #f6f6f7;
    }
    
    .footer-count {
        text-align: center;
        padding: 16px;
        font-size: 12px;
        color: #6d7175;
        background: white;
        border-top: 1px solid #e1e3e5;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6d7175;
    }
    .empty-state p {
        margin: 0 0 16px 0;
        font-size: 14px;
    }
</style>

<div class="report-details-container">
    <!-- FIRST RED BOX: Top Header Bar -->
    <div class="top-header">
        <div class="header-left-section">
            <a href="<?= $baseUrl ?>/reports<?= $suffix ?>" class="back-link">
                <svg class="back-arrow" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
            <h1 class="report-title"><?= htmlspecialchars($report['name']) ?></h1>
            <div class="title-icons">
                <button class="icon-btn star-button <?= ($report['is_favorite'] ?? 0) ? 'active' : '' ?>" 
                        title="Favorite" 
                        onclick="toggleReportFavorite(this, <?= $report['id'] ?>)">
                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true" 
                         style="fill: <?= ($report['is_favorite'] ?? 0) ? '#FFB100' : 'rgb(74, 74, 74)' ?>; color: <?= ($report['is_favorite'] ?? 0) ? '#FFB100' : 'rgb(74, 74, 74)' ?>">
                        <path fill-rule="evenodd" d="M8.872 4.123c.453-.95 1.803-.95 2.256 0l1.39 2.912 3.199.421c1.042.138 1.46 1.422.697 2.146l-2.34 2.222.587 3.172c.192 1.034-.901 1.828-1.825 1.327l-2.836-1.54-2.836 1.54c-.924.501-2.017-.293-1.825-1.327l.587-3.172-2.34-2.222c-.762-.724-.345-2.008.697-2.146l3.2-.421 1.389-2.912Zm1.128 1.119-1.222 2.561a1.25 1.25 0 0 1-.965.701l-2.814.371 2.058 1.954c.307.292.446.718.369 1.134l-.517 2.791 2.495-1.354a1.25 1.25 0 0 1 1.192 0l2.495 1.354-.517-2.79a1.25 1.25 0 0 1 .369-1.135l2.058-1.954-2.814-.37a1.25 1.25 0 0 1-.965-.702l-1.222-2.561Z"></path>
                    </svg>
                </button>
                <button class="icon-btn" title="Edit">
                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true" style="fill: rgb(74, 74, 74);"><path fill-rule="evenodd" d="M15.655 4.344a2.695 2.695 0 0 0-3.81 0l-.599.599-.009-.009-1.06 1.06.008.01-5.88 5.88a2.75 2.75 0 0 0-.805 1.944v1.922a.75.75 0 0 0 .75.75h1.922a2.75 2.75 0 0 0 1.944-.806l7.54-7.539a2.695 2.695 0 0 0 0-3.81Zm-4.409 2.72-5.88 5.88a1.25 1.25 0 0 0-.366.884v1.172h1.172c.331 0 .65-.132.883-.366l5.88-5.88-1.689-1.69Zm2.75.629.599-.599a1.195 1.195 0 1 0-1.69-1.689l-.598.599 1.69 1.689Z"></path></svg>
                </button>
                <button class="icon-btn" title="Undo">
                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true" style="fill: rgb(74, 74, 74);"><path d="M7.47 3.72a.75.75 0 0 1 1.06 1.06l-1.72 1.72h3.94a5 5 0 0 1 0 10h-1.5a.75.75 0 0 1 0-1.5h1.5a3.5 3.5 0 1 0 0-7h-3.94l1.72 1.72a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3Z"></path></svg>
                </button>
                <button class="icon-btn" title="Redo">
                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true" style="fill: rgb(74, 74, 74);"><path d="M12.53 3.72a.75.75 0 1 0-1.06 1.06l1.72 1.72h-3.94a5 5 0 0 0 0 10h1.5a.75.75 0 0 0 0-1.5h-1.5a3.5 3.5 0 1 1 0-7h3.94l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3a.75.75 0 0 0 0-1.06l-3-3Z"></path></svg>
                </button>
            </div>
        </div>
        
        <div class="header-actions">
            <!-- AI Button -->
            <!-- <button class="rp-btn rp-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13 7H7v6h6V7z"/><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/></svg>
                AI
            </button> -->
            
            <button class="rp-btn rp-btn-primary" onclick="runReport()" style="margin-right: 8px;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; margin-right: 6px;"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                Sync Data
            </button>
            
            <!-- Export Button -->
            <button class="rp-btn rp-btn-secondary" onclick="exportReport('csv')">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                Export
            </button>
            
            <!-- Share Button -->
            <button class="rp-btn rp-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/></svg>
                Share
            </button>
            
            <!-- Print Button -->
            <button class="rp-btn rp-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/></svg>
                Print
            </button>
            
            <!-- More Actions Button -->
            <!-- More Actions Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button class="rp-btn rp-btn-secondary dropdown-trigger" onclick="toggleDropdown('moreActionsMenu')">
                    More actions
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div id="moreActionsMenu" class="dropdown-content more-actions-menu">
                    <div class="more-action-item">
                        <span class="more-action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </span>
                        Copy and edit
                    </div>
                    <div class="more-action-item">
                        <span class="more-action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </span>
                        Add to dashboard
                    </div>
                    <div class="more-action-item">
                        <span class="more-action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        </span>
                        Insert to Google Sheets
                    </div>
                    <div class="more-action-item">
                        <span class="more-action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </span>
                        Refresh data
                    </div>
                    <div class="more-action-item">
                        <span class="more-action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </span>
                        Move to trash
                    </div>
                </div>
            </div>
            
            <!-- Save Button (Primary) -->
            <button class="rp-btn rp-btn-primary" onclick="runReport()">Save</button>
        </div>
    </div>
    
    <!-- SECOND RED BOX: Content Card (Purple Banner + Table) -->
    <div class="content-card">
        <!-- Description Banner -->
        <?php 
        $config = json_decode($report['query_config'], true);
        $dataset = $config['dataset'] ?? 'Report';
        $badge = $dataset === 'aov_time' ? 'Orders' : ucwords(str_replace('_', ' ', $dataset));
        ?>
        <div class="description-banner">
            <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="description-text">
                <?= htmlspecialchars($report['description']) ?>
            </div>
        </div>
        
        <!-- Control Bar -->
        <div class="control-bar">
            <div class="mr-auto grow md:mr-0">
                <div class="Look__Toolbar--xFZP+">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-nowrap gap-2">
                            <div class="PrimaryFilters__PrimaryFilter--32fOO">
                                <div class="dateRangePicker__Container--2wpat">
                                    <div>
                                        <div class="">
                                            <div class="DataRangeFilter__Button--A+vc1" id="PrimaryFilter" onclick="toggleDatePopover(event)">
                                                <div class="DataRangeFilter__Content--Q7tLy">
                                                    <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.75 3.5a.75.75 0 0 0-1.5 0v.407a3.075 3.075 0 0 0-.702.252 3.75 3.75 0 0 0-1.64 1.639c-.226.444-.32.924-.365 1.47-.043.531-.043 1.187-.043 2v1.464c0 .813 0 1.469.043 2 .045.546.14 1.026.366 1.47a3.75 3.75 0 0 0 1.639 1.64c.444.226.924.32 1.47.365.531.043 1.187.043 2 .043h3.383c.323 0 .542 0 .735-.02a3.75 3.75 0 0 0 3.344-3.344c.02-.193.02-.412.02-.735v-2.883c0-.813 0-1.469-.043-2-.045-.546-.14-1.026-.366-1.47a3.75 3.75 0 0 0-1.639-1.64 3.076 3.076 0 0 0-.702-.251v-.407a.75.75 0 0 0-1.5 0v.259c-.373-.009-.794-.009-1.268-.009h-1.964c-.474 0-.895 0-1.268.009v-.259Zm-1.521 1.995c.197-.1.458-.17.912-.207.462-.037 1.057-.038 1.909-.038h1.9c.853 0 1.447 0 1.91.038.453.037.714.107.912.207.423.216.767.56.983.984.1.197.17.458.207.912.014.18.024.38.029.609h-9.982c.006-.228.015-.429.03-.61.036-.453.106-.714.206-.911a2.25 2.25 0 0 1 .984-.984Zm-1.229 4.005v1.2c0 .853 0 1.447.038 1.91.037.453.107.714.207.912.216.423.56.767.984.983.197.1.458.17.912.207.462.037 1.057.038 1.909.038h3.306c.385 0 .52-.001.626-.012a2.25 2.25 0 0 0 2.006-2.006c.011-.106.012-.241.012-.626v-2.606h-10Z"></path></svg>
                                                    <div class="DataRangeFilter__Primary--xAbxO">
                                                        <div class="DataRangeFilter__RangeName--Zdy5n">Last 365 days</div>
                                                        <div class="DataRangeFilter__Range--sFRWK">Jan 23, 2025 – Jan 22, 2026</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Date Picker Dropdown -->
                                            <div class="dateRangePicker__Wrapper--lMP7K" id="DatePopover">
                                                <div>
                                                    <div class="calendar__CalendarWrapper--QY1r9">
                                                        <div class="calendar__MonthAndYearWrapper--2C9qJ" style="justify-content: space-between; margin-bottom: 20px;">
                                                            <button id="btn-prev-month" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter Polaris-Button--iconOnly" type="button">
                                                                <span class="Polaris-Button__Icon"><span class="Polaris-Icon"><svg viewBox="0 0 20 20" class="Polaris-Icon__Svg"><path fill-rule="evenodd" d="M11.72 14.53a.75.75 0 0 1 0-1.06L9.06 10l2.66-3.47a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1 0 1.06l-4 4a.75.75 0 0 1-1.06-1.06l2.66-3.47 4 4a.75.75 0 0 1 .53.22Z" opacity="0"></path><path fill-rule="evenodd" d="M12.78 14.53a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 0 1 0-1.06l4-4a.75.75 0 1 1 1.06 1.06L9.31 10l3.47 3.47a.75.75 0 0 1 0 1.06Z"></path></svg></span></span>
                                                            </button>
                                                            <div style="display: flex; gap: 10px;">
                                                                <div class="">
                                                                    <select id="select-month" style="-webkit-appearance: menulist; appearance: menulist; padding: 6px 12px 6px 8px; border: 1px solid #c9cccf; border-radius: 4px; color: #202223; font-size: 14px; cursor: pointer; background: white; height: 32px;">
                                                                        <option value="0">January</option><option value="1">February</option><option value="2">March</option><option value="3">April</option>
                                                                        <option value="4">May</option><option value="5">June</option><option value="6">July</option><option value="7">August</option>
                                                                        <option value="8">September</option><option value="9">October</option><option value="10">November</option><option value="11">December</option>
                                                                    </select>
                                                                </div>
                                                                <div class="">
                                                                    <select id="select-year" style="-webkit-appearance: menulist; appearance: menulist; padding: 6px 12px 6px 8px; border: 1px solid #c9cccf; border-radius: 4px; color: #202223; font-size: 14px; cursor: pointer; background: white; height: 32px; min-width: 80px;">
                                                                        <?php 
                                                                        $currentYear = date('Y');
                                                                        for($i = $currentYear - 10; $i <= $currentYear + 10; $i++) {
                                                                            $selected = ($i == $currentYear) ? 'selected' : '';
                                                                            echo "<option value='$i' $selected>$i</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <button id="btn-next-month" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter Polaris-Button--iconOnly" type="button">
                                                                <span class="Polaris-Button__Icon"><span class="Polaris-Icon"><svg viewBox="0 0 20 20" class="Polaris-Icon__Svg"><path fill-rule="evenodd" d="M7.22 14.53a.75.75 0 0 1 0-1.06L9.94 10 7.22 6.53a.75.75 0 0 1 1.06-1.06l4 4a.75.75 0 0 1 0 1.06l-4 4a.75.75 0 0 1-1.06 0Z"></path></svg></span></span>
                                                            </button>
                                                        </div>
                                                        <div class="calendar__Months--U6sTI calendar__MonthsHorizontal--2IeDc">
                                                            <div class="month__Month--ePVbp">
                                                                <div id="cal-left-title" style="text-align: center; font-weight: 600; font-size: 14px; margin-bottom: 12px;"></div>
                                                                <div class="month__WeekDays--BOPP8">
                                                                    <span class="month__WeekDay--pz65Q">Su</span><span class="month__WeekDay--pz65Q">Mo</span><span class="month__WeekDay--pz65Q">Tu</span><span class="month__WeekDay--pz65Q">We</span><span class="month__WeekDay--pz65Q">Th</span><span class="month__WeekDay--pz65Q">Fr</span><span class="month__WeekDay--pz65Q">Sa</span>
                                                                </div>
                                                                <div class="month__Days--pEM0e" id="cal-left-grid">
                                                                    <!-- Days will be injected here -->
                                                                </div>
                                                            </div>
                                                            <div class="month__Month--ePVbp">
                                                                <div id="cal-right-title" style="text-align: center; font-weight: 600; font-size: 14px; margin-bottom: 12px;"></div>
                                                                <div class="month__WeekDays--BOPP8">
                                                                    <span class="month__WeekDay--pz65Q">Su</span><span class="month__WeekDay--pz65Q">Mo</span><span class="month__WeekDay--pz65Q">Tu</span><span class="month__WeekDay--pz65Q">We</span><span class="month__WeekDay--pz65Q">Th</span><span class="month__WeekDay--pz65Q">Fr</span><span class="month__WeekDay--pz65Q">Sa</span>
                                                                </div>
                                                                <div class="month__Days--pEM0e" id="cal-right-grid">
                                                                    <!-- Days will be injected here -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="DateRange__Times--9jN9+">
                                                            <div class="DateRange__Time--n6JBi">
                                                                <div class="Time__Time--E2Jl5">12:00 AM</div>
                                                            </div>
                                                            <div class="DateRange__Time--n6JBi">
                                                                <div class="Time__Time--E2Jl5">11:59 PM</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="dateRangePicker__RightColumn--FyEor">
                                                    <div class="staticRange__Wrapper--EIsaL" style="margin-bottom: 12px;">
                                                        <div class="staticRange__Btn--N7i+3">
                                                            <div id="select-preset" class="" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; border: 1px solid #c9cccf; border-radius: 4px; background: white; height: 32px; cursor: pointer;">
                                                                <span class="preset-label-text" style="font-size: 14px; color: #202223;">Last 30 days</span>
                                                                <svg viewBox="0 0 20 20" style="width: 16px; height: 16px; fill: #5c5f62;"><path d="M10 14a.997.997 0 0 1-.707-.293l-5-5a.999.999 0 1 1 1.414-1.414L10 11.586l4.293-4.293a.999.999 0 1 1 1.414 1.414l-5 5A.997.997 0 0 1 10 14z"></path></svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Modal Injection (Ideally at bottom of body, but putting here for context scope if needed, though absolute positioning works) -->
                                                    <div id="PresetModal" class="presetModal__Wrapper--J8k2L">
                                                        <div class="presetModal__Content--m0zP1">
                                                            <div class="presetModal__Header--V8j2e">
                                                                <div class="presetModal__Title--T7f2z">Pick date range</div>
                                                                <div class="modal__Close--XyZ12" style="cursor: pointer;">
                                                                    <svg viewBox="0 0 20 20" style="width: 20px; height: 20px; fill: #5c5f62;"><path fill-rule="evenodd" d="M15.707 4.293a1 1 0 0 1 0 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 1.414-1.414L10 8.586l4.293-4.293a1 1 0 0 1 1.414 0z"/></svg>
                                                                </div>
                                                            </div>
                                                            <div class="presetModal__Body--K2l9j">
                                                                <div class="presetCard__Item--x9-1Z" data-value="today"><div class="presetCard__Label--y8H21">Today</div><div class="presetCard__Range--k7J3g">Jan 23, 2026</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="yesterday"><div class="presetCard__Label--y8H21">Yesterday</div><div class="presetCard__Range--k7J3g">Jan 22, 2026</div></div>
                                                                <!-- Empty cards for layout matching -->
                                                                <div></div><div></div>
                                                                
                                                                <div class="presetCard__Item--x9-1Z" data-value="last7"><div class="presetCard__Label--y8H21">Last 7 days</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last30"><div class="presetCard__Label--y8H21">Last 30 days</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last90"><div class="presetCard__Label--y8H21">Last 90 days</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last365"><div class="presetCard__Label--y8H21">Last 365 days</div></div>
                                                                
                                                                <div class="presetCard__Item--x9-1Z" data-value="last_week"><div class="presetCard__Label--y8H21">Last week</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last_month"><div class="presetCard__Label--y8H21">Last month</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last_quarter"><div class="presetCard__Label--y8H21">Last quarter</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="last_year"><div class="presetCard__Label--y8H21">Last year</div></div>
                                                                
                                                                <div class="presetCard__Item--x9-1Z" data-value="wtd"><div class="presetCard__Label--y8H21">Week to date</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="mtd"><div class="presetCard__Label--y8H21">Month to date</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="qtd"><div class="presetCard__Label--y8H21">Quarter to date</div></div>
                                                                <div class="presetCard__Item--x9-1Z" data-value="ytd"><div class="presetCard__Label--y8H21">Year to date</div></div>
                                                                
                                                                <div class="presetCard__Item--x9-1Z" data-value="all_time"><div class="presetCard__Label--y8H21">All Time</div></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="dateInputs__Wrapper--OHckw" style="margin-bottom:16px;">
                                                        <span class="dateInputs__Item--cZTCB dateInputs__ItemActive--SCfoG" style="flex: 1;">
                                                            <div class="Polaris-TextField Polaris-TextField--hasValue">
                                                                <input id="input-start-date" class="Polaris-TextField__Input" type="text" value="" style="text-align: center;">
                                                                <div class="Polaris-TextField__Backdrop"></div>
                                                            </div>
                                                        </span>
                                                        <span style="margin:0 4px">–</span>
                                                        <span class="dateInputs__Item--cZTCB" style="flex: 1;">
                                                            <div class="Polaris-TextField Polaris-TextField--hasValue">
                                                                <input id="input-end-date" class="Polaris-TextField__Input" type="text" value="" style="text-align: center;">
                                                                <div class="Polaris-TextField__Backdrop"></div>
                                                            </div>
                                                        </span>
                                                    </div>

                                                    <div style="margin-top:12px; display: flex; align-items: center; justify-content: space-between;">
                                                        <label class="Polaris-Choice Polaris-Checkbox__ChoiceLabel" style="margin: 0; display: flex; align-items: center;">
                                                            <span class="Polaris-Choice__Control">
                                                                <span class="Polaris-Checkbox">
                                                                    <input id="check-compare" type="checkbox" class="Polaris-Checkbox__Input" aria-invalid="false" role="checkbox" aria-checked="false" value="">
                                                                    <span class="Polaris-Checkbox__Backdrop"></span>
                                                                    <span class="Polaris-Checkbox__Icon"><svg viewBox="0 0 20 20" style="width: 12px; height: 12px; fill: currentColor;"><path fill-rule="evenodd" d="M15.03 5.53a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 0 1-1.06 0L4.47 9.59a.75.75 0 0 1 1.06-1.06l2.72 2.72 5.72-5.72a.75.75 0 0 1 1.06 0Z"></path></svg></span>
                                                                </span>
                                                            </span>
                                                            <span class="Polaris-Choice__Label"><span class="Polaris-Text--root Polaris-Text--bodyMd">Compare</span></span>
                                                        </label>

                                                        <div class="compareMode__Wrapper--j8Lka">
                                                            <div id="compare-mode-trigger" class="" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 12px 4px 8px; border: 1px solid #c9cccf; border-radius: 4px; background: white; height: 28px; cursor: pointer; min-width: 140px;">
                                                                <span id="compare-mode-text" style="font-size: 13px; color: #202223;">Previous period</span>
                                                                <svg viewBox="0 0 20 20" style="width: 16px; height: 16px; fill: #5c5f62;"><path d="M10 14a.997.997 0 0 1-.707-.293l-5-5a.999.999 0 1 1 1.414-1.414L10 11.586l4.293-4.293a.999.999 0 1 1 1.414 1.414l-5 5A.997.997 0 0 1 10 14z"></path></svg>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="dateInputs__Wrapper--OHckw" style="margin-top:6px; opacity: 0.5;">
                                                        <span class="dateInputs__Item--cZTCB">
                                                            <div class="Polaris-TextField"><input id="input-compare-start" class="Polaris-TextField__Input" type="text" readonly></div>
                                                        </span>
                                                        <span style="margin:0 4px">–</span>
                                                        <span class="dateInputs__Item--cZTCB">
                                                            <div class="Polaris-TextField"><input id="input-compare-end" class="Polaris-TextField__Input" type="text" readonly></div>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="dateRangePicker__Actions--0E+hP">
                                                        <button class="Polaris-Button Polaris-Button--pressable rp-btn rp-btn-secondary" type="button" onclick="toggleDatePopover()">
                                                            <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Cancel</span>
                                                        </button>
                                                        <button class="Polaris-Button Polaris-Button--pressable rp-btn rp-btn-primary" type="button" onclick="applyDateRange()">
                                                            <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Apply</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button id="Look:AddFilter" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeSlim Polaris-Button--textAlignCenter Polaris-Button--iconOnly" type="button">
                            <span class="Polaris-Button__Icon">
                                <span class="Polaris-Icon">
                                    <svg fill="#000000" height="20" viewBox="0 0 24 24" width="20"><path d="M15 17H18V14H20V17H23V19H20V22H18V19H15V17M13 19.88C13.04 20.18 12.94 20.5 12.72 20.71C12.32 21.1 11.69 21.1 11.3 20.71L7.29 16.7C7.06 16.47 6.96 16.16 7 15.87V10.75L2.21 4.62C1.87 4.19 1.95 3.56 2.38 3.22C2.57 3.08 2.78 3 3 3V3H17V3C17.22 3 17.43 3.08 17.62 3.22C18.05 3.56 18.13 4.19 17.79 4.62L13 10.75V19.88M5.04 5L9 10.07V15.58L11 17.58V10.05L14.96 5H5.04Z"></path></svg>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="control-actions" style="margin-left: auto;">
                 <!-- Replaced Control Actions -->
                 <div class="ml-auto flex items-center gap-2 md:ml-0" style="width:100%; justify-content: flex-end;">
                     <!-- Search placeholder/left side -->
                     <button id="TableSearch" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter Polaris-Button--iconOnly" type="button">
                        <span class="Polaris-Button__Icon">
                            <span class="Polaris-Icon">
                                <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path fill-rule="evenodd" d="M12.323 13.383a5.5 5.5 0 1 1 1.06-1.06l2.897 2.897a.75.75 0 1 1-1.06 1.06l-2.897-2.897Zm.677-4.383a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"></path></svg>
                            </span>
                        </span>
                    </button>
                    
                    <button class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantSecondary Polaris-Button--sizeSlim Polaris-Button--textAlignCenter Polaris-Button--iconWithText" type="button" aria-pressed="false">
                        <span class="Polaris-Button__Icon">
                            <span class="Polaris-Icon">
                                <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path fill-rule="evenodd" d="M9.095 6.25a3.001 3.001 0 0 1 5.81 0h1.345a.75.75 0 0 1 0 1.5h-1.345a3.001 3.001 0 0 1-5.81 0h-5.345a.75.75 0 0 1 0-1.5h5.345Zm1.405.75a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z"></path><path fill-rule="evenodd" d="M8 16a3.001 3.001 0 0 0 2.905-2.25h5.345a.75.75 0 0 0 0-1.5h-5.345a3.001 3.001 0 0 0-5.81 0h-1.345a.75.75 0 0 0 0 1.5h1.345a3.001 3.001 0 0 0 2.905 2.25Zm1.5-3a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path></svg>
                            </span>
                        </span>
                        <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Edit</span>
                    </button>
                    
                    <div>
                        <button id="LookToolbarFilters" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantSecondary Polaris-Button--sizeSlim Polaris-Button--textAlignCenter Polaris-Button--iconWithText" type="button" aria-pressed="false">
                            <span class="Polaris-Button__Icon">
                                <span class="Polaris-Icon">
                                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path d="M3 6a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5h-12.5a.75.75 0 0 1-.75-.75Z"></path><path d="M6.75 14a.75.75 0 0 1 .75-.75h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75Z"></path><path d="M5.5 9.25a.75.75 0 0 0 0 1.5h9a.75.75 0 0 0 0-1.5h-9Z"></path></svg>
                                </span>
                            </span>
                            <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Filters</span>
                        </button>
                    </div>
                    
                    <div>
                        <button id="LookQuickSorting" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantSecondary Polaris-Button--sizeSlim Polaris-Button--textAlignCenter Polaris-Button--iconWithText" type="button">
                            <span class="Polaris-Button__Icon">
                                <span class="Polaris-Icon">
                                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path d="M7.75 6.06v7.69a.75.75 0 0 1-1.5 0v-7.69l-1.72 1.72a.75.75 0 0 1-1.06-1.06l3-3a.75.75 0 0 1 1.06 0l3 3a.75.75 0 1 1-1.06 1.06l-1.72-1.72Z"></path><path d="M13.75 6.25a.75.75 0 0 0-1.5 0v7.69l-1.72-1.72a.75.75 0 1 0-1.06 1.06l3 3a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72v-7.69Z"></path></svg>
                                </span>
                            </span>
                            <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Sort</span>
                        </button>
                    </div>

                     <!-- Right side view toggles -->
                     <div class="ml-4">
                        <div class="DisplayMode__Wrapper--WYA24" id="DisplayModeVisualization">
                            <span>
                                <button class="DisplayMode__Button--Hf7-Y DisplayMode__Active--OZw0I" id="DisplayMode:Table" type="button">
                                    <span class="Polaris-Icon">
                                        <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path fill-rule="evenodd" d="M5.5 16.5a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-9Zm3.25-11.5h2.5v2h-2.5v-2Zm-1.5 0h-1.75a.5.5 0 0 0-.5.5v1.5h2.25v-2Zm0 3.5h-2.25v6a.5.5 0 0 0 .5.5h1.75v-6.5Zm1.5 6.5v-6.5h2.5v6.5h-2.5Zm4-8v-2h1.75a.5.5 0 0 1 .5.5v1.5h-2.25Zm0 1.5v6.5h1.75a.5.5 0 0 0 .5-.5v-6h-2.25Z"></path></svg>
                                    </span>
                                </button>
                            </span>
                            <span>
                                <button class="DisplayMode__Button--Hf7-Y" type="button">
                                    <span class="Polaris-Icon">
                                        <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path fill-rule="evenodd" d="M9.952 3.5h.096c.182 0 .371 0 .543.034a1.75 1.75 0 0 1 1.375 1.375c.035.172.034.361.034.543v9.096c0 .182 0 .371-.034.543a1.75 1.75 0 0 1-1.375 1.376 2.825 2.825 0 0 1-.543.033h-.096c-.182 0-.371 0-.543-.034a1.75 1.75 0 0 1-1.375-1.375 2.824 2.824 0 0 1-.034-.543v-9.096c0-.182 0-.371.034-.543a1.75 1.75 0 0 1 1.375-1.375c.172-.035.361-.034.543-.034Zm-.253 1.505a.25.25 0 0 0-.194.194 8.217 8.217 0 0 0-.005.3v9.001a8.221 8.221 0 0 0 .005.3.25.25 0 0 0 .194.195l.053.003c.055.002.127.002.248.002s.193 0 .248-.002l.053-.003a.25.25 0 0 0 .194-.194l.003-.053a8.05 8.05 0 0 0 .002-.248v-9a8.046 8.046 0 0 0-.005-.3.25.25 0 0 0-.194-.195 8.217 8.217 0 0 0-.3-.005 8.216 8.216 0 0 0-.302.005Z"></path><path fill-rule="evenodd" d="M5.452 9h.096c.182 0 .371 0 .543.034a1.75 1.75 0 0 1 1.375 1.375c.035.172.034.361.034.543v3.596c0 .182 0 .371-.034.543a1.75 1.75 0 0 1-1.375 1.376 2.825 2.825 0 0 1-.543.033h-.096c-.182 0-.371 0-.543-.034a1.75 1.75 0 0 1-1.375-1.375 2.824 2.824 0 0 1-.034-.543v-3.596c0-.182 0-.371.034-.543a1.75 1.75 0 0 1 1.375-1.375c.172-.035.361-.034.543-.034Zm-.253 1.505a.25.25 0 0 0-.194.194 8.217 8.217 0 0 0-.005.3v3.501a8.221 8.221 0 0 0 .005.3.25.25 0 0 0 .194.195l.053.003c.055.002.127.002.248.002s.193 0 .248-.002l.053-.003a.25.25 0 0 0 .194-.194l.003-.053c.002-.054.002-.127.002-.248v-3.5a8.208 8.208 0 0 0-.005-.3.25.25 0 0 0-.194-.195 8.045 8.045 0 0 0-.3-.005 8.045 8.045 0 0 0-.302.005Z"></path><path fill-rule="evenodd" d="M14.5 6h-.048c-.182 0-.371 0-.543.034a1.75 1.75 0 0 0-1.375 1.375 2.825 2.825 0 0 0-.034.543v6.596c0 .182 0 .371.034.543a1.75 1.75 0 0 0 1.375 1.376c.172.034.361.033.543.033h.096c.182 0 .371 0 .543-.034a1.75 1.75 0 0 0 1.375-1.375c.035-.172.034-.36.034-.543v-6.596c0-.182 0-.371-.034-.543a1.75 1.75 0 0 0-1.375-1.375c-.172-.035-.361-.034-.543-.034h-.048Zm-.495 1.7a.25.25 0 0 1 .194-.195 8.221 8.221 0 0 1 .3-.005 8.217 8.217 0 0 1 .302.005.25.25 0 0 1 .194.194l.003.053c.002.055.002.127.002.248v6.5a8.05 8.05 0 0 1-.005.3.25.25 0 0 1-.194.195l-.053.003a8.046 8.046 0 0 1-.248.002c-.121 0-.193 0-.248-.002l-.053-.003a.25.25 0 0 1-.194-.194l-.003-.053a8.05 8.05 0 0 1-.002-.248v-6.5c0-.121 0-.193.002-.248l.003-.053v.002-.002Z"></path></svg>
                                </span>
                            </button>
                        </span>
                    </div>
                 </div>
            </div>
        </div>
        </div>

        <!-- Data Table -->
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr id="table-headers">
                        <!-- Loaded dynamically -->
                    </tr>
                </thead>
                <tbody id="table-body">
                    <tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">Loading data...</td></tr>
                </tbody>
            </table>
            <div class="footer-count" id="record-count"></div>
        </div>
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
        min-width: 205px;
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

    /* New Control Bar Styles from User Snippet */
    .ml-auto { margin-left: auto; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .gap-2 { gap: 0.5rem; }
    .ml-4 { margin-left: 1rem; }
    
    

    .Polaris-Button--variantTertiary {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 6px;
        color: #5c5f62;
        border-radius: 4px;
    }
    .Polaris-Button--variantTertiary:hover {
        background: #f1f2f3;
    }

    .Polaris-Button__Icon {
        display: flex;
        align-items: center;
    }
    .Polaris-Icon svg {
        width: 20px;
        height: 20px;
        fill: rgb(92 95 98);
    }

    /* Display Mode Toggles (Segmented Control) */
    .DisplayMode__Wrapper--WYA24 {
        display: flex;
        background: #f1f2f3;
        border-radius: 8px;
        padding: 2px;
        gap: 2px;
    }
    .DisplayMode__Button--Hf7-Y {
        border: none;
        background: transparent;
        border-radius: 6px;
        padding: 4px 8px;
        cursor: pointer;
        color: #5c5f62;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .DisplayMode__Button--Hf7-Y:hover {
        background: rgba(255,255,255,0.5);
    }
    .DisplayMode__Active--OZw0I {
        background: white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        color: #202223;
    }
    .DisplayMode__Button--Hf7-Y svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    /* Date Range Filter Toolbar Styles */
    .Look__Toolbar--xFZP\+ {
        display: flex;
        align-items: center;
    }
    
    .DataRangeFilter__Button--A\+vc1, #PrimaryFilter {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #c9cccf;
        border-radius: 8px;
        padding: 4px 8px;
        cursor: pointer;
        height: 32px;
        box-sizing: border-box;
        position: relative;
        z-index: 10;
        user-select: none;
    }
    .DataRangeFilter__Button--A\+vc1:hover, #PrimaryFilter:hover {
        background: #f6f6f7;
    }
    .DataRangeFilter__Content--Q7tLy {
        display: flex;
        align-items: center;
        gap: 8px;
        pointer-events: none; /* Let clicks pass to the button */
    }
    .DataRangeFilter__Content--Q7tLy svg {
        width: 16px;
        height: 16px;
        color: #5c5f62;
    }
    
    .DataRangeFilter__Primary--xAbxO {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #202223;
    }
    
    .DataRangeFilter__RangeName--Zdy5n {
        background: #e4e5e7;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 12px;
    }
    
    .DataRangeFilter__Range--sFRWK {
        font-weight: 400;
    }
    
    /* Filter Pill Styles */
    .FilterPill__Filter--h7eN3 {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #c9cccf;
        border-radius: 8px;
        padding: 4px 8px 4px 12px;
        height: 32px;
        box-sizing: border-box;
        gap: 4px;
        margin-left: 8px;
        cursor: pointer;
    }
    .FilterPill__Filter--h7eN3:hover {
        background: #f6f6f7;
    }
    
    .FilterPill__Content--js97k {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
    }
    
    .Polaris-Button--sizeMicro {
        padding: 0;
        height: 20px;
        width: 20px;
        min-height: 20px;
        min-width: 20px;
    }
    
    .Polaris-Text--subdued {
        color: #6d7175;
    }
    
    /* Global flex utilities used in snippet */
    .flex { display: flex; }
    .items-center { align-items: center; }
    .gap-2 { gap: 0.5rem; }
    .gap-4 { gap: 1rem; }
    .flex-nowrap { flex-wrap: nowrap; }
    .mr-auto { margin-right: auto; }
    .overflow-hidden { overflow: hidden; }
    /* Date Range Picker Popover Styles */
    .dateRangePicker__Wrapper--lMP7K {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        background: white;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 16px;
        margin-top: 8px;
        width: max-content;
    }
    
    .dateRangePicker__Wrapper--lMP7K.show {
        display: flex;
        gap: 20px;
    }
    
    /* Internal Popover Layout */
    .calendar__CalendarWrapper--QY1r9 {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .calendar__MonthAndYearWrapper--2C9qJ {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .calendar__MonthsHorizontal--2IeDc {
        display: flex;
        gap: 20px;
    }
    
    .month__WeekDays--BOPP8 {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        margin-bottom: 8px;
    }
    
    .month__Days--pEM0e {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
    }
    
    .calendar__MonthPicker--tESMn select,
    .calendar__YearPicker--BDZ1N select {
        padding: 4px 8px;
        border: 1px solid #c9cccf;
        border-radius: 4px;
    }
    
    /* Day Cells */
    .dayCell__Day--0NGiF {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 12px;
        border-radius: 4px;
    }
    .dayCell__Day--0NGiF:hover {
        background-color: #f1f2f3;
    }
    .dayCell__DayInRange--l97gF {
        background-color: #f1f2f3;
    }
    .dayCell__DayIsStartRange--p80p4, 
    .dayCell__DayIsEndRange--EGp-Q {
        background-color: #202223;
        color: white;
    }
    .dayCell__DayPassive--rQM2t {
        color: #babec3;
    }
    
    /* Right Column (Preset Ranges) */
    .dateRangePicker__RightColumn--FyEor {
        display: flex;
        flex-direction: column;
        gap: 12px;
        border-left: 1px solid #e1e3e5;
        padding-left: 20px;
        min-width: 200px;
    }
    
    .DateRange__Times--9jN9\+ {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-top: 1px solid #e1e3e5;
        margin-top: 10px;
    }
    
    .dateInputs__Wrapper--OHckw {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .Polaris-TextField__Input {
        width: 100%;
        padding: 6px;
        border: 1px solid #c9cccf;
        border-radius: 4px;
    }
    
    .dateRangePicker__Actions--0E\+hP {
        display: flex;
        justify-content: flex-end; /* Align buttons to right */
        gap: 8px;
        margin-top: auto;
        padding-top: 16px;
    }
    
    /* Wrapper for relative positioning */
    .PrimaryFilters__PrimaryFilter--32fOO {
        position: relative;
    }
    /* Time Selector Fix */
    .Time__Time--E2Jl5 {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
        color: #5c5f62;
        padding-top: 8px;
    }
    .DateRange__Time--n6JBi {
        flex: 1;
        text-align: center;
    }
    .Time__Separator--lMBju {
        margin: 0 4px;
    }
    
    /* Layout Adjustments */
    .calendar__MonthsHorizontal--2IeDc {
        gap: 16px;
    }
    .dayCell__Day--0NGiF {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }
    
    /* Checkbox Styling */
    .Polaris-Checkbox {
        position: relative;
        display: inline-block;
        width: 16px;
        height: 16px;
        vertical-align: middle;
        margin-right: 8px;
        box-sizing: border-box;
    }
    .Polaris-Checkbox__Input {
        position: absolute;
        top: 0;
        left: 0;
        width: 16px !important;
        height: 16px !important;
        opacity: 0;
        z-index: 2;
        cursor: pointer;
        padding: 0;
        margin: 0;
    }
    .Polaris-Checkbox__Backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        border: 1px solid #babfc3; /* Standard Polaris border color */
        border-radius: 4px;
        pointer-events: none;
        box-sizing: border-box;
        transition: border-color 0.2s cubic-bezier(0.64, 0, 0.35, 1);
    }
    .Polaris-Checkbox__Input {
        background: #202223 !important;
        border-color: #202223 !important;
    }
    .Polaris-Checkbox__Icon {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        opacity: 0;
        color: white; /* Ensures SVG uses this color */
    }
    .Polaris-Checkbox__Input:checked ~ .Polaris-Checkbox__Backdrop ~ .Polaris-Checkbox__Icon {
        opacity: 1;
    }
    
    /* Apply Button Styling */
   
    
    /* Previous Period Button */
    .Polaris-Button--disclosure {
        justify-content: space-between;
    }
    
    /* Selected Range Highlight */
    .dayCell__DayIsStartRange--p80p4, .dayCell__DayIsEndRange--EGp-Q {
        background: #202223;
        border-radius: 4px;
    }
    .dayCell__DayInRange--l97gF {
        background: #f1f2f3;
        border-radius: 0; 
    }
    /* Round corners for start/end of range if they are middle of week */
    .dayCell__DayIsStartRange--p80p4 { border-top-right-radius: 0; border-bottom-right-radius: 0; border-radius: 4px 0 0 4px; }
    .dayCell__DayIsEndRange--EGp-Q { border-top-left-radius: 0; border-bottom-left-radius: 0; border-radius: 0 4px 4px 0; }
    
    
    /* Popover Layout Fixes */
    .dateRangePicker__RightColumn--FyEor {
        min-width: 280px;
        border-left: 1px solid #e1e3e5;
        padding-left: 20px;
    }
    
    .Polaris-Choice {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        margin-top: 12px;
    }
    .active-filter-pill {
        background: #f1f2f3;
    }
    
    /* Preset Modal Styles */
    .presetModal__Wrapper--J8k2L {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    .presetModal__Wrapper--J8k2L.show {
        display: flex;
    }
    .presetModal__Content--m0zP1 {
        background: white;
        border-radius: 8px;
        width: 800px;
        max-width: 90vw;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .presetModal__Header--V8j2e {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e1e3e5;
    }
    .presetModal__Title--T7f2z {
        font-weight: 600;
        font-size: 16px;
    }
    .presetModal__Body--K2l9j {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .presetCard__Item--x9-1Z {
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        padding: 12px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .presetCard__Item--x9-1Z:hover {
        border-color: #5c5f62;
    }
    .presetCard__Label--y8H21 {
        font-weight: 600;
        font-size: 13px;
        color: #202223;
        margin-bottom: 4px;
    }
    .presetCard__Range--k7J3g {
        font-size: 12px;
        color: #6d7175;
    }
    /* Compare Modal Specific */
    #ComparePresetModal .presetCard__Item--x9-1Z {
        /* Optional overrides */
    }
    
    /* More Actions Dropdown */
    .more-actions-menu {
        width: fit-content;
        border-radius: 12px;
        padding: 8px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: 1px solid #e1e3e5;
        top: calc(100% + 8px);
        left: 0;
        right: auto;
    }
    .more-action-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        font-size: 13px;
        color: #202223;
        cursor: pointer;
        transition: background-color 0.1s;
        gap: 12px;
        font-weight: 500;
    }
    .more-action-item:hover {
        background-color: #f1f2f3;
    }
    .more-action-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        color: #5c5f62;
    }
    .more-action-icon svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
    }
    .more-action-item.text-critical {
        color: #d82c0d;
    }
    .more-action-item.text-critical .more-action-icon {
        color: #d82c0d;
    }
</style>

<!-- Compare Mode Trigger Replacement (Search target by content, waiting for injection) -->
<!-- Modal Injection -->
<div id="ComparePresetModal" class="presetModal__Wrapper--J8k2L">
    <div class="presetModal__Content--m0zP1" style="width: 800px;">
        <div class="presetModal__Header--V8j2e">
            <div class="presetModal__Title--T7f2z">Pick date range</div>
            <div class="modal__Close--XyZ12" style="cursor: pointer;">
                <svg viewBox="0 0 20 20" style="width: 20px; height: 20px; fill: #5c5f62;"><path fill-rule="evenodd" d="M15.707 4.293a1 1 0 0 1 0 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 1.414-1.414L10 8.586l4.293-4.293a1 1 0 0 1 1.414 0z"/></svg>
            </div>
        </div>
        <div class="presetModal__Body--K2l9j">
            <div class="presetCard__Item--x9-1Z" data-value="previous_period">
                <div class="presetCard__Label--y8H21">Previous period</div>
                <div class="presetCard__Range--k7J3g" id="cp-previous_period">--</div>
            </div>
            <div class="presetCard__Item--x9-1Z" data-value="previous_week">
                <div class="presetCard__Label--y8H21">Previous week</div>
                <div class="presetCard__Range--k7J3g" id="cp-previous_week">--</div>
            </div>
            <div class="presetCard__Item--x9-1Z" data-value="previous_month">
                <div class="presetCard__Label--y8H21">Previous month</div>
                <div class="presetCard__Range--k7J3g" id="cp-previous_month">--</div>
            </div>
            <div class="presetCard__Item--x9-1Z" data-value="previous_year">
                <div class="presetCard__Label--y8H21">Previous year</div>
                <div class="presetCard__Range--k7J3g" id="cp-previous_year">--</div>
            </div>
        </div>
    </div>
</div>

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
    let activeReportColumns = <?php 
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

    // Toggle Favorite Function for Detail Page
    function toggleReportFavorite(btn, id) {
        const svg = btn.querySelector('svg');
        const isActive = btn.classList.contains('active');
        
        // Optimistic UI update
        if (isActive) {
            btn.classList.remove('active');
            svg.style.fill = 'rgb(74, 74, 74)';
            svg.style.color = 'rgb(74, 74, 74)';
        } else {
            btn.classList.add('active');
            svg.style.fill = '#FFB100';
            svg.style.color = '#FFB100';
        }

        const authParams = window.location.search || '';
        fetch(`${baseUrl}/reports/toggle-favorite${authParams}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                // Revert if failed
                if (isActive) {
                    btn.classList.add('active');
                    svg.style.fill = '#FFB100';
                    svg.style.color = '#FFB100';
                } else {
                    btn.classList.remove('active');
                    svg.style.fill = 'rgb(74, 74, 74)';
                    svg.style.color = 'rgb(74, 74, 74)';
                }
                alert('Failed to update favorite: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
             // Revert if failed
             if (isActive) {
                btn.classList.add('active');
                svg.style.fill = '#FFB100';
                svg.style.color = '#FFB100';
            } else {
                btn.classList.remove('active');
                svg.style.fill = 'rgb(74, 74, 74)';
                svg.style.color = 'rgb(74, 74, 74)';
            }
        });
    }

    // Close when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.btn') && !event.target.matches('.dropdown-trigger') && !event.target.closest('.dropdown-trigger') && !event.target.closest('#DatePopover')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
            // Close datepicker if open
            const datePop = document.getElementById('DatePopover');
            if(datePop && datePop.classList.contains('show')) {
                datePop.classList.remove('show');
            }
        }
    }

    // --- Date Picker Logic ---
    let datePickerInstance = null;

    class DateRangePicker {
        constructor() {
            this.startDate = new Date();
            this.startDate.setDate(new Date().getDate() - 30); // Default to last 30 days
            this.endDate = new Date();
            this.currentLabel = "Last 30 days"; 
            this.compareMode = "previous_period";
            // Initial view: Left grid = EndDate Month - 1
            this.viewDate = new Date(this.endDate.getFullYear(), this.endDate.getMonth() - 1, 1); 
            this.init();
        }

        init() {
            this.cacheDOM();
            if(this.els.popover) {
                // this.populateYears(); // Handled by PHP
                this.render();
                this.bindEvents();
                this.updateInputs();
                this.updateMainButton();
                this.updateCompareInputs();
            }
        }

        cacheDOM() {
            this.els = {
                popover: document.getElementById('DatePopover'),
                trigger: document.getElementById('PrimaryFilter'),
                leftTitle: document.getElementById('cal-left-title'),
                rightTitle: document.getElementById('cal-right-title'),
                leftGrid: document.getElementById('cal-left-grid'),
                rightGrid: document.getElementById('cal-right-grid'),
                inputStart: document.getElementById('input-start-date'),
                inputEnd: document.getElementById('input-end-date'),
                btnPrev: document.getElementById('btn-prev-month'),
                btnNext: document.getElementById('btn-next-month'),
                selectMonth: document.getElementById('select-month'),
                selectYear: document.getElementById('select-year'),
                selectPreset: document.getElementById('select-preset'), 
                selectCompareMode: document.getElementById('select-compare-mode'), // Legacy? Removing from usage but keeping mapping just in case
                compareModeTrigger: document.getElementById('compare-mode-trigger'),
                compareModeText: document.getElementById('compare-mode-text'),
                presetModal: document.getElementById('PresetModal'), 
                comparePresetModal: document.getElementById('ComparePresetModal'),
                inputCompareStart: document.getElementById('input-compare-start'),
                inputCompareEnd: document.getElementById('input-compare-end'),
                checkCompare: document.getElementById('check-compare'),
            };
        }

        bindEvents() {
            // Navigation
            if(this.els.btnPrev) this.els.btnPrev.addEventListener('click', (e) => { e.stopPropagation(); this.changeMonth(-1); });
            if(this.els.btnNext) this.els.btnNext.addEventListener('click', (e) => { e.stopPropagation(); this.changeMonth(1); });

            // Preset Trigger
            if(this.els.selectPreset) {
                this.els.selectPreset.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.togglePresetModal();
                });
            }
            
            // Compare Trigger
            if(this.els.compareModeTrigger) {
                this.els.compareModeTrigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleCompareModal();
                });
            }

            // Presets Modal
            if(this.els.presetModal) {
                 this.els.presetModal.addEventListener('click', (e) => {
                     if(e.target === this.els.presetModal) this.togglePresetModal();
                 });
                 const cards = this.els.presetModal.querySelectorAll('.presetCard__Item--x9-1Z');
                 cards.forEach(card => {
                     card.addEventListener('click', (e) => {
                         e.stopPropagation();
                         this.handlePresetChange(card.dataset.value);
                         this.togglePresetModal();
                     });
                 });
                 const closeBtn = this.els.presetModal.querySelector('.modal__Close--XyZ12');
                 if(closeBtn) closeBtn.addEventListener('click', (e) => { e.stopPropagation(); this.togglePresetModal(); });
            }

            // Compare Presets Modal
            if(this.els.comparePresetModal) {
                 this.els.comparePresetModal.addEventListener('click', (e) => {
                     if(e.target === this.els.comparePresetModal) this.toggleCompareModal();
                 });
                 const cards = this.els.comparePresetModal.querySelectorAll('.presetCard__Item--x9-1Z');
                 cards.forEach(card => {
                     card.addEventListener('click', (e) => {
                         e.stopPropagation();
                         this.handleComparePresetChange(card.dataset.value);
                         this.toggleCompareModal();
                     });
                 });
                 const closeBtn = this.els.comparePresetModal.querySelector('.modal__Close--XyZ12');
                 if(closeBtn) closeBtn.addEventListener('click', (e) => { e.stopPropagation(); this.toggleCompareModal(); });
            }

            // Compare Checkbox
            if(this.els.checkCompare) {
                this.els.checkCompare.addEventListener('change', (e) => {
                    this.toggleCompare(e.target.checked);
                });
            }
            
            // Month/Year Selectors
            if(this.els.selectMonth) {
                this.els.selectMonth.addEventListener('change', (e) => {
                    const val = parseInt(e.target.value);
                    this.viewDate = new Date(this.viewDate.getFullYear(), val, 1);
                    this.render();
                });
            }
            if(this.els.selectYear) {
               this.els.selectYear.addEventListener('change', (e) => {
                    const val = parseInt(e.target.value);
                    this.viewDate = new Date(val, this.viewDate.getMonth(), 1);
                    this.render();
                });
            }
        }
        
        togglePresetModal() {
            if(this.els.presetModal) {
                this.els.presetModal.classList.toggle('show');
            }
        }

        toggleCompareModal() {
            if(this.els.comparePresetModal) {
                if(!this.els.comparePresetModal.classList.contains('show')) {
                    // Update texts before showing
                    const modes = ['previous_period', 'previous_week', 'previous_month', 'previous_year'];
                    modes.forEach(m => {
                        const range = this.calculateCompareRange(m);
                        const el = document.getElementById('cp-' + m);
                        if(el && range.start && range.end) {
                            const s = range.start.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            const e = range.end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            el.textContent = (range.start.getFullYear() === range.end.getFullYear()) 
                                ? `${s.split(',')[0]} – ${e}` 
                                : `${s} – ${e}`; // simplified formatting
                        }
                    });
                }
                this.els.comparePresetModal.classList.toggle('show');
            }
        }

        handleComparePresetChange(value) {
            this.compareMode = value;
            // Update trigger text
            let label = "Previous period";
            if(value === 'previous_year') label = "Previous year";
            if(value === 'previous_month') label = "Previous month";
            if(value === 'previous_week') label = "Previous week";
            
            if(this.els.compareModeText) this.els.compareModeText.textContent = label;
            
            this.updateCompareInputs();
        }

        calculateCompareRange(mode) {
             if (!this.startDate || !this.endDate) return { start: null, end: null };
             
             let pStart = new Date(this.startDate);
             let pEnd = new Date(this.endDate);
             
             // Reset time
             pStart.setHours(0,0,0,0);
             pEnd.setHours(0,0,0,0);

             if (mode === 'previous_period') {
                 const diffTime = Math.abs(this.endDate - this.startDate);
                 const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                 // End is 1 day before start
                 const newEnd = new Date(this.startDate);
                 newEnd.setDate(newEnd.getDate() - 1);
                 pEnd = newEnd;
                 
                 const newStart = new Date(pEnd);
                 newStart.setDate(newStart.getDate() - diffDays);
                 pStart = newStart;
             } else if (mode === 'previous_year') {
                 pStart.setFullYear(pStart.getFullYear() - 1);
                 pEnd.setFullYear(pEnd.getFullYear() - 1);
             } else if (mode === 'previous_month') {
                 pStart.setMonth(pStart.getMonth() - 1);
                 pEnd.setMonth(pEnd.getMonth() - 1);
             } else if (mode === 'previous_week') {
                 pStart.setDate(pStart.getDate() - 7);
                 pEnd.setDate(pEnd.getDate() - 7);
             }
             return { start: pStart, end: pEnd };
        }
        
        handlePresetChange(value) {
            const today = new Date();
            let start = new Date();
            let end = new Date();
            let label = "Custom";
            
            switch(value) {
                case 'today':
                    label = "Today";
                    break;
                case 'yesterday':
                    start.setDate(today.getDate() - 1);
                    end.setDate(today.getDate() - 1);
                    label = "Yesterday";
                    break;
                case 'last7':
                    start.setDate(today.getDate() - 7);
                    // end is today
                    label = "Last 7 days";
                    break;
                case 'last30':
                    start.setDate(today.getDate() - 30);
                    // end is today
                    label = "Last 30 days";
                    break;
                case 'last90':
                    start.setDate(today.getDate() - 90);
                    label = "Last 90 days";
                    break;
                case 'last365':
                    start.setDate(today.getDate() - 365);
                    label = "Last 365 days";
                    break;
                case 'last_week':
                    // Last Week: Monday to Sunday of the previous week
                    // Logic: Find current day, go back to previous Monday-Sunday block
                    // Adjust to: Previous week (Monday - Sunday)
                    {
                        const day = today.getDay(); // 0 (Sun) - 6 (Sat)
                        const diffToMon = day === 0 ? 6 : day - 1; // Days to subtract to get to *this* Monday
                        
                        // This Monday
                        const thisMon = new Date(today);
                        thisMon.setDate(today.getDate() - diffToMon);
                        
                        // Last Monday
                        start = new Date(thisMon);
                        start.setDate(thisMon.getDate() - 7);
                        
                        // Last Sunday
                        end = new Date(thisMon);
                        end.setDate(thisMon.getDate() - 1);
                        
                        label = "Last week";
                    }
                    break;
                case 'last_month': 
                    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    end = new Date(today.getFullYear(), today.getMonth(), 0);
                    label = "Last month";
                    break; 
                case 'last_quarter':
                    {
                        const currMonth = today.getMonth();
                        const currQuarter = Math.floor(currMonth / 3); // 0, 1, 2, 3
                        const prevQuarter = currQuarter - 1;
                        
                        let year = today.getFullYear();
                        let qStartMonth = prevQuarter * 3;
                        
                        if (prevQuarter < 0) {
                            year--;
                            qStartMonth = 9; // Q4 of prev year
                        }
                        
                        start = new Date(year, qStartMonth, 1);
                        end = new Date(year, qStartMonth + 3, 0);
                        label = "Last quarter";
                    }
                    break;
                case 'last_year':
                    start = new Date(today.getFullYear() - 1, 0, 1);
                    end = new Date(today.getFullYear() - 1, 11, 31);
                    label = "Last year";
                    break;
                case 'wtd': // Week to date (Mon to Today)
                    {
                        const day = today.getDay(); // 0 (Sun) - 6 (Sat)
                        const diffToMon = day === 0 ? 6 : day - 1; 
                        start = new Date(today);
                        start.setDate(today.getDate() - diffToMon);
                        end = today;
                        label = "Week to date";
                    }
                    break;
                case 'mtd': // Month to date
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = today;
                    label = "Month to date";
                    break;
                case 'qtd': // Quarter to date
                    {
                        const currMonth = today.getMonth();
                        const currQuarter = Math.floor(currMonth / 3);
                        const qStartMonth = currQuarter * 3;
                        start = new Date(today.getFullYear(), qStartMonth, 1);
                        end = today;
                        label = "Quarter to date";
                    }
                    break;
                case 'ytd': // Year to date
                    start = new Date(today.getFullYear(), 0, 1);
                    end = today;
                    label = "Year to date";
                    break;
                case 'all_time':
                    start = null;
                    end = null;
                    label = "All Time";
                    break;
                default:
                    return;
            }
            
            this.startDate = start;
            this.endDate = end;
            // Set viewDate to Start Month (Left Grid)
            if (start) {
                this.viewDate = new Date(start.getFullYear(), start.getMonth(), 1);
            } else {
                const now = new Date();
                this.viewDate = new Date(now.getFullYear(), now.getMonth(), 1);
            }
            
            // Update button text
            if(this.els.selectPreset) {
                const textSpan = this.els.selectPreset.querySelector('.preset-label-text');
                if(textSpan) textSpan.textContent = label;
            }
            this.currentLabel = label;

            this.render();
            this.updateInputs();
            if(this.els.checkCompare && this.els.checkCompare.checked) {
                this.toggleCompare(true); // Recalculate compare dates
            }
            
            // Auto Apply on Preset Click
            applyDateRange();
        }


        
        // populateYears removed - handled in PHP

        toggleCompare(isChecked) {
             if(!this.els.inputCompareStart || !this.els.inputCompareEnd) return;
             
             // Update aria-checked
             if(this.els.checkCompare) {
                 this.els.checkCompare.setAttribute('aria-checked', isChecked ? 'true' : 'false');
             }

             const wrapper = this.els.inputCompareStart.closest('.dateInputs__Wrapper--OHckw');
             if(wrapper) wrapper.style.opacity = isChecked ? '1' : '0.5';
             
             // Enable/Disable inputs
             this.els.inputCompareStart.disabled = !isChecked; // Using disabled attribute is better for 'showing' state
             this.els.inputCompareEnd.disabled = !isChecked;
             
             if(isChecked) {
                 this.updateCompareInputs(); // Force update values when checking
             }
        }

        toggle() {
            this.els.popover.classList.toggle('show');
        }
        
        changeMonth(delta) {
            this.viewDate.setMonth(this.viewDate.getMonth() + delta);
            this.render();
        }

        render() {
            // viewDate now represents the LEFT (start) Month
            const leftYear = this.viewDate.getFullYear();
            const leftMonth = this.viewDate.getMonth();
            const rightDate = new Date(leftYear, leftMonth + 1, 1);
            const rightYear = rightDate.getFullYear();
            const rightMonth = rightDate.getMonth();

            // Update Selectors (to Left Month)
            if(this.els.selectMonth) this.els.selectMonth.value = leftMonth;
            if(this.els.selectYear) this.els.selectYear.value = leftYear;

            // Update Grid Titles (Above grids)
            if(this.els.leftTitle) this.els.leftTitle.textContent = this.viewDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            if(this.els.rightTitle) this.els.rightTitle.textContent = rightDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });

            if(this.els.leftGrid) this.renderGrid(this.els.leftGrid, leftYear, leftMonth);
            if(this.els.rightGrid) this.renderGrid(this.els.rightGrid, rightYear, rightMonth);
        }

        renderGrid(container, year, month) {
            container.innerHTML = '';
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'dayCell__Day--0NGiF dayCell__DayPassive--rQM2t'; 
                container.appendChild(empty);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, month, d);
                const btn = document.createElement('button');
                btn.className = 'dayCell__Day--0NGiF';
                btn.textContent = d;
                
                const t = date.getTime();
                const s = this.startDate ? new Date(this.startDate.getFullYear(), this.startDate.getMonth(), this.startDate.getDate()).getTime() : null;
                const e = this.endDate ? new Date(this.endDate.getFullYear(), this.endDate.getMonth(), this.endDate.getDate()).getTime() : null;

                if (s && t === s) btn.classList.add('dayCell__DayIsStartRange--p80p4');
                if (e && t === e) btn.classList.add('dayCell__DayIsEndRange--EGp-Q');
                if (s && e && t > s && t < e) btn.classList.add('dayCell__DayInRange--l97gF');
                
                btn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    this.handleDayClick(date);
                });
                container.appendChild(btn);
            }
        }

        handleDayClick(date) {
            if (this.startDate && this.endDate) {
                this.startDate = date;
                this.endDate = null;
            } else if (!this.startDate) {
                this.startDate = date;
            } else if (!this.endDate) {
                if (date < this.startDate) {
                    this.endDate = this.startDate;
                    this.startDate = date;
                } else {
                    this.endDate = date;
                }
            } else {
                this.startDate = date;
            }
            this.updateInputs();
            this.render();
        }

        updateInputs() {
            if (this.els.inputStart && this.startDate) this.els.inputStart.value = this.startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            if (this.els.inputEnd && this.endDate) this.els.inputEnd.value = this.endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            this.updateCompareInputs(); // Keep compare inputs in sync
        }
        
        updateMainButton() {
             const rangeTextDOM = document.querySelector('.DataRangeFilter__Range--sFRWK');
             const rangeNameDOM = document.querySelector('.DataRangeFilter__RangeName--Zdy5n');
             
             const s = this.startDate ? this.startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '...';
             const e = this.endDate ? this.endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '...';
             
             if(rangeTextDOM) rangeTextDOM.textContent = `${s} – ${e}`;
             if(rangeNameDOM && this.currentLabel) rangeNameDOM.textContent = this.currentLabel;
        }

        updateCompareInputs() {
             if(!this.els.inputCompareStart || !this.els.inputCompareEnd) return;
             
             if (this.startDate && this.endDate) {
                 const range = this.calculateCompareRange(this.compareMode || 'previous_period');
                 
                 if(range.start && range.end) {
                     this.els.inputCompareStart.value = range.start.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                     this.els.inputCompareEnd.value = range.end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                 }
             } else {
                 this.els.inputCompareStart.value = "";
                 this.els.inputCompareEnd.value = "";
             }
        }
    }

    // Global Functions for HTML onclicks
    function toggleDatePopover(event) {
        if(event) event.stopPropagation();
        if (datePickerInstance) datePickerInstance.toggle();
    }
    
    function applyDateRange() {
        if (datePickerInstance) {
            datePickerInstance.updateMainButton(); 
            datePickerInstance.toggle();
            
            const startStr = datePickerInstance.startDate ? datePickerInstance.startDate.toISOString().split('T')[0] : '';
            const endStr = datePickerInstance.endDate ? datePickerInstance.endDate.toISOString().split('T')[0] : '';
            
            // Auto Trigger Sync on Apply (Range or All Time)
            if ((startStr && endStr) || (!startStr && !endStr && datePickerInstance.currentLabel === 'All Time')) {
                // Determine if we should clear current table to show "Working..." state better
                const tbody = document.getElementById('table-body');
                if(tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">Syncing data for selected range...</td></tr>';
                
                // Call runReport in auto mode to fetch fresh data for this range
                runReport(true); 
            } else {
                fetchReportData(startStr, endStr);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        datePickerInstance = new DateRangePicker();
    });

    document.addEventListener('click', function(event) {
        const popover = document.getElementById('DatePopover');
        const trigger = document.getElementById('PrimaryFilter');
        if (popover && popover.classList.contains('show')) {
            if (!popover.contains(event.target) && !trigger.contains(event.target)) {
                popover.classList.remove('show');
            }
        }
    });



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
    
    // Auto-run state
    let hasAttemptedAutoRun = false;

    // Core Report Functions
    // Enhanced Run Report Logic
    function runReport(isAuto = false) {
        if(!isAuto) {
            const btn = document.querySelector('.rp-btn-primary[onclick="runReport()"]');
            if(btn) {
                 if(!btn.dataset.original) btn.dataset.original = btn.innerHTML;
                 // Spinner
                 btn.innerHTML = `<svg class="animate-spin" viewBox="0 0 24 24" style="width:16px;height:16px;animation:spin 1s linear infinite;fill:none;stroke:currentColor;stroke-width:2;margin-right:6px;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"></path></svg> Syncing...`;
                 btn.disabled = true;
                 
                 if(!document.getElementById('spin-style')) {
                    const style = document.createElement('style');
                    style.id = 'spin-style';
                    style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                    document.head.appendChild(style);
                 }
            }
        }
        
        let bodyFn = { filters: [] };
        if (datePickerInstance && datePickerInstance.startDate && datePickerInstance.endDate) {
             const start = datePickerInstance.startDate.toISOString().split('T')[0];
             const end = datePickerInstance.endDate.toISOString().split('T')[0];
             bodyFn.filters.push({
                 field: 'created_at',
                 operator: '>=',
                 value: start
             });
             bodyFn.filters.push({
                 field: 'created_at',
                 operator: '<=',
                 value: end
             });
        }

        shopFetch(`${baseUrl}/reports/${reportId}/run`, { 
            method: 'POST',
            body: JSON.stringify(bodyFn),
            headers: {'Content-Type': 'application/json'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If COMPLETED, fetch immediately.
                if (data.status === 'COMPLETED') {
                     refreshTableAfterSync(isAuto);
                } else if (data.status === 'PENDING' && data.operation_id) {
                     // Start polling from client side
                     pollReportStatus(data.operation_id, isAuto);
                } else {
                     // Fallback for unknown status
                     setTimeout(() => refreshTableAfterSync(isAuto), 3000);
                }
            } else {
                if(!isAuto) {
                    alert('Error: ' + (data.error || 'Failed to run report'));
                    resetSyncButton();
                }
                console.error("Run Report Failed", data);
                const tbody = document.getElementById('table-body');
                if(tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #d82c0d;">Sync failed: ${data.error}</td></tr>`;
            }
        })
        .catch(err => {
            console.error('Run Report Error:', err);
            if(!isAuto) {
                alert('Failed to run report. Check console for details.');
                resetSyncButton();
            }
        });
    }

    function pollReportStatus(operationId, isAuto) {
        const pollInterval = 2000; // 2 seconds
        const maxAttempts = 30; // 60 seconds max extra wait
        let attempts = 0;

        const poller = setInterval(() => {
            attempts++;
            // Use query param to avoid slash issues in URL path
            shopFetch(`${baseUrl}/api/bulk-operation/status?id=${encodeURIComponent(operationId)}`)
                .then(res => res.json())
                .then(resData => {
                    const status = resData.status || (resData.data ? resData.data.status : null);
                    // console.log("Polling status:", status);
                    
                    if (status === 'COMPLETED') {
                        clearInterval(poller);
                        refreshTableAfterSync(isAuto);
                    } else if (status === 'FAILED' || status === 'CANCELED' || status === 'EXPIRED') {
                        clearInterval(poller);
                        if(!isAuto) {
                            let msg = 'Report generation failed with status: ' + status;
                            if(resData.error_code) msg += ' (' + resData.error_code + ')';
                            if(resData.message) msg += `\n\n${resData.message}`;
                            if(resData.debug && resData.debug.missing_scopes && resData.debug.missing_scopes.length) {
                                msg += `\n\nMissing scopes:\n- ${resData.debug.missing_scopes.join('\n- ')}`;
                            }
                            if(resData.debug && resData.debug.reauthorize_url) {
                                msg += `\n\nReauthorize here:\n${window.location.origin}${resData.debug.reauthorize_url}`;
                            }
                            alert(msg);
                            resetSyncButton();
                        }
                    } else if (attempts >= maxAttempts) {
                        clearInterval(poller);
                        if(!isAuto) {
                            alert('Report generation timed out. Please try refreshing manually.');
                            resetSyncButton();
                        }
                    }
                })
                .catch(e => {
                    console.error("Polling error", e);
                    // Don't stop polling on single network error, but if many?
                    if(attempts % 5 === 0) console.log("Still polling...", attempts);
                });
        }, pollInterval);
    }

    function refreshTableAfterSync(isAuto) {
        if (datePickerInstance) {
            const startStr = datePickerInstance.startDate ? datePickerInstance.startDate.toISOString().split('T')[0] : '';
            const endStr = datePickerInstance.endDate ? datePickerInstance.endDate.toISOString().split('T')[0] : '';
            fetchReportData(startStr, endStr, true); // Silent refresh
        } else {
            fetchReportData(null, null, true);
        }
        if(!isAuto) resetSyncButton();
    }

    function resetSyncButton() {
        const btn = document.querySelector('.rp-btn-primary[onclick="runReport()"]');
        if(btn && btn.dataset.original) {
            btn.innerHTML = btn.dataset.original;
            btn.disabled = false;
        }
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

    // Load report data function
    function fetchReportData(startDate = null, endDate = null, silent = false) {
        const tbody = document.getElementById('table-body');
        
        // Only show loading if NOT silent
        if(!silent && tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">Loading data...</td></tr>';

        let url = `${baseUrl}/reports/${reportId}/data`;
        const params = new URLSearchParams();
        if(startDate) params.append('start_date', startDate);
        if(endDate) params.append('end_date', endDate);
        
        // Add shop/host params
        params.append('shop', reportShopDomain);
        if(reportHost) params.append('host', reportHost);
        
        // Cache buster
        params.append('_t', new Date().getTime());

        fetch(`${url}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    let rows = data.data;
                    
                    // Update columns if provided (e.g. after auto-patch)
                    if (data.columns && Array.isArray(data.columns) && data.columns.length > 0) {
                        activeReportColumns = data.columns;
                        // console.log("Updated activeReportColumns:", activeReportColumns);
                    }

                    // Debug logging
                    // console.log("Raw rows:", rows.length, rows[0]);

                    const originalCount = rows.length;
                    
                    const datasetType = "<?= $config['dataset'] ?? '' ?>";
                    const isTotalInventory = datasetType === 'total_inventory_summary';

                    if (startDate && endDate && !isTotalInventory) {
                        const start = new Date(startDate);
                        start.setHours(0,0,0,0);
                        const end = new Date(endDate);
                        end.setHours(23,59,59,999);

                        // console.log("Filtering between:", start, end);

                        rows = rows.filter(row => {
                            // Detect date field
                            let dateVal = null;
                            
                            // Prioritize robust dates
                            if(row.date) dateVal = row.date;
                            else if(row.processedAt) dateVal = row.processedAt;
                            else if(row.processed_at) dateVal = row.processed_at;
                            else if(row.updatedAt) dateVal = row.updatedAt;
                            else if(row.updated_at) dateVal = row.updated_at;
                            else if(row.createdAt || row.created_at) {
                                dateVal = row.createdAt || row.created_at;
                            }

                            if (!dateVal) return true; // Keep if no relevant date found

                            const d = new Date(dateVal);
                            const inRange = d >= start && d <= end;
                            // if(!inRange) console.log("Excluded:", dateVal, row);
                            return inRange;
                        });
                    }

                    if (rows.length > 0) {
                        renderTableData(rows);
                        hasAttemptedAutoRun = true; 
                    } else {
                         // Data exists but hidden by filter?
                        if (originalCount > 0) {
                             if(tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">
                                No data matching this date range.<br/>
                                <span style="font-size:12px; color:#8c9196;">(${originalCount} records hidden by filter)</span>
                             </td></tr>`;
                             document.getElementById('record-count').innerText = `0 records (${originalCount} hidden)`;
                        } else {
                            handleNoData(tbody);
                        }
                    }
                } else {
                    handleNoData(tbody);
                }
            })
            .catch(err => {
                console.error("Fetch Error details:", err);
                if(tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #d82c0d;">Error: ${err.message}</td></tr>`;
            });
    }

    function handleNoData(tbody) {
        if (!hasAttemptedAutoRun) {
            // Auto-trigger Sync
            if(tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">Syncing latest data from Shopify...</td></tr>';
            hasAttemptedAutoRun = true;
            runReport(true);
        } else {
            // Truly empty after sync
            if(tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center; padding: 20px; color: #6d7175;">No data found for this period.</td></tr>';
            document.getElementById('record-count').innerText = '0 records';
            const chartWrap = document.getElementById('chart-wrap');
            if(chartWrap) chartWrap.style.display = 'none'; 
        }
    }

    function renderTableData(rows) {
        // Handle chart visualization
        if (visualType === 'chart') {
            document.getElementById('chart-wrap').style.display = 'block';
            renderChart(rows);
        }
        
        const configColumns = activeReportColumns; 
        
        // Column definitions with formatters
        const columnDefs = {
                'id': { label: 'Id', formatter: val => formatId(val) },
                'image': { 
                    label: 'Image', 
                    formatter: val => val ? `<img src="${val}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" alt="" loading="lazy" />` : '<div style="width: 40px; height: 40px; background: #f1f2f3; border-radius: 4px;"></div>' 
                },
                    'created_at': { label: 'DAY Created at', key: 'createdAt', formatter: val => formatDate(val) },
                    'updated_at': { label: 'Last Updated', key: 'updatedAt', formatter: val => formatDate(val) },
                    'email': { label: 'Email' },
                    'accepts_marketing': { 
                        label: 'Accepts email marketing', 
                        key: 'emailMarketingConsent', 
                        formatter: val => (val && val.marketingState === 'SUBSCRIBED') ? 'Yes' : 'No' 
                    },
                    'full_name': { label: 'Full name', key: 'displayName' },
                    'country': { label: 'Country', key: 'defaultAddress', formatter: val => val?.country || (typeof val === 'string' ? val : '') },
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
                    
                    // Line Items & Products
                    'quantity': { label: 'Quantity', key: 'quantity' },
                    'price': { 
                        label: 'Price', 
                        formatter: (val, row) => {
                            if (val && typeof val === 'object' && val.amount !== undefined) return formatMoney(val);
                            if (row.priceRangeV2) return formatMoney(row.priceRangeV2.minVariantPrice);
                            if (row.priceSet) return formatMoney(row.priceSet.shopMoney);
                            return formatMoney(val);
                        }
                    },
                    'cost': { label: 'Cost', formatter: val => val === '-' ? '-' : formatMoney(val) },
                    'unit_margin': { label: 'Unit Margin', formatter: val => val === '-' ? '-' : formatMoney(val) },
                    'unit_margin_percent': { label: 'Unit Margin Percent' },

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
                    'sessions_count': { label: 'Sessions', formatter: val => val ? val.toLocaleString() : '0' },
                    
                    // Products By Type specific
                    'product_title': { label: 'Product Title' },
                    'product_type': { label: 'Product Type' },
                    'total_variants': { label: 'Total Variants' },
                    'total_quantity': { label: 'Total Quantity' },
                    'sku': { label: 'SKU' },
                    'total_inventory_value': { label: 'Total inventory value', formatter: val => formatMoney(val) },
                    'total_inventory_cost': { label: 'Total inventory value (cost based)', formatter: val => formatMoney(val) }
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
        headerRow.innerHTML = activeColumns.map(col => `
            <th>
                <div class="column-header">
                    ${col.label}
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </th>
        `).join('');
        
        // Body
        const tbody = document.getElementById('table-body');
        
        let lastProductTitle = '';
        
        tbody.innerHTML = rows.map(row => {
            const currentRowHtml = '<tr>' + activeColumns.map((col, index) => {
                let val;
                if (col.id === 'country') {
                        val = row.defaultAddress || row.shippingAddress;
                } else if (col.id === 'created_at') {
                        val = row.createdAt || row.created_at;
                } else if (col.id === 'updated_at') {
                        val = row.updatedAt || row.updated_at;
                } else {
                        val = row[col.id];
                        if (val === undefined) val = row[col.key];
                        // Fallback for product_title if missing but title exists
                        if (col.id === 'product_title' && val === undefined && row.title) val = row.title;
                }
                
                // HIDE REPEATING PRODUCT TITLES & IMAGES logic
                let displayVal = col.formatter(val ?? (col.id.includes('total') ? 0 : '-'), row);
                
                if (col.id === 'product_title' || col.id === 'image') {
                     const currentProductTitle = row.product_title || row.title || '';
                     if (currentProductTitle && currentProductTitle === lastProductTitle) {
                         displayVal = '';
                     } else if (col.id === 'product_title') {
                         lastProductTitle = currentProductTitle;
                     }
                }
                
                return `<td>${displayVal}</td>`;
            }).join('') + '</tr>';
            
            return currentRowHtml;
        }).join('');
        
        document.getElementById('record-count').innerText = `${rows.length} records`;
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
         // 1. Load local data immediately (Stale-While-Revalidate)
         if (datePickerInstance) {
            const startStr = datePickerInstance.startDate ? datePickerInstance.startDate.toISOString().split('T')[0] : '';
            const endStr = datePickerInstance.endDate ? datePickerInstance.endDate.toISOString().split('T')[0] : '';
            fetchReportData(startStr, endStr);
         } else {
             fetchReportData(); 
         }
         
         // 2. Trigger Auto-Sync in background to get fresh data
         // Check if we haven't synced recently? For now, always sync on load for "Realtime" feel.
         runReport(true); 
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
