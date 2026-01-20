<?php
$title = 'Reports';
ob_start();
?>

<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText Polaris-DisplayText--sizeLarge">Reports</h1>
        </div>
        <div class="Polaris-Page__Actions">
            <a href="/reports/create" class="Polaris-Button Polaris-Button--primary">
                <span class="Polaris-Button__Content">
                    <span>Create Report</span>
                </span>
            </a>
        </div>
    </div>
    
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <div class="Polaris-Stack Polaris-Stack--vertical">
                    <div class="Polaris-Stack__Item">
                        <div class="Polaris-TextField">
                            <input type="text" id="search-input" class="Polaris-TextField__Input" 
                                   placeholder="Search reports..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    
    .dashboard-container {
        padding: 0 20px 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .search-container {
        margin-bottom: 20px;
    }
    .search-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }
    
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        align-items: start;
    }
    
    @media (max-width: 1000px) {
        .reports-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 700px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }
    }

    .report-card {
        background: white;
        border-radius: 4px;
        box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);
        padding: 20px;
        margin-bottom: 20px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #212b36;
        margin: 0;
    }
    .card-action {
        background: #303030;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
    }
    .report-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .report-item {
        margin-bottom: 8px;
    }
    .report-link {
        color: #2c6ecb;
        text-decoration: none;
        font-size: 14px;
    }
    .report-link:hover {
        text-decoration: underline;
    }
</style>

<!-- Custom Header -->
<div class="dashboard-header">
    <div class="header-nav">
        <a href="/reports" class="header-link active">Reports</a>
        <a href="/explore" class="header-link">Explore</a>
        <a href="/schedule" class="header-link">Schedule</a>
        <a href="/settings" class="header-link">Settings</a>
    </div>
    <div class="header-right">
        <a href="#" class="header-link">Docs</a>
    </div>
</div>

<div class="dashboard-container">
    <!-- Search Bar -->
    <form action="/reports" method="GET" class="search-container">
        <input type="text" name="search" class="search-input" placeholder="Search by report name..." value="<?= htmlspecialchars($search ?? '') ?>">
    </form>

    <!-- 3-Column Grid -->
    <div class="reports-grid">
        
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('search-input').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        const search = this.value;
        window.location.href = '/reports?search=' + encodeURIComponent(search);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

