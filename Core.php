<?php
declare(strict_types=1);

namespace BusinessEngine;

final class Core
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct() {}

    public function boot(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        
        \BusinessEngine\Concierge\Controllers\ConciergeController::init();
        \BusinessEngine\Gastronomy\GastronomyModule::init();
        \BusinessEngine\Gastronomy\Controllers\RecipeController::init();
        \BusinessEngine\Products\Controllers\ProductController::init();
        \BusinessEngine\Pricing\Controllers\PricingController::init();
        \BusinessEngine\Customers\Controllers\CustomerController::init();
        \BusinessEngine\Orders\Controllers\OrderController::init();
        \BusinessEngine\Orders\Controllers\DeliveryZoneController::init();
        \BusinessEngine\StreetSales\Controllers\StreetSalesController::init();
        \BusinessEngine\Backup\Controllers\BackupController::init();
        \BusinessEngine\CsvImport\Controllers\CsvImportController::init();
        \BusinessEngine\Dashboard\Controllers\DashboardController::init();
    }

    public function enqueueAssets(string $hook): void
    {
        if (!str_contains($hook, 'business-engine') && !str_contains($hook, 'be-')) {
            return;
        }

        wp_enqueue_style('be-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
        wp_enqueue_style('be-tokens', BE_PLUGIN_URL . 'assets/css/tokens.css', [], BE_VERSION);
        wp_enqueue_script('be-app', BE_PLUGIN_URL . 'assets/js/be-app.js', ['jquery'], BE_VERSION, true);
        wp_localize_script('be-app', 'beSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('be_secure_nonce'),
        ]);
    }
}