<nav class="Polaris-Navigation">
    <div class="Polaris-Navigation__Section">
        <ul class="Polaris-Navigation__Section">
            <li class="Polaris-Navigation__ListItem">
                <a href="/dashboard" class="Polaris-Navigation__Item <?= $currentPage === 'dashboard' ? 'Polaris-Navigation__Item--selected' : '' ?>">
                    <span class="Polaris-Navigation__Text">Dashboard</span>
                </a>
            </li>
            <li class="Polaris-Navigation__ListItem">
                <a href="/reports" class="Polaris-Navigation__Item <?= $currentPage === 'reports' ? 'Polaris-Navigation__Item--selected' : '' ?>">
                    <span class="Polaris-Navigation__Text">Reports</span>
                </a>
            </li>
            <li class="Polaris-Navigation__ListItem">
                <a href="/explore" class="Polaris-Navigation__Item <?= $currentPage === 'explore' ? 'Polaris-Navigation__Item--selected' : '' ?>">
                    <span class="Polaris-Navigation__Text">Explore</span>
                </a>
            </li>
            <li class="Polaris-Navigation__ListItem">
                <a href="/schedule" class="Polaris-Navigation__Item <?= $currentPage === 'schedule' ? 'Polaris-Navigation__Item--selected' : '' ?>">
                    <span class="Polaris-Navigation__Text">Schedule</span>
                </a>
            </li>
            <li class="Polaris-Navigation__ListItem">
                <a href="/settings" class="Polaris-Navigation__Item <?= $currentPage === 'settings' ? 'Polaris-Navigation__Item--selected' : '' ?>">
                    <span class="Polaris-Navigation__Text">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

