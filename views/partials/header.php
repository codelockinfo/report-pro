<style>
    .dashboard-header {
        background-color: #303030;
        color: white;
        padding: 0 20px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .header-nav {
        display: flex;
        gap: 20px;
        height: 100%;
    }
    .header-link {
        color: #e0e0e0;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        padding: 0 10px;
        height: 100%;
        border-bottom: 3px solid transparent;
    }
    .header-link.active {
        color: white;
        border-bottom-color: white;
    }
    .header-link:hover {
        color: white;
        text-decoration: none;
    }
</style>

<div class="dashboard-header">
    <div class="header-nav">
        <a href="/reports" class="header-link <?= ($currentRoute === '/reports') ? 'active' : '' ?>">Reports</a>
        <a href="/explore" class="header-link <?= ($currentRoute === '/explore') ? 'active' : '' ?>">Chart analysis</a>
        <a href="/schedule" class="header-link <?= ($currentRoute === '/schedule') ? 'active' : '' ?>">Schedule</a>
        <a href="/settings" class="header-link <?= ($currentRoute === '/settings') ? 'active' : '' ?>">Settings</a>
    </div>
    <div class="header-right">
        <a href="#" class="header-link">Docs</a>
    </div>
</div>
