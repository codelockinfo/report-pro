# Report Pro - System Architecture

## Overview

Report Pro is a Shopify Embedded App built with PHP that provides comprehensive reporting capabilities for Shopify merchants.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     Shopify Admin                            │
│                    (Embedded App)                            │
└───────────────────────┬───────────────────────────────────────┘
                        │
                        │ HTTPS / App Bridge
                        │
┌───────────────────────▼───────────────────────────────────────┐
│                    Web Server                                 │
│              (Apache/Nginx + PHP 8.1+)                        │
└───────────────────────┬───────────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
┌───────▼──────┐ ┌──────▼──────┐ ┌──────▼──────┐
│   Router     │ │  Controllers │ │   Models    │
│              │ │              │ │             │
│  - Routes    │ │  - Auth      │ │  - Shop     │
│  - Dispatch  │ │  - Reports   │ │  - Report   │
│              │ │  - Export    │ │  - Schedule │
└───────┬──────┘ └──────┬───────┘ └──────┬──────┘
        │               │               │
        └───────────────┼───────────────┘
                        │
            ┌───────────▼───────────┐
            │      Services         │
            │                       │
            │  - ShopifyService     │
            │  - ReportBuilder       │
            │  - ExportService      │
            └───────────┬───────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
┌───────▼──────┐ ┌──────▼──────┐ ┌──────▼──────┐
│   MySQL      │ │  Shopify    │ │   Storage   │
│   Database   │ │  GraphQL    │ │   (Files)   │
│              │ │   API       │ │             │
│  - shops     │ │             │ │  - exports  │
│  - reports   │ │  - Orders   │ │  - cache   │
│  - schedules │ │  - Products │ │             │
│  - exports   │ │  - Customers│ │             │
└──────────────┘ └─────────────┘ └─────────────┘
```

## Component Details

### 1. Frontend Layer

**Technology**: HTML + CSS + JavaScript + Shopify Polaris

**Components**:
- **App Bridge**: Handles embedded app communication
- **Polaris CSS**: Shopify design system
- **JavaScript**: Vanilla JS for interactivity

**Key Files**:
- `views/layouts/app.php` - Main layout
- `views/dashboard/index.php` - Dashboard
- `views/reports/*.php` - Report views
- `public/js/app.js` - Client-side logic

### 2. Application Layer

#### Router (`app/Core/Router.php`)
- Handles URL routing
- Maps URLs to controller methods
- Supports route parameters

#### Controllers (`app/Controllers/`)
- **AuthController**: OAuth flow, session management
- **DashboardController**: Main dashboard
- **ReportController**: Report CRUD operations
- **ExportController**: Export generation and download
- **ScheduleController**: Report scheduling
- **SettingsController**: App settings
- **WebhookController**: Webhook handling
- **ApiController**: API endpoints
- **AjaxController**: AJAX endpoints

#### Models (`app/Models/`)
- **Shop**: Shop data management
- **Report**: Report definitions
- **ReportColumn**: Report column configuration
- **ReportFilter**: Report filter configuration
- **ReportResult**: Report execution results
- **Schedule**: Scheduled report configurations
- **Export**: Export file management
- **BulkOperation**: Bulk operation tracking
- **Settings**: App settings

#### Services (`app/Services/`)
- **ShopifyService**: Shopify GraphQL API client
- **ReportBuilderService**: Report query building and execution
- **ExportService**: Export file generation

### 3. Data Layer

#### Database Schema

**Core Tables**:
- `shops` - Shopify store information
- `reports` - Report definitions
- `report_columns` - Column configurations
- `report_filters` - Filter configurations
- `report_results` - Cached report data
- `schedules` - Scheduled report configurations
- `exports` - Export file metadata
- `bulk_operations` - Bulk operation tracking
- `cached_data` - General data caching
- `settings` - App settings per shop

**Relationships**:
- shops → reports (1:N)
- reports → report_columns (1:N)
- reports → report_filters (1:N)
- reports → report_results (1:N)
- reports → schedules (1:N)
- shops → exports (1:N)

### 4. External Integrations

#### Shopify Admin GraphQL API

**Endpoints Used**:
- `/admin/api/{version}/graphql.json` - GraphQL queries
- Bulk Operations API - Large dataset queries

**Operations**:
- Fetch orders
- Fetch products
- Fetch customers
- Fetch transactions
- Create bulk operations
- Check bulk operation status

#### Webhooks

**Handled Webhooks**:
- `app/uninstalled` - Clean up shop data
- `customers/data_request` - GDPR data request
- `customers/redact` - GDPR customer redaction
- `shop/redact` - GDPR shop redaction

### 5. Background Processing

#### Cron Jobs

**scheduled_reports.php** (Runs every minute):
- Checks for due scheduled reports
- Executes reports
- Generates exports
- Sends emails (if configured)
- Updates next run time

**bulk_operations.php** (Runs every 5 minutes):
- Checks pending bulk operations
- Polls Shopify for completion status
- Processes completed operations
- Updates database

### 6. Security

#### Authentication
- OAuth 2.0 flow
- HMAC validation
- Session management
- Token encryption

#### Data Protection
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- CSRF protection (state parameter)
- Secure file storage
- Token-based download links

### 7. File Structure

```
report-pro/
├── app/
│   ├── Core/              # Core MVC framework
│   ├── Controllers/       # Request handlers
│   ├── Models/           # Data models
│   └── Services/         # Business logic
├── config/               # Configuration
├── views/                # View templates
│   ├── layouts/         # Layout templates
│   ├── dashboard/        # Dashboard views
│   ├── reports/         # Report views
│   └── ...
├── public/              # Public assets
├── storage/             # File storage
│   └── exports/         # Export files
├── cron/                # Cron job scripts
├── vendor/              # Composer dependencies
└── index.php            # Entry point
```

## Data Flow

### Report Generation Flow

1. User creates/edits report configuration
2. User clicks "Run Report"
3. Controller calls ReportBuilderService
4. Service builds GraphQL query
5. Service creates bulk operation via ShopifyService
6. Bulk operation ID saved to database
7. Cron job polls for completion
8. On completion, data downloaded and processed
9. Results saved to report_results table
10. User views report data

### Export Flow

1. User clicks "Export"
2. Controller calls ExportService
3. Service reads report_results data
4. Service generates file (CSV/Excel/PDF)
5. File saved to storage/exports
6. Export record created with download token
7. User downloads via token URL
8. Token expires after 24 hours

### Scheduled Report Flow

1. User creates schedule
2. Schedule saved with next_run_at time
3. Cron job checks for due schedules
4. Cron executes report (same as manual flow)
5. Export generated
6. Email sent (if configured)
7. Next run time calculated and updated

## Performance Considerations

### Caching Strategy
- Report results cached in database
- Bulk operation results cached
- Settings cached per shop

### Database Optimization
- Indexed foreign keys
- Indexed frequently queried columns
- Proper table relationships

### API Usage
- Bulk Operations for large datasets
- Rate limit handling
- Efficient GraphQL queries
- Pagination support

## Scalability

### Horizontal Scaling
- Stateless application design
- Database connection pooling
- File storage can be moved to S3/cloud

### Vertical Scaling
- PHP-FPM configuration
- MySQL query optimization
- Caching layer (Redis/Memcached) - future enhancement

## Monitoring & Logging

### Error Logging
- PHP error_log
- Application-level logging
- Webhook error tracking

### Performance Monitoring
- Database query logging
- API response time tracking
- File generation time tracking

## Future Enhancements

1. **Caching Layer**: Redis/Memcached integration
2. **Queue System**: Laravel Queue or similar
3. **Email Service**: SMTP/API integration
4. **Analytics**: User behavior tracking
5. **Multi-tenant**: Enhanced isolation
6. **API Rate Limiting**: Per-shop rate limits
7. **Real-time Updates**: WebSocket support
8. **Advanced Filters**: More filter options
9. **Chart Visualizations**: Chart.js integration
10. **Export Formats**: More export options

