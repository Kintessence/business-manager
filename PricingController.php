<?php
declare(strict_types=1);

namespace BusinessEngine\Pricing\Controllers;

final class PricingController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Simulador Multicanal & Margens',
            '🧭 Simulador & Canais',
            'manage_options',
            'be-pricing-simulator',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;

        $profile = get_option('be_business_profile', [
            'salary' => 3000.0,
            'fixed_costs' => 600.0,
            'hours_day' => 6.0,
            'days_week' => 5,
            'margin' => 25.0
        ]);

        $monthlyHours = max(1.0, (($profile['hours_day'] ?? 6) * ($profile['days_week'] ?? 5) * 4.333) * 0.85);
        $totalCost = ($profile['salary'] ?? 3000) + ($profile['fixed_costs'] ?? 600);
        $cMin = ($totalCost / $monthlyHours) / 60.0;

        // Buscar Produtos Finais com Preços e Custos
        $products = $wpdb->get_results("SELECT id, name, final_price, production_time_min, target_margin FROM {$wpdb->prefix}be_products ORDER BY name ASC");

        // Buscar Fichas Técnicas com Cálculo de Custo Unitário
        $recipes = $wpdb->get_results("SELECT id, name, yield_qty, yield_unit, prep_time_min, bake_time_min FROM {$wpdb->prefix}be_recipes ORDER BY name ASC");
        $recipesList = [];

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
            $portionCost = ($ingCost + $timeCost) / max(0.01, (float)$r->yield_qty);

            $recipesList[] = [
                'id' => $r->id,
                'name' => $r->name,
                'portion_cost' => round($portionCost, 2),
                'yield_unit' => $r->yield_unit
            ];
        }

        $defaultChannels = [
            ['name' => 'Balcão / PIX', 'fee' => 0.0],
            ['name' => 'Cartão de Débito', 'fee' => 1.8],
            ['name' => 'Cartão de Crédito (1x)', 'fee' => 3.5],
            ['name' => 'iFood (Entrega Própria)', 'fee' => 12.0],
            ['name' => 'iFood (Entrega Parceira)', 'fee' => 27.0],
            ['name' => 'Marketplace Geral', 'fee' => 18.0],
        ];

        include BE_PLUGIN_DIR . 'modules/Pricing/Views/pricing-simulator.php';
    }
}