<?php
/**
 * Application Configuration
 */

return [
    'app_name' => 'Report Pro',
    'app_version' => '1.0.0',
    'app_url' => getenv('APP_URL') ?: 'https://reportpro.codelocksolutions.com',
    
    // Shopify App Configuration
    // IMPORTANT: Set these as environment variables for security
    // Do not commit credentials to version control
    'shopify' => [
        'api_key' => getenv('SHOPIFY_API_KEY') ?: '',
        'api_secret' => getenv('SHOPIFY_API_SECRET') ?: '',
        'scopes' => 'read_orders,read_products,read_customers,read_inventory,read_analytics,read_draft_orders,read_marketing_events,read_price_rules,read_discounts,read_shopify_payments_payouts,read_files,read_shopify_payments_disputes',
        'redirect_uri' => getenv('SHOPIFY_REDIRECT_URI') ?: 'https://reportpro.codelocksolutions.com/auth/callback',
        'api_version' => '2024-01',
        'app_type' => 'public', // Public app - can be installed by any merchant
        'embedded' => true, // Embedded app - loads in Shopify admin
        'oauth_version' => '2.0', // OAuth 2.0 for public apps
    ],
    
    // Database Configuration
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'u402017191_report_pro',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    
    // Security
    'secret_key' => getenv('APP_SECRET_KEY') ?: 'change-this-secret-key-in-production',
    'encryption_key' => getenv('APP_ENCRYPTION_KEY') ?: 'change-this-encryption-key-in-production',
    
    // File Storage
    'storage_path' => ROOT_PATH . '/storage',
    'exports_path' => ROOT_PATH . '/storage/exports',
    'max_export_size' => 100 * 1024 * 1024, // 100MB
    
    // Timezone
    'timezone' => 'UTC',
    
    // Pagination
    'per_page' => 50,
    'max_per_page' => 500,
    
    // Cache
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour
    
    // Export
    'export_token_expiry' => 24 * 60 * 60, // 24 hours
];

