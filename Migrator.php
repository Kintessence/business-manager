<?php
declare(strict_types=1);

namespace BusinessEngine\Database;

final class Migrator
{
    public static function run(): void
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sqlSupplies = "CREATE TABLE {$wpdb->prefix}be_supplies (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            category VARCHAR(100) DEFAULT 'Geral',
            pkg_type VARCHAR(50) DEFAULT 'un',
            pkg_size DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
            unit_type VARCHAR(20) NOT NULL DEFAULT 'g',
            pkg_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            use_unit VARCHAR(20) NOT NULL DEFAULT 'g',
            loss_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            allergens TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        $sqlRecipes = "CREATE TABLE {$wpdb->prefix}be_recipes (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            category VARCHAR(100) DEFAULT 'Geral',
            yield_qty DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            yield_unit VARCHAR(50) DEFAULT 'un',
            prep_time_min INT UNSIGNED DEFAULT 0,
            bake_time_min INT UNSIGNED DEFAULT 0,
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        $sqlRecipeItems = "CREATE TABLE {$wpdb->prefix}be_recipe_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            recipe_id BIGINT(20) UNSIGNED NOT NULL,
            supply_id BIGINT(20) UNSIGNED NOT NULL,
            quantity DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            measure_type VARCHAR(50) NOT NULL DEFAULT 'unit',
            PRIMARY KEY (id),
            KEY recipe_id (recipe_id),
            KEY supply_id (supply_id)
        ) $charsetCollate;";

        $sqlProducts = "CREATE TABLE {$wpdb->prefix}be_products (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            sku VARCHAR(50) NULL,
            category VARCHAR(100) DEFAULT 'Geral',
            strategic_role INT DEFAULT 1,
            production_time_min INT UNSIGNED DEFAULT 0,
            target_margin DECIMAL(5,2) NOT NULL DEFAULT 25.00,
            final_price DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        $sqlProductItems = "CREATE TABLE {$wpdb->prefix}be_product_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            item_type VARCHAR(20) NOT NULL DEFAULT 'recipe',
            item_id BIGINT(20) UNSIGNED NOT NULL,
            quantity DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) $charsetCollate;";

        $sqlCustomers = "CREATE TABLE {$wpdb->prefix}be_customers (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            phone VARCHAR(50) NULL,
            has_whatsapp TINYINT(1) DEFAULT 1,
            email VARCHAR(191) NULL,
            instagram VARCHAR(100) NULL,
            address TEXT NULL,
            birthday VARCHAR(20) NULL,
            default_discount DECIMAL(5,2) DEFAULT 0.00,
            orders_count INT UNSIGNED DEFAULT 0,
            amount_spent DECIMAL(12,4) DEFAULT 0.0000,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        $sqlOrders = "CREATE TABLE {$wpdb->prefix}be_orders (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            sequential_id VARCHAR(50) NULL,
            customer_name VARCHAR(191) NOT NULL,
            customer_phone VARCHAR(50) NULL,
            has_whatsapp TINYINT(1) DEFAULT 1,
            items_subtotal DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            discount_type VARCHAR(20) DEFAULT 'fixed',
            discount_value DECIMAL(12,4) DEFAULT 0.0000,
            delivery_fee DECIMAL(12,4) DEFAULT 0.0000,
            total_amount DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            amount_paid DECIMAL(12,4) DEFAULT 0.0000,
            payment_status VARCHAR(50) DEFAULT 'unpaid',
            payment_method VARCHAR(50) DEFAULT 'pix',
            order_type VARCHAR(50) DEFAULT 'retirada',
            production_status VARCHAR(50) DEFAULT 'agendado',
            order_reason VARCHAR(100) NULL,
            schedule_at DATETIME NULL,
            delivery_address TEXT NULL,
            notes TEXT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        $sqlOrderItems = "CREATE TABLE {$wpdb->prefix}be_order_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NULL,
            product_name VARCHAR(191) NOT NULL,
            quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            unit_price DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            total_price DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) $charsetCollate;";

        $sqlDeliveryZones = "CREATE TABLE {$wpdb->prefix}be_delivery_zones (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            fee DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        dbDelta($sqlSupplies);
        dbDelta($sqlRecipes);
        dbDelta($sqlRecipeItems);
        dbDelta($sqlProducts);
        dbDelta($sqlProductItems);
        dbDelta($sqlCustomers);
        dbDelta($sqlOrders);
        dbDelta($sqlOrderItems);
        dbDelta($sqlDeliveryZones);
        
        update_option('be_version', BE_VERSION);
    }
}