<?php
declare(strict_types=1);

namespace BusinessEngine\StreetSales\Controllers;

final class StreetSalesController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_seller_load', [self::class, 'ajaxSaveLoad']);
        add_action('wp_ajax_be_close_seller_load', [self::class, 'ajaxCloseLoad']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Vendas de Rua & Cargas',
            '🛵 Vendas de Rua',
            'manage_options',
            'be-street-sales',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;
        $action = sanitize_key($_GET['action'] ?? 'list');
        $id = (int)($_GET['id'] ?? 0);

        if ($action === 'new') {
            $products = $wpdb->get_results("SELECT id, name, final_price FROM {$wpdb->prefix}be_products ORDER BY name ASC");
            include BE_PLUGIN_DIR . 'modules/StreetSales/Views/reconcile-view.php';
            return;
        }

        if ($action === 'pos' && $id > 0) {
            $load = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_seller_loads WHERE id = %d", $id));
            $items = $wpdb->get_results($wpdb->prepare("
                SELECT li.*, p.name as product_name 
                FROM {$wpdb->prefix}be_seller_load_items li
                JOIN {$wpdb->prefix}be_products p ON li.product_id = p.id
                WHERE li.load_id = %d
            ", $id));
            include BE_PLUGIN_DIR . 'modules/StreetSales/Views/mobile-pos.php';
            return;
        }

        if ($action === 'reconcile' && $id > 0) {
            $load = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_seller_loads WHERE id = %d", $id));
            $items = $wpdb->get_results($wpdb->prepare("
                SELECT li.*, p.name as product_name 
                FROM {$wpdb->prefix}be_seller_load_items li
                JOIN {$wpdb->prefix}be_products p ON li.product_id = p.id
                WHERE li.load_id = %d
            ", $id));
            include BE_PLUGIN_DIR . 'modules/StreetSales/Views/reconcile-view.php';
            return;
        }

        $loads = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}be_seller_loads ORDER BY load_date DESC, id DESC LIMIT 50");
        include BE_PLUGIN_DIR . 'modules/StreetSales/Views/loads-list.php';
    }

    public static function ajaxSaveLoad(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $seller = sanitize_text_field($_POST['seller_name'] ?? '');
        $date = sanitize_text_field($_POST['load_date'] ?? current_time('Y-m-d'));
        $items = $_POST['items'] ?? [];

        if (empty($seller)) {
            wp_send_json_error(['message' => 'Informe o nome do vendedor/rota.'], 400);
        }

        $wpdb->insert("{$wpdb->prefix}be_seller_loads", [
            'seller_name' => $seller,
            'load_date' => $date,
            'status' => 'open',
        ]);
        $loadId = (int)$wpdb->insert_id;

        if (is_array($items)) {
            foreach ($items as $it) {
                $pId = (int)($it['product_id'] ?? 0);
                $qty = (int)($it['qty'] ?? 0);
                $price = (float)($it['price'] ?? 0.0);
                if ($pId > 0 && $qty > 0) {
                    $wpdb->insert("{$wpdb->prefix}be_seller_load_items", [
                        'load_id' => $loadId,
                        'product_id' => $pId,
                        'initial_qty' => $qty,
                        'returned_qty' => 0,
                        'unit_price' => $price,
                    ]);
                }
            }
        }

        wp_send_json_success(['redirect' => admin_url('admin.php?page=be-street-sales')]);
    }

    public static function ajaxCloseLoad(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $loadId = (int)($_POST['load_id'] ?? 0);
        $cash = (float)($_POST['cash_received'] ?? 0);
        $pix = (float)($_POST['pix_received'] ?? 0);
        $card = (float)($_POST['card_received'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $returns = $_POST['returns'] ?? [];

        if ($loadId <= 0) {
            wp_send_json_error(['message' => 'Carga inválida.'], 400);
        }

        if (is_array($returns)) {
            foreach ($returns as $itemId => $retQty) {
                $wpdb->update(
                    "{$wpdb->prefix}be_seller_load_items",
                    ['returned_qty' => (int)$retQty],
                    ['id' => (int)$itemId, 'load_id' => $loadId]
                );
            }
        }

        $wpdb->update(
            "{$wpdb->prefix}be_seller_loads",
            [
                'status' => 'closed',
                'cash_received' => $cash,
                'pix_received' => $pix,
                'card_received' => $card,
                'notes' => $notes,
            ],
            ['id' => $loadId]
        );

        wp_send_json_success(['redirect' => admin_url('admin.php?page=be-street-sales')]);
    }
}