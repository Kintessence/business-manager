<?php
declare(strict_types=1);

namespace BusinessEngine\Concierge\Controllers;

final class ConciergeController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_concierge_step', [self::class, 'ajaxSaveStep']);
    }

    public static function registerMenu(): void
    {
        add_menu_page(
            'Business Engine',
            'Business Engine',
            'manage_options',
            'business-engine',
            [self::class, 'renderConcierge'],
            'dashicons-chart-pie',
            25
        );
    }

    public static function renderConcierge(): void
    {
        $profile = get_option('be_business_profile', [
            'niche' => 'gastronomy',
            'salary' => 3000.0,
            'fixed_costs' => 600.0,
            'hours_day' => 6.0,
            'days_week' => 5,
            'margin' => 25.0,
            'setup_completed' => false
        ]);

        include BE_PLUGIN_DIR . 'modules/Concierge/Views/concierge-wizard.php';
    }

    public static function ajaxSaveStep(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        $data = $_POST['data'] ?? [];
        $current = get_option('be_business_profile', []);
        $updated = array_merge($current, $data);
        
        update_option('be_business_profile', $updated);
        wp_send_json_success(['profile' => $updated]);
    }
}
