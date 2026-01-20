<?php
$title = 'Chart analysis';
$currentRoute = '/chart-analysis';
ob_start();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<style>
    /* Layout */
    .analysis-container {
        padding: 0 20px 20px;
        max-width: 100%;
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

    /* Top Controls */
    .controls-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .control-group {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        padding: 0;
        overflow: hidden;
    }
    .control-btn {
        background: none;
        border: none;
        border-right: 1px solid #dfe3e8;
        padding: 6px 12px;
        font-size: 13px;
        color: #212b36;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .control-btn:last-child {
        border-right: none;
    }
    .control-btn:hover {
        background-color: #f9fafb;
    }
    .add-filter-btn {
        background: #f4f6f8;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
        color: #212b36;
    }

    /* Main Builder Area */
    .builder-layout {
        display: flex;
        gap: 20px;
        height: calc(100vh - 200px);
        min-height: 600px;
    }
    
    /* Sidebar */
    .builder-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: white;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
    }
    .source-selector {
        padding: 10px;
        border-bottom: 1px solid #dfe3e8;
    }
    .source-dropdown {
        width: 100%;
        padding: 8px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-weight: 600;
        font-size: 13px;
        background: #f9fafb;
    }
    .field-search {
        padding: 10px;
        border-bottom: 1px solid #dfe3e8;
    }
    .search-input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-size: 13px;
    }
    .fields-list {
        flex-grow: 1;
        overflow-y: auto;
        padding: 10px 0;
    }
    .field-group {
        margin-bottom: 5px;
    }
    .field-item {
        padding: 6px 15px;
        font-size: 13px;
        color: #212b36;
        cursor: grab;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .field-item:hover {
        background-color: #f4f6f8;
        color: #2c6ecb;
    }
    .field-icon {
        color: #919eab;
        font-size: 10px;
    }
    .field-item.expandable {
        font-weight: 500;
        color: #2c6ecb;
    }
    
    /* Main Content */
    .builder-content {
        flex-grow: 1;
        display: flex;
        gap: 20px;
    }
    .drop-zones {
        width: 200px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .drop-zone {
        background: #f4f6f8;
        border: 1px dashed #babfc3;
        border-radius: 4px;
        padding: 10px;
        min-height: 80px;
    }
    .drop-zone-title {
        font-size: 12px;
        font-weight: 600;
        color: #637381;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    
    .report-preview {
        flex-grow: 1;
        background: white;
        border-radius: 4px;
        padding: 40px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        color: #637381;
        font-size: 14px;
    }
</style>

<div class="analysis-container">
    <!-- Announcement Banner -->
    <div class="announcement-banner">
        <span class="announcement-icon">👉</span>
        <div class="text">
            <strong>Better Reports is making improvements!</strong><br>
            The <strong>Order lines</strong> table has been renamed to <strong>Agreement lines</strong>.<br>
            <a href="#" class="announcement-link">Read the full announcement of upcoming changes.</a>
        </div>
    </div>

    <!-- Controls -->
    <div class="controls-bar">
        <div class="control-group">
            <button class="control-btn">
                Date <svg viewBox="0 0 20 20" width="10" height="10"><path d="M5 8l5 5 5-5z" fill="currentColor"/></svg>
            </button>
            <button class="control-btn">
                Any time <svg viewBox="0 0 20 20" width="10" height="10"><path d="M5 8l5 5 5-5z" fill="currentColor"/></svg>
            </button>
            <button class="control-btn" title="Clear">
                <span style="font-size: 16px;">&times;</span>
            </button>
        </div>
        <button class="add-filter-btn">Add filter</button>
    </div>

    <!-- Report Builder -->
    <div class="builder-layout">
        <!-- Sidebar -->
        <div class="builder-sidebar">
            <div class="source-selector">
                <select class="source-dropdown">
                    <option>Agreement lines (formerly order lines)</option>
                    <option>Orders</option>
                    <option>Products</option>
                    <option>Customers</option>
                </select>
            </div>
            <div class="field-search">
                <input type="text" class="search-input" placeholder="Search...">
            </div>
            <div class="fields-list">
                <div class="field-item" style="color: #2c6ecb; padding-left: 10px;">+ Create new</div>
                
                <div class="field-item"><span class="field-icon">∑</span> Total gross sales</div>
                <div class="field-item"><span class="field-icon">∑</span> Total net sales</div>
                <div class="field-item"><span class="field-icon">∑</span> Total sales</div>
                <div class="field-item"><span class="field-icon">∑</span> Total quantity sold</div>
                <div class="field-item"><span class="field-icon">∑</span> Total orders</div>
                <div class="field-item"><span class="field-icon">∑</span> Total customers</div>
                <div class="field-item"><span class="field-icon">∑</span> Average units per order</div>
                <div class="field-item"><span class="field-icon">∑</span> Average order value</div>
                <div class="field-item" style="color: #637381; font-size: 11px; padding-left: 32px;">More ⌄</div>
                
                <div class="field-item expandable"><span class="field-icon">›</span> Fulfillment Location</div>
                <div class="field-item expandable"><span class="field-icon">›</span> Order</div>
                <div class="field-item expandable"><span class="field-icon">›</span> Product</div>
                <div class="field-item expandable"><span class="field-icon">›</span> Refund</div>
                <div class="field-item expandable"><span class="field-icon">›</span> Variant</div>
                <div class="field-item" style="color: #637381; font-size: 11px; padding-left: 32px;">More ⌄</div>
                
                <div class="field-item"><span class="field-icon">▦</span> Date</div>
                <div class="field-item"><span class="field-icon">abc</span> Kind</div>
                <div class="field-item"><span class="field-icon">abc</span> Line type</div>
                <div class="field-item"><span class="field-icon">abc</span> Product name</div>
                <div class="field-item"><span class="field-icon">abc</span> Product type</div>
                <div class="field-item"><span class="field-icon">abc</span> SKU</div>
                <div class="field-item"><span class="field-icon">abc</span> Type</div>
                <div class="field-item"><span class="field-icon">abc</span> Variant name</div>
                <div class="field-item"><span class="field-icon">abc</span> Vendor</div>
            </div>
        </div>

        <!-- Content -->
        <div class="builder-content">
            <div class="drop-zones">
                <div class="control-group" style="margin-bottom: 0;">
                    <button class="control-btn" style="width: 100%; justify-content: space-between;">
                        Table <svg viewBox="0 0 20 20" width="10" height="10"><path d="M5 8l5 5 5-5z" fill="currentColor"/></svg>
                    </button>
                </div>
                
                <div style="font-size: 11px; color: #637381; margin-top: 5px;">Selected fields ...</div>
                
                <div class="drop-zone">
                    <div class="drop-zone-title">Dimensions</div>
                </div>
                <div class="drop-zone">
                    <div class="drop-zone-title">Measures</div>
                </div>
                <div class="drop-zone">
                    <div class="drop-zone-title">Color saturation (row)</div>
                </div>
                <div class="drop-zone">
                    <div class="drop-zone-title">Hidden fields</div>
                </div>
            </div>
            
            <div class="report-preview">
                Please add fields to your report
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
