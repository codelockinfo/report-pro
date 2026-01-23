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
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
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
        padding: 12px 20px;
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
                <button class="icon-btn" title="Favorite">
                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true" style="fill: rgb(74, 74, 74);"><path fill-rule="evenodd" d="M8.872 4.123c.453-.95 1.803-.95 2.256 0l1.39 2.912 3.199.421c1.042.138 1.46 1.422.697 2.146l-2.34 2.222.587 3.172c.192 1.034-.901 1.828-1.825 1.327l-2.836-1.54-2.836 1.54c-.924.501-2.017-.293-1.825-1.327l.587-3.172-2.34-2.222c-.762-.724-.345-2.008.697-2.146l3.2-.421 1.389-2.912Zm1.128 1.119-1.222 2.561a1.25 1.25 0 0 1-.965.701l-2.814.371 2.058 1.954c.307.292.446.718.369 1.134l-.517 2.791 2.495-1.354a1.25 1.25 0 0 1 1.192 0l2.495 1.354-.517-2.79a1.25 1.25 0 0 1 .369-1.135l2.058-1.954-2.814-.37a1.25 1.25 0 0 1-.965-.702l-1.222-2.561Z"></path></svg>
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
            <button class="rp-btn rp-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13 7H7v6h6V7z"/><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/></svg>
                AI
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
            
            <!-- Open in new tab Button -->
            <button class="rp-btn rp-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/></svg>
                Open in new tab
            </button>
            
            <!-- More Actions Button -->
            <button class="rp-btn rp-btn-secondary">
                More actions
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            
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
                <strong>Sales by customer</strong> revealing number of orders and average order value, filtered by orders processed in the last 30 days and sorted by total sales in descending order.
            </div>
        </div>
        
        <!-- Control Bar -->
        <div class="control-bar">
            <div class="mr-auto grow overflow-hidden md:mr-0">
                <div class="Look__Toolbar--xFZP+">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-nowrap gap-2">
                            <div class="PrimaryFilters__PrimaryFilter--32fOO">
                                <div class="dateRangePicker__Container--2wpat">
                                    <div>
                                        <div class="">
                                            <div class="DataRangeFilter__Button--A+vc1" id="PrimaryFilter">
                                                <div class="DataRangeFilter__Content--Q7tLy">
                                                    <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.75 3.5a.75.75 0 0 0-1.5 0v.407a3.075 3.075 0 0 0-.702.252 3.75 3.75 0 0 0-1.64 1.639c-.226.444-.32.924-.365 1.47-.043.531-.043 1.187-.043 2v1.464c0 .813 0 1.469.043 2 .045.546.14 1.026.366 1.47a3.75 3.75 0 0 0 1.639 1.64c.444.226.924.32 1.47.365.531.043 1.187.043 2 .043h3.383c.323 0 .542 0 .735-.02a3.75 3.75 0 0 0 3.344-3.344c.02-.193.02-.412.02-.735v-2.883c0-.813 0-1.469-.043-2-.045-.546-.14-1.026-.366-1.47a3.75 3.75 0 0 0-1.639-1.64 3.076 3.076 0 0 0-.702-.251v-.407a.75.75 0 0 0-1.5 0v.259c-.373-.009-.794-.009-1.268-.009h-1.964c-.474 0-.895 0-1.268.009v-.259Zm-1.521 1.995c.197-.1.458-.17.912-.207.462-.037 1.057-.038 1.909-.038h1.9c.853 0 1.447 0 1.91.038.453.037.714.107.912.207.423.216.767.56.983.984.1.197.17.458.207.912.014.18.024.38.029.609h-9.982c.006-.228.015-.429.03-.61.036-.453.106-.714.206-.911a2.25 2.25 0 0 1 .984-.984Zm-1.229 4.005v1.2c0 .853 0 1.447.038 1.91.037.453.107.714.207.912.216.423.56.767.984.983.197.1.458.17.912.207.462.037 1.057.038 1.909.038h3.306c.385 0 .52-.001.626-.012a2.25 2.25 0 0 0 2.006-2.006c.011-.106.012-.241.012-.626v-2.606h-10Z"></path></svg>
                                                    <div class="DataRangeFilter__Primary--xAbxO">
                                                        <div class="DataRangeFilter__RangeName--Zdy5n">Last 365 days</div>
                                                        <div class="DataRangeFilter__Range--sFRWK">Jan 23, 2025 – Jan 22, 2026</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span class="">
                                    <div class="FilterPill__Filter--h7eN3">
                                        <div class="FilterPill__Content--js97k">
                                            <strong class="Polaris-Text--root Polaris-Text--bodySm">Processed At</strong>
                                            <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--subdued"> last 365 days</span>
                                        </div>
                                        <div style="display: flex;">
                                            <button class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMicro Polaris-Button--textAlignCenter Polaris-Button--iconOnly" type="button" aria-pressed="false" tabindex="0" aria-describedby=":r16:" data-polaris-tooltip-activator="true">
                                                <span class="Polaris-Button__Icon">
                                                    <span class="Polaris-Icon">
                                                        <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true"><path fill-rule="evenodd" d="M6.24 8.2a.75.75 0 0 1 1.06.04l2.7 2.908 2.7-2.908a.75.75 0 1 1 1.1 1.02l-3.25 3.5a.75.75 0 0 1-1.1 0l-3.25-3.5a.75.75 0 0 1 .04-1.06Z"></path></svg>
                                                    </span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </span>
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
                <div class="empty-state">
                    <p>No data available. Run the report to generate data.</p>
                    <button onclick="runReport()" class="rp-btn rp-btn-primary">Run Report</button>
                </div>
            <?php endif; ?>
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

    /* New Control Bar Styles from User Snippet */
    .ml-auto { margin-left: auto; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .gap-2 { gap: 0.5rem; }
    .ml-4 { margin-left: 1rem; }
    
    .Polaris-Button--variantSecondary {
        background: white;
        border: 1px solid #c9cccf;
        color: #202223;
        border-radius: 8px; /* Rounded look from image */
        padding: 4px 10px 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 0 rgba(0,0,0,0.05);
    }
    .Polaris-Button--variantSecondary:hover {
        background: #f6f6f7;
        border-color: #c9cccf;
    }

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
    
    .DataRangeFilter__Button--A\+vc1 {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #c9cccf;
        border-radius: 8px;
        padding: 4px 8px;
        cursor: pointer;
        height: 32px;
        box-sizing: border-box;
    }
    .DataRangeFilter__Button--A\+vc1:hover {
        background: #f6f6f7;
    }
    .DataRangeFilter__Content--Q7tLy {
        display: flex;
        align-items: center;
        gap: 8px;
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

                // Header with dropdown arrows
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
