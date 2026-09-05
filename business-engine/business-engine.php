<?php
/**
 * Plugin Name: Business Engine - Multi-Niche Management
 * Plugin URI:  https://github.com/business-engine/engine
 * Description: Sistema modular de gestão financeira, precificação multicanal, CRM relacional e esteira operacional para micro e pequenas empresas.
 * Version: 1.4.3
 * Author:      Business Engine Team
 * Text Domain: business-engine
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

declare(strict_types=1);

namespace BusinessEngine;

if (!defined('ABSPATH')) {
    exit;
}

define('BE_VERSION', '1.4.3');
define('BE_PLUGIN_FILE', __FILE__);
define('BE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BE_MIN_PHP_VERSION', '8.1');

if (version_compare(PHP_VERSION, BE_MIN_PHP_VERSION, '<')) {
    add_action('admin_notices', function (): void {
        echo '<div class="notice notice-error"><p>' . 
             sprintf(
                 __('<strong>Business Engine:</strong> Requer PHP %s ou superior. Versão atual: %s.', 'business-engine'),
                 BE_MIN_PHP_VERSION,
                 PHP_VERSION
             ) . 
             '</p></div>';
    });
    return;
}

require_once BE_PLUGIN_DIR . 'includes/Autoloader.php';
\BusinessEngine\Autoloader::register();

register_activation_hook(__FILE__, function (): void {
    \BusinessEngine\Database\Migrator::migrate();
    set_transient('be_activation_redirect', true, 30);
});

register_deactivation_hook(__FILE__, function (): void {
    delete_transient('be_activation_redirect');
});

add_action('plugins_loaded', function (): void {
    \BusinessEngine\Core::instance()->boot();
});