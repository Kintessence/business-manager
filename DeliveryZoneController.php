<?php
declare(strict_types=1);

namespace BusinessEngine\Orders\Controllers;

final class DeliveryZoneController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_delivery_zone', [self::class, 'ajaxSaveZone']);
        add_action('wp_ajax_be_delete_delivery_zone', [self::class, 'ajaxDeleteZone']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Tabela de Zonas & Frete',
            '🚚 Zonas de Entrega',
            'manage_options',
            'be-delivery-zones',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;
        $zones = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}be_delivery_zones ORDER BY fee ASC, name ASC");
        include BE_PLUGIN_DIR . 'modules/Orders/Views/delivery-zones.php';
    }

    public static function ajaxSaveZone(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $fee = max(0.0, (float)($_POST['fee'] ?? 0.0));

        if (empty($name)) {
            wp_send_json_error(['message' => 'Informe o nome do bairro ou região.'], 400);
        }

        if ($id > 0) {
            $wpdb->update("{$wpdb->prefix}be_delivery_zones", ['name' => $name, 'fee' => $fee], ['id' => $id]);
        } else {
            $wpdb->insert("{$wpdb->prefix}be_delivery_zones", ['name' => $name, 'fee' => $fee, 'active' => 1]);
        }

        wp_send_json_success(['message' => 'Zona salva com sucesso!']);
    }

    public static function ajaxDeleteZone(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_delivery_zones", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}