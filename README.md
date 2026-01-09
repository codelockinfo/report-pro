# Report Pro - Shopify Embedded App

A production-ready Shopify Embedded App for generating and managing reports, built with PHP.

## Features

- 📊 **Custom Report Builder** - Create custom reports with flexible column selection and filters
- 📈 **Predefined Reports** - Access to pre-built reports for Orders, Customers, Products, and Transactions
- 📅 **Scheduled Reports** - Automate report generation with daily, weekly, or monthly schedules
- 📤 **Export Functionality** - Export reports to CSV, Excel, or PDF
- 🔐 **Shopify OAuth** - Secure authentication using Shopify OAuth 2.0
- 🌐 **GraphQL API** - Uses Shopify Admin GraphQL API for data fetching
- 📦 **Bulk Operations** - Handles large datasets using Shopify Bulk Operations API
- 🔔 **Webhooks** - GDPR compliance webhooks (data request, redaction, app uninstall)

## Tech Stack

- **Backend**: PHP 8.1+
- **Framework**: Core PHP MVC
- **Frontend**: HTML + CSS + JavaScript
- **UI Framework**: Shopify Polaris CSS (via CDN)
- **Database**: MySQL
- **API**: Shopify Admin GraphQL API

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd report-pro
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

1. Import the database schema:
```bash
mysql -u root -p u402017191_report_pro < u402017191_report_pro.sql
```

2. Update database credentials in `config/config.php` or set environment variables:
```php
'DB_HOST' => '127.0.0.1',
'DB_NAME' => 'u402017191_report_pro',
'DB_USER' => 'root',
'DB_PASSWORD' => 'your_password',
```

### 4. Shopify App Configuration

1. Create a Shopify App in your Partner Dashboard
2. Set the following in `config/config.php`:
```php
'shopify' => [
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'scopes' => 'read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics',
    'redirect_uri' => 'https://reportpro.codelocksolutions.com/auth/callback',
    'api_version' => '2024-01',
],
```

### 5. Web Server Configuration

#### Apache (.htaccess already included)
Ensure mod_rewrite is enabled.

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### 6. Set Up Cron Jobs

Add to your crontab:
```bash
# Check scheduled reports every minute
* * * * * php /path/to/report-pro/cron/scheduled_reports.php

# Check bulk operations every 5 minutes
*/5 * * * * php /path/to/report-pro/cron/bulk_operations.php
```

### 7. Create Storage Directories

```bash
mkdir -p storage/exports
chmod 755 storage/exports
```

## Usage

### Installing the App

1. Navigate to: `https://reportpro.codelocksolutions.com/auth/install?shop=your-shop.myshopify.com`
2. Authorize the app
3. You'll be redirected to the dashboard

### Creating a Custom Report

1. Go to **Reports** → **Create Report**
2. Select a dataset (Orders, Products, Customers, or Transactions)
3. Choose columns to include
4. Add filters (optional)
5. Save and run the report

### Scheduling Reports

1. Go to **Schedule**
2. Select a report
3. Choose frequency (Daily, Weekly, Monthly)
4. Set time and recipients
5. Save schedule

### Exporting Reports

1. Open any report
2. Click **Export CSV** (or Excel/PDF)
3. Download the file

## Project Structure

```
report-pro/
├── app/
│   ├── Core/           # Core MVC classes
│   ├── Controllers/    # Application controllers
│   ├── Models/         # Database models
│   └── Services/        # Business logic services
├── config/             # Configuration files
├── views/              # View templates
├── public/             # Public assets
├── storage/            # File storage
├── cron/               # Cron job scripts
├── vendor/             # Composer dependencies
├── index.php           # Application entry point
└── composer.json       # Composer configuration
```

## API Endpoints

### Authentication
- `GET /auth/install` - Start OAuth installation
- `GET /auth/callback` - OAuth callback
- `GET /auth/logout` - Logout

### Reports
- `GET /reports` - List all reports
- `GET /reports/create` - Create report form
- `POST /reports/store` - Save new report
- `GET /reports/{id}` - View report
- `POST /reports/{id}/run` - Execute report
- `GET /reports/{id}/data` - Get report data (JSON)

### Exports
- `POST /export/generate` - Generate export
- `GET /export/{token}/download` - Download export file

### Schedules
- `GET /schedule` - List schedules
- `POST /schedule/store` - Create schedule
- `POST /schedule/{id}/toggle` - Enable/disable schedule

## Security

- ✅ OAuth HMAC validation
- ✅ Webhook verification
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (htmlspecialchars in views)
- ✅ CSRF protection (state parameter in OAuth)

## GDPR Compliance

The app handles the following GDPR webhooks:
- `customers/data_request` - Customer data request
- `customers/redact` - Customer data redaction
- `shop/redact` - Shop data redaction
- `app/uninstalled` - App uninstall cleanup

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/config.php`
- Ensure MySQL is running
- Verify database exists

### OAuth Errors
- Verify API key and secret in config
- Check redirect URI matches Shopify app settings
- Ensure scopes are correct

### Bulk Operations Not Completing
- Check cron job is running
- Verify API rate limits
- Check error logs

## License

Proprietary - All rights reserved

## Support

For issues and questions, please contact support.

