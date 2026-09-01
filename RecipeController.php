<?php
declare(strict_types=1);

namespace BusinessEngine\Gastronomy\Controllers;

final class RecipeController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_recipe', [self::class, 'ajaxSaveRecipe']);
        add_action('wp_ajax_be_delete_recipe', [self::class, 'ajaxDeleteRecipe']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Fichas Técnicas & Receitas',
            '📋 Fichas Técnicas',
            'manage_options',
            'be-recipes',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;
        $action = sanitize_key($_GET['action'] ?? 'list');
        $id = (int)($_GET['id'] ?? 0);

        if ($action === 'new' || $action === 'edit') {
            $recipe = null;
            $items = [];
            if ($id > 0) {
                $recipe = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}be_recipes WHERE id = %d", $id
                ));
                $items = $wpdb->get_results($wpdb->prepare(
                    "SELECT ri.*, s.name as supply_name, s.pkg_cost, s.pkg_size, s.unit_type, s.use_unit 
                     FROM {$wpdb->prefix}be_recipe_items ri 
                     JOIN {$wpdb->prefix}be_supplies s ON ri.supply_id = s.id 
                     WHERE ri.recipe_id = %d", $id
                ));
            }

            $supplies = $wpdb->get_results("SELECT id, name, pkg_cost, pkg_size, unit_type, use_unit FROM {$wpdb->prefix}be_supplies ORDER BY name ASC");
            $profile = get_option('be_business_profile', ['salary' => 3000, 'fixed_costs' => 600, 'hours_day' => 6, 'days_week' => 5]);
            
            $monthlyHours = max(1.0, (($profile['hours_day'] ?? 6) * ($profile['days_week'] ?? 5) * 4.333) * 0.85);
            $totalCost = ($profile['salary'] ?? 3000) + ($profile['fixed_costs'] ?? 600);
            $cMin = ($totalCost / $monthlyHours) / 60.0;

            include BE_PLUGIN_DIR . 'modules/Gastronomy/Views/recipe-edit.php';
            return;
        }

        $recipes = $wpdb->get_results("
            SELECT r.*, COUNT(ri.id) as total_ingredients 
            FROM {$wpdb->prefix}be_recipes r 
            LEFT JOIN {$wpdb->prefix}be_recipe_items ri ON r.id = ri.recipe_id 
            GROUP BY r.id 
            ORDER BY r.name ASC
        ");

        include BE_PLUGIN_DIR . 'modules/Gastronomy/Views/recipes-list.php';
    }

    public static function ajaxSaveRecipe(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $yieldQty = max(0.01, (float)($_POST['yield_qty'] ?? 1.0));
        $yieldUnit = sanitize_text_field($_POST['yield_unit'] ?? 'un');
        $prepTime = max(0, (int)($_POST['prep_time_min'] ?? 0));
        $bakeTime = max(0, (int)($_POST['bake_time_min'] ?? 0));
        $items = $_POST['items'] ?? [];

        if (empty($name)) {
            wp_send_json_error(['message' => 'Nome da receita é obrigatório.'], 400);
        }

        $wpdb->query('START TRANSACTION');

        try {
            $recipeData = [
                'name' => $name,
                'yield_qty' => $yieldQty,
                'yield_unit' => $yieldUnit,
                'prep_time_min' => $prepTime,
                'bake_time_min' => $bakeTime,
            ];

            if ($id > 0) {
                $wpdb->update("{$wpdb->prefix}be_recipes", $recipeData, ['id' => $id]);
                $wpdb->delete("{$wpdb->prefix}be_recipe_items", ['recipe_id' => $id]);
                $recipeId = $id;
            } else {
                $wpdb->insert("{$wpdb->prefix}be_recipes", $recipeData);
                $recipeId = (int)$wpdb->insert_id;
            }

            if (!empty($items) && is_array($items)) {
                foreach ($items as $item) {
                    $supplyId = (int)($item['supply_id'] ?? 0);
                    $qty = (float)($item['quantity'] ?? 0);
                    $measure = sanitize_text_field($item['measure_type'] ?? 'g');

                    if ($supplyId > 0 && $qty > 0) {
                        $wpdb->insert("{$wpdb->prefix}be_recipe_items", [
                            'recipe_id' => $recipeId,
                            'supply_id' => $supplyId,
                            'quantity' => $qty,
                            'measure_type' => $measure,
                        ]);
                    }
                }
            }

            $wpdb->query('COMMIT');
            wp_send_json_success(['recipe_id' => $recipeId, 'redirect' => admin_url('admin.php?page=be-recipes')]);
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    public static function ajaxDeleteRecipe(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_recipe_items", ['recipe_id' => $id]);
            $wpdb->delete("{$wpdb->prefix}be_recipes", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}