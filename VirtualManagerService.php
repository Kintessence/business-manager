<?php
declare(strict_types=1);

namespace BusinessEngine\Manager\Services;

final class VirtualManagerService
{
    public static function analyzeBusiness(): array
    {
        global $wpdb;

        $profile = get_option('be_business_profile', [
            'salary' => 3000.0,
            'fixed_costs' => 600.0,
            'hours_day' => 6.0,
            'days_week' => 5,
            'margin' => 25.0
        ]);

        $salary = (float)($profile['salary'] ?? 3000.0);
        $fixed = (float)($profile['fixed_costs'] ?? 600.0);
        $totalStructuralCost = $salary + $fixed;

        // Horas e Custo do Minuto
        $hoursDay = (float)($profile['hours_day'] ?? 6.0);
        $daysWeek = (float)($profile['days_week'] ?? 5.0);
        $monthlyHours = max(1.0, ($hoursDay * $daysWeek * 4.333) * 0.85);
        $cHour = $totalStructuralCost / $monthlyHours;
        $cMin = $cHour / 60.0;

        // Ponto de Equilíbrio
        $targetMargin = (float)($profile['margin'] ?? 25.0);
        $variableDeductions = $targetMargin + 6.0 + 3.5;
        $divisor = max(0.05, (100.0 - $variableDeductions) / 100.0);
        $monthlyBreakEven = $totalStructuralCost / 0.65;

        // Dados do Banco de Pedidos
        $totalOrders = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders");
        $paidRevenue = (float)$wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}be_orders WHERE payment_status = 'paid' OR payment_status = 'Pago'");
        $unpaidRevenue = (float)$wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}be_orders WHERE payment_status != 'paid' AND payment_status != 'Pago'");
        $unpaidCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders WHERE payment_status != 'paid' AND payment_status != 'Pago'");

        // Contagens gerais
        $totalSupplies = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_supplies");
        $totalRecipes = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_recipes");
        $totalProducts = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_products");
        $totalCustomers = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_customers");

        $avgTicket = $totalOrders > 0 ? (($paidRevenue + $unpaidRevenue) / $totalOrders) : 0.0;

        // Gerador de Alertas do Gestor Virtual
        $alerts = [];

        if ($unpaidCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Contas a Receber / Pendências',
                'text' => "Você possui {$unpaidCount} pedido(s) não pagos, totalizando R$ " . number_format($unpaidRevenue, 2, ',', '.') . " a receber."
            ];
        }

        if ($totalSupplies > 0 && $totalRecipes === 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => '💡',
                'title' => 'Criação de Fichas Técnicas',
                'text' => "Seus insumos já foram carregados ({$totalSupplies} itens), mas nenhuma ficha técnica foi cadastrada ainda. Cadastre receitas para apurar custos de porção."
            ];
        }

        if ($totalProducts === 0 && $totalRecipes > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => '📦',
                'title' => 'Composição Comercial',
                'text' => "Monte seus produtos finais combinando as fichas técnicas com embalagens para habilitar a formação de preço de venda com markup inteligente."
            ];
        }

        if ($paidRevenue >= $monthlyBreakEven) {
            $alerts[] = [
                'type' => 'success',
                'icon' => '🎯',
                'title' => 'Ponto de Equilíbrio Superado',
                'text' => 'O faturamento registrado superou o custo mínimo estrutural mensal estimado (R$ ' . number_format($monthlyBreakEven, 2, ',', '.') . ').'
            ];
        }

        return [
            'metrics' => [
                'salary' => $salary,
                'fixed_costs' => $fixed,
                'total_structural' => $totalStructuralCost,
                'cost_hour' => $cHour,
                'cost_min' => $cMin,
                'breakeven_monthly' => $monthlyBreakEven,
                'total_orders' => $totalOrders,
                'paid_revenue' => $paidRevenue,
                'unpaid_revenue' => $unpaidRevenue,
                'unpaid_count' => $unpaidCount,
                'avg_ticket' => $avgTicket,
                'total_supplies' => $totalSupplies,
                'total_recipes' => $totalRecipes,
                'total_products' => $totalProducts,
                'total_customers' => $totalCustomers,
            ],
            'alerts' => $alerts,
        ];
    }
}