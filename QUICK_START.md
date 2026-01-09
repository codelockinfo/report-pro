# Quick Start Guide - Report Pro

## 5-Minute Setup

### 1. Database Setup (1 minute)
```bash
mysql -u root -p
CREATE DATABASE u402017191_report_pro;
exit;
mysql -u root -p u402017191_report_pro < u402017191_report_pro.sql
```

### 2. Configuration (2 minutes)
Edit `config/config.php`:
```php
'shopify' => [
    'api_key' => 'YOUR_API_KEY',
    'api_secret' => 'YOUR_API_SECRET',
    'redirect_uri' => 'https://reportpro.codelocksolutions.com/auth/callback',
],
'database' => [
    'host' => '127.0.0.1',
    'name' => 'u402017191_report_pro',
    'user' => 'root',
    'password' => 'your_password',
],
```

### 3. Create Storage Directory (30 seconds)
```bash
mkdir -p storage/exports
chmod 755 storage/exports
```

### 4. Install App (1 minute)
Visit: `https://reportpro.codelocksolutions.com/auth/install?shop=your-shop.myshopify.com`

### 5. Set Up Cron (30 seconds)
```bash
crontab -e
```
Add:
```
* * * * * php /path/to/report-pro/cron/scheduled_reports.php
*/5 * * * * php /path/to/report-pro/cron/bulk_operations.php
```

## Common Tasks

### Create a Report
1. Go to Reports → Create Report
2. Select dataset
3. Choose columns
4. Save and run

### Export Report
1. Open report
2. Click "Export CSV"
3. Download file

### Schedule Report
1. Go to Schedule
2. Select report
3. Choose frequency
4. Save

## Troubleshooting

### App won't install
- Check API credentials
- Verify redirect URI matches Shopify app settings
- Ensure HTTPS in production

### Reports not generating
- Check bulk operations cron is running
- Verify API rate limits
- Check error logs

### Exports failing
- Check storage/exports directory permissions
- Verify disk space
- Check error logs

## Need Help?

- Full docs: See README.md
- Installation: See INSTALLATION.md
- Architecture: See ARCHITECTURE.md

