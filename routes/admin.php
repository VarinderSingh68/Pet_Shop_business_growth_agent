<?php

declare(strict_types=1);

use App\Controllers\Admin\ActivityController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\BrandController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\CustomerController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DeveloperController;
use App\Controllers\Admin\InventoryController;
use App\Controllers\Admin\MarketingController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\SecurityController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\SettingsController;
use App\Core\Router;

/** @var Router $router */

$router->get('/admin/login', [AdminAuthController::class, 'showLogin'], ['guest']);
$router->post('/admin/login', [AdminAuthController::class, 'login'], ['guest', 'csrf', 'throttle:10,1']);
$router->get('/admin/login/2fa', [AdminAuthController::class, 'showTwoFactorChallenge'], ['guest']);
$router->post('/admin/login/2fa', [AdminAuthController::class, 'verifyTwoFactor'], ['guest', 'csrf', 'throttle:10,1']);
$router->post('/admin/logout', [AdminAuthController::class, 'logout'], ['admin', 'csrf']);
$router->post('/admin/customers/stop-impersonating', [CustomerController::class, 'stopImpersonate'], ['auth', 'csrf']);

$router->group(['prefix' => '/admin', 'middleware' => ['admin']], function (Router $router) {
    $router->get('/', [DashboardController::class, 'index']);

    $router->get('/catalogue', [ProductController::class, 'index']);
    $router->post('/catalogue/bulk', [ProductController::class, 'bulkAction'], ['csrf']);
    $router->get('/catalogue/products/create', [ProductController::class, 'create']);
    $router->post('/catalogue/products', [ProductController::class, 'store'], ['csrf']);
    $router->get('/catalogue/products/{id}/edit', [ProductController::class, 'edit']);
    $router->post('/catalogue/products/{id}', [ProductController::class, 'update'], ['csrf']);
    $router->post('/catalogue/products/{id}/delete', [ProductController::class, 'destroy'], ['csrf']);
    $router->post('/catalogue/products/{id}/variants', [ProductController::class, 'storeVariant'], ['csrf']);
    $router->post('/catalogue/products/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'], ['csrf']);
    $router->post('/catalogue/products/{id}/variants/{variantId}/delete', [ProductController::class, 'destroyVariant'], ['csrf']);
    $router->post('/catalogue/products/{id}/images', [ProductController::class, 'storeImage'], ['csrf']);
    $router->post('/catalogue/products/{id}/images/{imageId}/delete', [ProductController::class, 'destroyImage'], ['csrf']);

    $router->get('/catalogue/import', [ProductController::class, 'importForm']);
    $router->post('/catalogue/import', [ProductController::class, 'import'], ['csrf']);

    $router->get('/catalogue/categories', [CategoryController::class, 'index']);
    $router->post('/catalogue/categories', [CategoryController::class, 'store'], ['csrf']);
    $router->post('/catalogue/categories/{id}', [CategoryController::class, 'update'], ['csrf']);
    $router->post('/catalogue/categories/{id}/delete', [CategoryController::class, 'destroy'], ['csrf']);

    $router->get('/catalogue/brands', [BrandController::class, 'index']);
    $router->post('/catalogue/brands', [BrandController::class, 'store'], ['csrf']);
    $router->post('/catalogue/brands/{id}', [BrandController::class, 'update'], ['csrf']);
    $router->post('/catalogue/brands/{id}/delete', [BrandController::class, 'destroy'], ['csrf']);

    $router->get('/inventory', [InventoryController::class, 'index']);
    $router->post('/inventory/{variantId}/adjust', [InventoryController::class, 'adjust'], ['csrf']);
    $router->get('/inventory/{variantId}/ledger', [InventoryController::class, 'ledger']);

    $router->get('/orders', [OrderController::class, 'index']);
    $router->get('/orders/{id}', [OrderController::class, 'show']);
    $router->post('/orders/{id}/status', [OrderController::class, 'updateStatus'], ['csrf']);
    $router->post('/orders/{id}/note', [OrderController::class, 'addNote'], ['csrf']);
    $router->post('/orders/{id}/refund', [OrderController::class, 'refund'], ['csrf']);
    $router->get('/orders/{id}/invoice', [OrderController::class, 'invoice']);

    $router->get('/customers', [CustomerController::class, 'index']);
    $router->get('/customers/{id}', [CustomerController::class, 'show']);
    $router->post('/customers/{id}/impersonate', [CustomerController::class, 'impersonate'], ['csrf']);

    $router->get('/marketing', [MarketingController::class, 'segments']);
    $router->get('/marketing/segments', [MarketingController::class, 'segments']);
    $router->get('/marketing/segments/{id}', [MarketingController::class, 'segmentMembers']);
    $router->get('/marketing/campaigns', [MarketingController::class, 'campaigns']);
    $router->post('/marketing/campaigns', [MarketingController::class, 'storeCampaign'], ['csrf']);
    $router->get('/marketing/campaigns/{id}', [MarketingController::class, 'showCampaign']);
    $router->post('/marketing/campaigns/{id}/send', [MarketingController::class, 'sendCampaign'], ['csrf']);
    $router->post('/marketing/copilot/{key}', [MarketingController::class, 'copilotAction'], ['csrf']);
    $router->get('/marketing/activity', [MarketingController::class, 'growthActions']);
    $router->get('/marketing/coupons', [MarketingController::class, 'coupons']);
    $router->post('/marketing/coupons', [MarketingController::class, 'storeCoupon'], ['csrf']);
    $router->post('/marketing/coupons/{id}/toggle', [MarketingController::class, 'toggleCoupon'], ['csrf']);

    $router->get('/content', function () { redirect('/admin/content/blog'); });

    $router->get('/content/blog', [ContentController::class, 'blogIndex']);
    $router->get('/content/blog/create', [ContentController::class, 'blogCreate']);
    $router->post('/content/blog', [ContentController::class, 'blogStore'], ['csrf']);
    $router->get('/content/blog/{id}/edit', [ContentController::class, 'blogEdit']);
    $router->post('/content/blog/{id}', [ContentController::class, 'blogUpdate'], ['csrf']);
    $router->post('/content/blog/{id}/delete', [ContentController::class, 'blogDestroy'], ['csrf']);
    $router->get('/content/blog/categories', [ContentController::class, 'blogCategories']);
    $router->post('/content/blog/categories', [ContentController::class, 'storeBlogCategory'], ['csrf']);

    $router->get('/content/pages', [ContentController::class, 'pages']);
    $router->get('/content/pages/create', [ContentController::class, 'pageCreate']);
    $router->post('/content/pages', [ContentController::class, 'pageStore'], ['csrf']);
    $router->get('/content/pages/{id}/edit', [ContentController::class, 'pageEdit']);
    $router->post('/content/pages/{id}', [ContentController::class, 'pageUpdate'], ['csrf']);
    $router->post('/content/pages/{id}/delete', [ContentController::class, 'pageDestroy'], ['csrf']);

    $router->get('/content/faqs', [ContentController::class, 'faqs']);
    $router->post('/content/faqs', [ContentController::class, 'storeFaq'], ['csrf']);
    $router->post('/content/faqs/{id}', [ContentController::class, 'updateFaq'], ['csrf']);
    $router->post('/content/faqs/{id}/delete', [ContentController::class, 'destroyFaq'], ['csrf']);

    $router->get('/content/testimonials', [ContentController::class, 'testimonials']);
    $router->post('/content/testimonials', [ContentController::class, 'storeTestimonial'], ['csrf']);
    $router->post('/content/testimonials/{id}/toggle', [ContentController::class, 'toggleTestimonial'], ['csrf']);
    $router->post('/content/testimonials/{id}/delete', [ContentController::class, 'destroyTestimonial'], ['csrf']);

    $router->get('/content/banners', [ContentController::class, 'banners']);
    $router->post('/content/banners', [ContentController::class, 'storeBanner'], ['csrf']);
    $router->post('/content/banners/{id}/toggle', [ContentController::class, 'toggleBanner'], ['csrf']);
    $router->post('/content/banners/{id}/delete', [ContentController::class, 'destroyBanner'], ['csrf']);

    $router->get('/settings', [SettingsController::class, 'index']);
    $router->post('/settings', [SettingsController::class, 'update'], ['csrf']);

    $router->get('/roles', [RoleController::class, 'index']);
    $router->post('/roles/{id}', [RoleController::class, 'update'], ['csrf']);

    $router->get('/activity', [ActivityController::class, 'index']);

    $router->get('/security', [SecurityController::class, 'index']);
    $router->post('/security/2fa/setup', [SecurityController::class, 'setup'], ['csrf']);
    $router->post('/security/2fa/confirm', [SecurityController::class, 'confirm'], ['csrf']);
    $router->post('/security/2fa/disable', [SecurityController::class, 'disable'], ['csrf']);

    $router->get('/services', function () { redirect('/admin/services/staff'); });
    $router->get('/services/staff', [ServiceController::class, 'staff']);
    $router->get('/services/staff/create', [ServiceController::class, 'createStaff']);
    $router->post('/services/staff', [ServiceController::class, 'storeStaff'], ['csrf']);
    $router->get('/services/staff/{id}/edit', [ServiceController::class, 'editStaff']);
    $router->post('/services/staff/{id}', [ServiceController::class, 'updateStaff'], ['csrf']);
    $router->post('/services/staff/{id}/blackout', [ServiceController::class, 'storeBlackoutDate'], ['csrf']);
    $router->post('/services/staff/{id}/blackout/{blackoutId}/delete', [ServiceController::class, 'destroyBlackoutDate'], ['csrf']);

    $router->get('/services/services', [ServiceController::class, 'services']);
    $router->get('/services/services/create', [ServiceController::class, 'createService']);
    $router->post('/services/services', [ServiceController::class, 'storeService'], ['csrf']);
    $router->get('/services/services/{id}/edit', [ServiceController::class, 'editService']);
    $router->post('/services/services/{id}', [ServiceController::class, 'updateService'], ['csrf']);

    $router->get('/services/appointments', [ServiceController::class, 'appointments']);
    $router->post('/services/appointments/{id}/status', [ServiceController::class, 'updateAppointmentStatus'], ['csrf']);
    $router->post('/services/appointments/{id}/reschedule', [ServiceController::class, 'rescheduleAppointment'], ['csrf']);

    $router->get('/reports', function () { redirect('/admin/reports/sales'); });
    $router->get('/reports/sales', [ReportController::class, 'sales']);
    $router->get('/reports/sales/export/{format}', [ReportController::class, 'salesExport']);
    $router->get('/reports/coupons', [ReportController::class, 'coupons']);
    $router->get('/reports/coupons/export', [ReportController::class, 'couponsExport']);
    $router->get('/reports/cohorts', [ReportController::class, 'cohorts']);
    $router->get('/reports/cohorts/export', [ReportController::class, 'cohortsExport']);
    $router->get('/reports/services', [ReportController::class, 'services']);
    $router->get('/reports/services/export', [ReportController::class, 'servicesExport']);
    $router->get('/reports/inventory', [ReportController::class, 'inventory']);
    $router->get('/reports/inventory/export', [ReportController::class, 'inventoryExport']);

    $router->group(['prefix' => '/dev', 'middleware' => ['developer']], function (Router $router) {
        $router->get('/', [DeveloperController::class, 'index']);

        $router->get('/migrations', [DeveloperController::class, 'migrations']);
        $router->post('/migrations/run', [DeveloperController::class, 'runMigration'], ['csrf']);

        $router->get('/logs', [DeveloperController::class, 'logs']);
        $router->get('/queries', [DeveloperController::class, 'queries']);

        $router->get('/cron', [DeveloperController::class, 'cron']);
        $router->post('/cron/run', [DeveloperController::class, 'runCron'], ['csrf']);

        $router->get('/mail', [DeveloperController::class, 'mail']);
        $router->get('/mail/{id}/preview', [DeveloperController::class, 'mailPreview']);

        $router->get('/webhooks', [DeveloperController::class, 'webhooks']);
        $router->post('/webhooks/{id}/replay', [DeveloperController::class, 'replayWebhook'], ['csrf']);

        $router->get('/api-explorer', [DeveloperController::class, 'apiExplorer']);
        $router->get('/api-tokens', [DeveloperController::class, 'apiTokens']);
        $router->post('/api-tokens', [DeveloperController::class, 'storeApiToken'], ['csrf']);
        $router->post('/api-tokens/{id}/revoke', [DeveloperController::class, 'revokeApiToken'], ['csrf']);

        $router->get('/feature-flags', [DeveloperController::class, 'featureFlags']);
        $router->post('/feature-flags', [DeveloperController::class, 'storeFeatureFlag'], ['csrf']);
        $router->post('/feature-flags/{id}/toggle', [DeveloperController::class, 'toggleFeatureFlag'], ['csrf']);

        $router->get('/backups', [DeveloperController::class, 'backups']);
        $router->post('/backups/create', [DeveloperController::class, 'createBackup'], ['csrf']);
        $router->get('/backups/{filename}/download', [DeveloperController::class, 'downloadBackup']);
        $router->post('/backups/{filename}/restore', [DeveloperController::class, 'restoreBackup'], ['csrf']);

        $router->get('/health', [DeveloperController::class, 'health']);

        $router->post('/reset-demo-data', [DeveloperController::class, 'resetDemoData'], ['csrf']);
    });
});
