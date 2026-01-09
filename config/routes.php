<?php
/**
 * Application Routes
 */

// Authentication routes
$router->get('/auth/install', 'AuthController@install');
$router->get('/auth/callback', 'AuthController@callback');
$router->get('/auth/logout', 'AuthController@logout');

// Main app routes (require authentication)
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Reports routes
$router->get('/reports', 'ReportController@index');
$router->get('/reports/create', 'ReportController@create');
$router->post('/reports/store', 'ReportController@store');
$router->get('/reports/{id}', 'ReportController@show');
$router->get('/reports/{id}/edit', 'ReportController@edit');
$router->post('/reports/{id}/update', 'ReportController@update');
$router->post('/reports/{id}/delete', 'ReportController@delete');
$router->post('/reports/{id}/run', 'ReportController@run');
$router->get('/reports/{id}/data', 'ReportController@getData');

// Predefined reports
$router->get('/reports/predefined/{type}', 'ReportController@predefined');

// Explore routes
$router->get('/explore', 'ExploreController@index');
$router->get('/explore/{category}', 'ExploreController@category');

// Schedule routes
$router->get('/schedule', 'ScheduleController@index');
$router->post('/schedule/store', 'ScheduleController@store');
$router->post('/schedule/{id}/update', 'ScheduleController@update');
$router->post('/schedule/{id}/delete', 'ScheduleController@delete');
$router->post('/schedule/{id}/toggle', 'ScheduleController@toggle');

// Export routes
$router->post('/export/generate', 'ExportController@generate');
$router->get('/export/{token}/download', 'ExportController@download');
$router->get('/export/history', 'ExportController@history');

// Settings routes
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/update', 'SettingsController@update');

// API routes
$router->post('/api/graphql', 'ApiController@graphql');
$router->post('/api/bulk-operation', 'ApiController@bulkOperation');
$router->get('/api/bulk-operation/{id}/status', 'ApiController@bulkOperationStatus');

// Webhook routes
$router->post('/webhooks/app/uninstalled', 'WebhookController@appUninstalled');
$router->post('/webhooks/customers/data_request', 'WebhookController@customersDataRequest');
$router->post('/webhooks/customers/redact', 'WebhookController@customersRedact');
$router->post('/webhooks/shop/redact', 'WebhookController@shopRedact');

// AJAX routes
$router->post('/ajax/reports/search', 'AjaxController@searchReports');
$router->post('/ajax/reports/columns', 'AjaxController@getColumns');
$router->get('/ajax/reports/{id}/preview', 'AjaxController@previewReport');

