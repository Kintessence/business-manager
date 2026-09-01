<?php
declare(strict_types=1);

namespace BusinessEngine\Backup\Services;

final class BackupService
{
    private static array $tables = [
        'be_supplies',
        'be_recipes',
        'be_recipe_items',
        'be_products',
        'be_product_items',
        'be_customers',
        'be_orders',
        'be_order_items',
        'be_delivery_zones',
        'be_seller_loads',
        'be_seller_load_items',
    ];

    public static function exportData(): array
    {
        global $wpdb;

        $export = [
            'meta' => [
                'generator'  => 'BusinessEngine / KitchenManager',
                'version'    => defined('BE_VERSION') ? BE_VERSION : '1.0.0',
                'created_at' => current_time('mysql'),
                'site_url'   => site_url(),
            ],
            'options' => [
                'be_business_profile' => get_option('be_business_profile', []),
                'be_version'          => get_option('be_version', '1.0.0'),
            ],
            'tables' => [],
        ];

        foreach (self::$tables as $table) {
            $tableName = $wpdb->prefix . $table;
            $tableExists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tableName));
            
            if ($tableExists === $tableName) {
                $rows = $wpdb->get_results("SELECT * FROM {$tableName}", ARRAY_A);
                $export['tables'][$table] = $rows ?: [];
            }
        }

        return $export;
    }

    public static function restoreData(string $jsonContent): array
    {
        global $wpdb;

        $data = json_decode($jsonContent, true);
        if (!is_array($data) || !isset($data['tables'])) {
            return ['success' => false, 'message' => 'Arquivo de backup inválido ou corrompido.'];
        }

        $wpdb->query('START TRANSACTION');

        try {
            // 1. Restaura opções do negócio
            if (isset($data['options']['be_business_profile'])) {
                update_option('be_business_profile', $data['options']['be_business_profile']);
            }

            // 2. Restaura tabelas
            $restoredCounts = [];
            foreach (self::$tables as $table) {
                if (!isset($data['tables'][$table])) {
                    continue;
                }

                $tableName = $wpdb->prefix . $table;
                $wpdb->query("TRUNCATE TABLE {$tableName}");

                $rows = $data['tables'][$table];
                $count = 0;
                foreach ($rows as $row) {
                    $wpdb->insert($tableName, $row);
                    $count++;
                }
                $restoredCounts[$table] = $count;
            }

            $wpdb->query('COMMIT');

            return [
                'success' => true,
                'message' => 'Banco de dados restaurado com sucesso!',
                'counts'  => $restoredCounts,
            ];
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            return ['success' => false, 'message' => 'Erro ao restaurar banco: ' . $e->getMessage()];
        }
    }
}