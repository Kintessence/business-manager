<?php
declare(strict_types=1);

namespace BusinessEngine\Dashboard\Controllers;

use BusinessEngine\Manager\Services\VirtualManagerService;

final class DashboardController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerSubmenu']);
    }

    public static function registerSubmenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Painel Geral & Gestor Virtual',
            '📊 Dashboard Geral',
            'manage_options',
            'be-dashboard',
            [self::class, 'renderDashboard']
        );
    }

    public static function renderDashboard(): void
    {
        $analysis = VirtualManagerService::analyzeBusiness();
        $metrics = $analysis['metrics'];
        $alerts  = $analysis['alerts'];

        include BE_PLUGIN_DIR . 'modules/Dashboard/Views/dashboard-view.php';
    }
}