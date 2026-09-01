<?php
declare(strict_types=1);

namespace BusinessEngine\Gastronomy;

final class GastronomyModule
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_update_supply', [self::class, 'ajaxUpdateSupply']);
        add_action('wp_ajax_be_delete_supply', [self::class, 'ajaxDeleteSupply']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Insumos & Embalagens',
            '🧂 Insumos',
            'manage_options',
            'be-supplies',
            [self::class, 'renderSuppliesPage']
        );
    }

    public static function renderSuppliesPage(): void
    {
        global $wpdb;
        $search = sanitize_text_field($_GET['s'] ?? '');
        $paged = max(1, (int)($_GET['paged'] ?? 1));
        $perPage = 25;
        $offset = ($paged - 1) * $perPage;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(" WHERE name LIKE %s OR category LIKE %s", "%{$search}%", "%{$search}%");
        }

        $totalFiltered = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_supplies {$where}");
        $supplies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}be_supplies {$where} ORDER BY name ASC LIMIT {$offset}, {$perPage}");
        $totalPages = max(1, (int)ceil($totalFiltered / $perPage));

        include BE_PLUGIN_DIR . 'modules/Gastronomy/Views/supplies-list.php';
    }

    public static function ajaxUpdateSupply(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'Geral');
        $pkgSize = max(0.0001, (float)($_POST['pkg_size'] ?? 1.0));
        $pkgCost = max(0.0, (float)($_POST['pkg_cost'] ?? 0.0));
        $unitType = sanitize_text_field($_POST['unit_type'] ?? 'g');
        $lossPct = max(0.0, min(99.0, (float)($_POST['loss_pct'] ?? 0.0)));
        $allergens = sanitize_text_field($_POST['allergens'] ?? '');

        if (empty($name)) {
            wp_send_json_error(['message' => 'O nome do insumo é obrigatório.'], 400);
        }

        // Sugestão de Unidade de Uso
        $useUnit = $unitType === 'kg' ? 'g' : ($unitType === 'L' ? 'ml' : $unitType);

        $data = [
            'name' => $name,
            'category' => $category,
            'pkg_size' => $pkgSize,
            'pkg_cost' => $pkgCost,
            'unit_type' => $unitType,
            'use_unit' => $useUnit,
            'loss_pct' => $lossPct,
            'allergens' => $allergens,
        ];

        if ($id > 0) {
            $wpdb->update("{$wpdb->prefix}be_supplies", $data, ['id' => $id]);
        } else {
            $wpdb->insert("{$wpdb->prefix}be_supplies", $data);
            $id = (int)$wpdb->insert_id;
        }

        wp_send_json_success(['id' => $id, 'message' => 'Insumo salvo com sucesso!']);
    }

    public static function ajaxDeleteSupply(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_supplies", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}