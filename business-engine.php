<?php
/**
 * Plugin Name: Business Engine (Modular ERP & Pricing)
 * Plugin URI:  https://github.com/business-engine/core
 * Description: ERP modular e motor financeiro universal para microempresas com módulo vertical de Gastronomia/Confeitaria e Importador Maya.
 * Version:     3.0.0
 * Requires PHP: 8.1
 * Author:      Business Engine Team
 * Text Domain: business-engine
 */

declare(strict_types=1);

namespace BusinessEngine;

if (!defined('ABSPATH')) exit;

define('BE_VERSION', '3.0.0');
define('BE_PLUGIN_FILE', __FILE__);
define('BE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once BE_PLUGIN_DIR . 'includes/Support/Autoloader.php';
\BusinessEngine\Support\Autoloader::register();

register_activation_hook(__FILE__, function(): void {
    \BusinessEngine\Database\Migrator::run();
});

add_action('plugins_loaded', function(): void {
    \BusinessEngine\Core::getInstance()->boot();
});
