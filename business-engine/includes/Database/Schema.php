<?php
declare(strict_types=1);

namespace BusinessEngine\Database;

final class Schema
{
    public static function createTables(): void
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "
        CREATE TABLE {$wpdb->prefix}be_supplies (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            category VARCHAR(50) DEFAULT 'Ingrediente',
            pkg_type VARCHAR(50) DEFAULT 'Unidade',
            pkg_size DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
            unit_type VARCHAR(20) NOT NULL DEFAULT 'un',
            use_unit VARCHAR(20) NOT NULL DEFAULT 'un',
            pkg_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            loss_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            unit_cost_calculated DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
            allergens TEXT,
            metadata LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_supply_cat (category),
            KEY idx_supply_name (name)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_recipes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            category VARCHAR(50) DEFAULT 'Geral',
            yield_qty DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            yield_unit VARCHAR(50) DEFAULT 'porções',
            prep_time_minutes INT UNSIGNED NOT NULL DEFAULT 0,
            labor_cost_calculated DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            supplies_cost_calculated DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            total_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            unit_cost DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
            instructions LONGTEXT,
            metadata LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_recipe_name (name),
            KEY idx_recipe_cat (category)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_recipe_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            recipe_id BIGINT UNSIGNED NOT NULL,
            supply_id BIGINT UNSIGNED NOT NULL,
            quantity DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            unit VARCHAR(20) NOT NULL DEFAULT 'g',
            unit_cost_snapshot DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
            subtotal_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY  (id),
            KEY idx_rec_item (recipe_id, supply_id)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_products (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            sku VARCHAR(50) DEFAULT NULL,
            strategic_role VARCHAR(50) DEFAULT 'carro_chefe',
            direct_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            final_price DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            target_margin_pct DECIMAL(5,2) NOT NULL DEFAULT 25.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            stock_qty DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            metadata LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_prod_name (name),
            KEY idx_prod_role (strategic_role)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_product_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT UNSIGNED NOT NULL,
            item_type VARCHAR(20) NOT NULL DEFAULT 'recipe',
            item_id BIGINT UNSIGNED NOT NULL,
            quantity DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
            unit_cost_snapshot DECIMAL(12,6) NOT NULL DEFAULT 0.000000,
            subtotal_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY  (id),
            KEY idx_prod_item (product_id, item_id)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_customers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            phone VARCHAR(50) DEFAULT '',
            channel VARCHAR(50) DEFAULT 'whatsapp',
            address VARCHAR(255) DEFAULT '',
            neighborhood VARCHAR(100) DEFAULT '',
            notes TEXT,
            metadata LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_cust_phone (phone),
            KEY idx_cust_name (name)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_orders (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id BIGINT UNSIGNED NOT NULL,
            order_number VARCHAR(50) NOT NULL,
            status VARCHAR(30) DEFAULT 'pendente',
            delivery_date DATE NULL,
            delivery_time VARCHAR(20) DEFAULT '',
            delivery_type VARCHAR(30) DEFAULT 'entrega',
            subtotal_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            payment_method VARCHAR(50) DEFAULT 'pix',
            is_paid TINYINT(1) NOT NULL DEFAULT 0,
            notes TEXT,
            metadata LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_order_cust (customer_id),
            KEY idx_order_status (status),
            KEY idx_order_deliv (delivery_date)
        ) $charsetCollate;

        CREATE TABLE {$wpdb->prefix}be_order_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            unit_price_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY  (id),
            KEY idx_ord_item (order_id, product_id)
        ) $charsetCollate;
        ";

        dbDelta($sql);
    }
}