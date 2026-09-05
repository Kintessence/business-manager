<?php
declare(strict_types=1);

namespace BusinessEngine;

use BusinessEngine\Database\Schema;
use BusinessEngine\Vocabulary\VocabularyModule;
use BusinessEngine\BusinessProfile\BusinessProfileModule;
use BusinessEngine\Ingredients\IngredientsModule;
use BusinessEngine\Recipes\RecipesModule;
use BusinessEngine\Products\ProductsModule;
use BusinessEngine\Customers\CustomersModule;
use BusinessEngine\Orders\OrdersModule;

final class Core
{
    private static ?self $instance = null;
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getInstance(): self
    {
        return self::instance();
    }

    private function __construct() {}

    public function boot(): void
    {
        Schema::createTables();
        $this->registerModules();
        $this->initModules();
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    private function registerModules(): void
    {
        $modules = [
            VocabularyModule::class,
            BusinessProfileModule::class,
            IngredientsModule::class,
            RecipesModule::class,
            ProductsModule::class,
            CustomersModule::class,
            OrdersModule::class,
        ];

        foreach ($modules as $modClass) {
            if (class_exists($modClass)) {
                $this->modules[$modClass] = new $modClass();
            }
        }
    }

    private function initModules(): void
    {
        foreach ($this->modules as $module) {
            $module->init();
        }
    }

    public function enqueueAssets(string $hook): void
    {
        if (strpos($hook, 'be-') === false && strpos($hook, 'business-engine') === false) {
            return;
        }

        wp_enqueue_style('be-tokens', BE_PLUGIN_URL . 'assets/css/tokens.css', [], BE_VERSION);
        wp_enqueue_script('be-scripts', BE_PLUGIN_URL . 'assets/js/scripts.js', ['jquery'], BE_VERSION, true);

        wp_localize_script('be-scripts', 'beSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('be_secure_nonce'),
        ]);
    }
}