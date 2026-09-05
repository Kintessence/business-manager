<?php
declare(strict_types=1);

namespace BusinessEngine\Database;

final class Migrator
{
    public const DB_VERSION = '2.0.0';

    public static function migrate(): void
    {
        global $wpdb;
        $installedVersion = get_option('be_db_version', '0.0.0');

        if (version_compare($installedVersion, self::DB_VERSION, '>=')) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charsetCollate = $wpdb->get_charset_collate();
        $p = $wpdb->prefix . 'be_';

        $queries = [
            "CREATE TABLE {$p}supplies (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(100) NOT NULL DEFAULT 'Ingrediente',
                pkg_type VARCHAR(50) NOT NULL DEFAULT 'Pacote',
                pkg_size DECIMAL(12,4) NOT NULL DEFAULT 1000.0000,
                unit_type VARCHAR(20) NOT NULL DEFAULT 'g',
                use_unit VARCHAR(20) NOT NULL DEFAULT 'g',
                pkg_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                loss_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                allergens VARCHAR(255) NULL DEFAULT '',
                stock_qty DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                min_stock DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                metadata LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_supplies_cat (category),
                KEY idx_supplies_name (name(191))
            ) {$charsetCollate};",

            "CREATE TABLE {$p}recipes (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(100) NOT NULL DEFAULT 'Geral',
                yield_qty DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
                yield_unit VARCHAR(50) NOT NULL DEFAULT 'porções',
                prep_time_minutes INT UNSIGNED NOT NULL DEFAULT 0,
                labor_cost_calculated DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                supplies_cost_calculated DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                total_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                unit_cost DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
                instructions LONGTEXT NULL,
                metadata LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_recipes_cat (category)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}recipe_items (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                recipe_id BIGINT UNSIGNED NOT NULL,
                supply_id BIGINT UNSIGNED NOT NULL,
                quantity DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                unit VARCHAR(20) NOT NULL DEFAULT 'g',
                unit_cost_snapshot DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
                subtotal_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                PRIMARY KEY (id),
                KEY idx_ri_recipe (recipe_id),
                KEY idx_ri_supply (supply_id)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}products (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                sku VARCHAR(100) NULL,
                strategic_role VARCHAR(50) NOT NULL DEFAULT 'carro_chefe',
                direct_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                final_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                target_margin_pct DECIMAL(5,2) NOT NULL DEFAULT 25.00,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                stock_qty DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                metadata LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_products_role (strategic_role),
                KEY idx_products_active (is_active)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}product_items (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id BIGINT UNSIGNED NOT NULL,
                item_type VARCHAR(20) NOT NULL DEFAULT 'recipe',
                item_id BIGINT UNSIGNED NOT NULL,
                quantity DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
                unit_cost_snapshot DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
                subtotal_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                PRIMARY KEY (id),
                KEY idx_pi_product (product_id),
                KEY idx_pi_target (item_type, item_id)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}customers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NULL,
                has_whatsapp TINYINT(1) NOT NULL DEFAULT 1,
                email VARCHAR(191) NULL,
                address TEXT NULL,
                default_discount DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                orders_count INT UNSIGNED NOT NULL DEFAULT 0,
                amount_spent DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                metadata LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_customers_phone (phone),
                KEY idx_customers_name (name(191))
            ) {$charsetCollate};",

            "CREATE TABLE {$p}delivery_zones (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(150) NOT NULL,
                fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                estimated_minutes INT UNSIGNED NOT NULL DEFAULT 30,
                PRIMARY KEY (id)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}orders (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sequential_id BIGINT UNSIGNED NULL,
                customer_id BIGINT UNSIGNED NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50) NULL,
                order_type VARCHAR(50) NOT NULL DEFAULT 'retirada',
                delivery_zone_id BIGINT UNSIGNED NULL,
                delivery_address TEXT NULL,
                delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                schedule_at DATETIME NULL,
                order_reason VARCHAR(255) NULL,
                items_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                discount_type VARCHAR(20) NOT NULL DEFAULT 'fixed',
                discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                payment_method VARCHAR(50) NOT NULL DEFAULT 'pix',
                payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
                production_status VARCHAR(50) NOT NULL DEFAULT 'agendado',
                notes TEXT NULL,
                metadata LONGTEXT NULL,
                order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_orders_customer (customer_id),
                KEY idx_orders_schedule (schedule_at),
                KEY idx_orders_prod_status (production_status),
                KEY idx_orders_pay_status (payment_status)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}order_items (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
                unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (id),
                KEY idx_oi_order (order_id),
                KEY idx_oi_product (product_id)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}seller_loads (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                seller_name VARCHAR(150) NOT NULL,
                load_date DATE NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'open',
                total_expected DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total_received DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                difference DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_sl_date (load_date),
                KEY idx_sl_status (status)
            ) {$charsetCollate};",

            "CREATE TABLE {$p}seller_load_items (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                load_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                dispatched_qty DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                returned_qty DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                sold_qty DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (id),
                KEY idx_sli_load (load_id)
            ) {$charsetCollate};"
        ];

        foreach ($queries as $sql) {
            dbDelta($sql);
        }

        update_option('be_db_version', self::DB_VERSION);
    }
}