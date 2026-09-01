<?php
declare(strict_types=1);

namespace BusinessEngine\Products\Controllers;

final class ProductController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_product', [self::class, 'ajaxSaveProduct']);
        add_action('wp_ajax_be_delete_product', [self::class, 'ajaxDeleteProduct']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Catálogo de Produtos & Precificação',
            '🏷️ Produtos Finais',
            'manage_options',
            'be-products',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;
        $action = sanitize_key($_GET['action'] ?? 'list');
        $id = (int)($_GET['id'] ?? 0);

        $profile = get_option('be_business_profile', ['salary' => 3000.0, 'fixed_costs' => 600.0, 'hours_day' => 6.0, 'days_week' => 5, 'margin' => 25.0]);
        $monthlyHours = max(1.0, (($profile['hours_day'] ?? 6) * ($profile['days_week'] ?? 5) * 4.333) * 0.85);
        $totalCost = ($profile['salary'] ?? 3000) + ($profile['fixed_costs'] ?? 600);
        $cMin = ($totalCost / $monthlyHours) / 60.0;

        if ($action === 'new' || $action === 'edit') {
            $product = null;
            $items = [];
            if ($id > 0) {
                $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_products WHERE id = %d", $id));
                $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_product_items WHERE product_id = %d ORDER BY id ASC", $id));
            }

            $supplies = $wpdb->get_results("SELECT id, name, pkg_cost, pkg_size, unit_type, use_unit FROM {$wpdb->prefix}be_supplies ORDER BY name ASC");
            $recipes = $wpdb->get_results("SELECT id, name, yield_qty, yield_unit, prep_time_min, bake_time_min FROM {$wpdb->prefix}be_recipes ORDER BY name ASC");

            $recipesMap = [];
            foreach ($recipes as $r) {
                $recItems = $wpdb->get_results($wpdb->prepare("
                    SELECT ri.quantity, s.pkg_cost, s.pkg_size, s.unit_type, s.use_unit 
                    FROM {$wpdb->prefix}be_recipe_items ri 
                    JOIN {$wpdb->prefix}be_supplies s ON ri.supply_id = s.id 
                    WHERE ri.recipe_id = %d", $r->id
                ));
                
                $ingCost = 0.0;
                foreach ($recItems as $ri) {
                    $normSize = (float)$ri->pkg_size;
                    if (($ri->unit_type === 'kg' && $ri->use_unit === 'g') || ($ri->unit_type === 'L' && $ri->use_unit === 'ml')) {
                        $normSize *= 1000.0;
                    }
                    $uCost = $normSize > 0 ? ((float)$ri->pkg_cost / $normSize) : 0.0;
                    $ingCost += ($uCost * (float)$ri->quantity);
                }

                $timeCost = ((int)$r->prep_time_min + (int)$r->bake_time_min) * $cMin;
                $totalBatch = $ingCost + $timeCost;
                $portionCost = $totalBatch / max(0.01, (float)$r->yield_qty);

                $recipesMap[$r->id] = [
                    'id' => $r->id,
                    'name' => $r->name,
                    'yield_unit' => $r->yield_unit,
                    'portion_cost' => $portionCost
                ];
            }

            include BE_PLUGIN_DIR . 'modules/Products/Views/product-edit.php';
            return;
        }

        $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}be_products ORDER BY name ASC");
        include BE_PLUGIN_DIR . 'modules/Products/Views/products-list.php';
    }

    public static function ajaxSaveProduct(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $sku = sanitize_text_field($_POST['sku'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'Geral');
        $strategicRole = (int)($_POST['strategic_role'] ?? 1);
        $prodTime = max(0, (int)($_POST['production_time_min'] ?? 0));
        $targetMargin = (float)($_POST['target_margin'] ?? 25.0);
        $finalPrice = (float)($_POST['final_price'] ?? 0.0);
        $items = $_POST['items'] ?? [];

        if (empty($name)) {
            wp_send_json_error(['message' => 'Nome do produto é obrigatório.'], 400);
        }

        $wpdb->query('START TRANSACTION');

        try {
            $prodData = [
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'strategic_role' => $strategicRole,
                'production_time_min' => $prodTime,
                'target_margin' => $targetMargin,
                'final_price' => $finalPrice,
            ];

            if ($id > 0) {
                $wpdb->update("{$wpdb->prefix}be_products", $prodData, ['id' => $id]);
                $wpdb->delete("{$wpdb->prefix}be_product_items", ['product_id' => $id]);
                $prodId = $id;
            } else {
                $wpdb->insert("{$wpdb->prefix}be_products", $prodData);
                $prodId = (int)$wpdb->insert_id;
            }

            if (!empty($items) && is_array($items)) {
                foreach ($items as $item) {
                    $itemType = sanitize_key($item['item_type'] ?? 'recipe');
                    $itemId = (int)($item['item_id'] ?? 0);
                    $qty = max(0.001, (float)($item['quantity'] ?? 1.0));

                    if ($itemId > 0) {
                        $wpdb->insert("{$wpdb->prefix}be_product_items", [
                            'product_id' => $prodId,
                            'item_type' => $itemType,
                            'item_id' => $itemId,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

            $wpdb->query('COMMIT');
            wp_send_json_success(['product_id' => $prodId, 'redirect' => admin_url('admin.php?page=be-products')]);
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    public static function ajaxDeleteProduct(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_product_items", ['product_id' => $id]);
            $wpdb->delete("{$wpdb->prefix}be_products", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}