<?php
declare(strict_types=1);

namespace BusinessEngine\Orders\Controllers;

final class OrderController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_order', [self::class, 'ajaxSaveOrder']);
        add_action('wp_ajax_be_delete_order', [self::class, 'ajaxDeleteOrder']);
        add_action('wp_ajax_be_get_order_details', [self::class, 'ajaxGetOrderDetails']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Histórico de Pedidos & Vendas',
            '📦 Pedidos & Vendas',
            'manage_options',
            'be-orders',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;

        $search = sanitize_text_field($_GET['s'] ?? '');
        $paged = max(1, (int)($_GET['paged'] ?? 1));
        $perPage = 25;
        $offset = ($paged - 1) * $perPage;

        $whereClause = '';
        if (!empty($search)) {
            $whereClause = $wpdb->prepare(" WHERE customer_name LIKE %s OR sequential_id LIKE %s OR notes LIKE %s OR order_reason LIKE %s", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%");
        }

        $totalFiltered = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders {$whereClause}");
        $orders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}be_orders {$whereClause} ORDER BY order_date DESC LIMIT {$offset}, {$perPage}");

        $totalOrders = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders");
        $totalRevenue = (float)$wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}be_orders WHERE payment_status = 'paid' OR payment_status = 'Pago'");
        $avgTicket = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0.0;
        $totalPages = max(1, (int)ceil($totalFiltered / $perPage));

        $productsCatalog = $wpdb->get_results("SELECT id, name, final_price FROM {$wpdb->prefix}be_products ORDER BY name ASC");
        $customersList = $wpdb->get_results("SELECT id, name, phone, has_whatsapp, address, default_discount FROM {$wpdb->prefix}be_customers ORDER BY name ASC LIMIT 200");
        $deliveryZones = $wpdb->get_results("SELECT id, name, fee FROM {$wpdb->prefix}be_delivery_zones WHERE active = 1 ORDER BY fee ASC");

        include BE_PLUGIN_DIR . 'modules/Orders/Views/orders-list.php';
    }

    public static function ajaxGetOrderDetails(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        global $wpdb;
        $id = (int)($_GET['id'] ?? 0);
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_orders WHERE id = %d", $id));
        
        $items = $wpdb->get_results($wpdb->prepare("
            SELECT 
                oi.id, oi.order_id, oi.product_id, oi.quantity, oi.unit_price, oi.total_price,
                CASE 
                    WHEN oi.product_name IS NOT NULL AND oi.product_name != '' AND oi.product_name != 'Item' THEN oi.product_name
                    WHEN p.name IS NOT NULL AND p.name != '' THEN p.name
                    ELSE CONCAT('Produto #', oi.product_id)
                END AS product_name
            FROM {$wpdb->prefix}be_order_items oi
            LEFT JOIN {$wpdb->prefix}be_products p ON oi.product_id = p.id
            WHERE oi.order_id = %d
            ORDER BY oi.id ASC
        ", $id));
        
        wp_send_json_success(['order' => $order, 'items' => $items]);
    }

    public static function ajaxSaveOrder(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $customerName = sanitize_text_field($_POST['customer_name'] ?? '');
        $customerPhone = sanitize_text_field($_POST['customer_phone'] ?? '');
        $hasWhatsapp = isset($_POST['has_whatsapp']) ? (int)$_POST['has_whatsapp'] : 1;
        $orderReason = sanitize_text_field($_POST['order_reason'] ?? '');
        $productionStatus = sanitize_text_field($_POST['production_status'] ?? 'agendado');
        $scheduleAt = sanitize_text_field($_POST['schedule_at'] ?? '');
        $orderType = sanitize_text_field($_POST['order_type'] ?? 'retirada');
        $deliveryFee = (float)($_POST['delivery_fee'] ?? 0.0);
        $deliveryAddress = sanitize_textarea_field($_POST['delivery_address'] ?? '');
        $discountType = sanitize_text_field($_POST['discount_type'] ?? 'fixed');
        $discountValue = (float)($_POST['discount_value'] ?? 0.0);
        $paymentStatus = sanitize_text_field($_POST['payment_status'] ?? 'unpaid');
        $paymentMethod = sanitize_text_field($_POST['payment_method'] ?? 'pix');
        $amountPaid = (float)($_POST['amount_paid'] ?? 0.0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];

        if (empty($customerName)) {
            wp_send_json_error(['message' => 'Informe o nome do cliente.'], 400);
        }

        $itemsSubtotal = 0.0;
        if (!empty($items) && is_array($items)) {
            foreach ($items as $it) {
                $qty = max(0.01, (float)($it['quantity'] ?? 1.0));
                $uPrice = (float)($it['unit_price'] ?? 0.0);
                $itemsSubtotal += ($qty * $uPrice);
            }
        }

        $calcDiscount = ($discountType === 'percent') ? ($itemsSubtotal * ($discountValue / 100)) : $discountValue;
        $calcDiscount = max(0.0, min($itemsSubtotal, $calcDiscount));
        $effectiveDeliveryFee = ($orderType === 'entrega') ? $deliveryFee : 0.0;
        $totalAmount = max(0.0, ($itemsSubtotal - $calcDiscount) + $effectiveDeliveryFee);

        if ($amountPaid >= $totalAmount && $totalAmount > 0) {
            $paymentStatus = 'paid';
        }

        $wpdb->query('START TRANSACTION');

        try {
            $orderData = [
                'customer_name'     => $customerName,
                'customer_phone'    => $customerPhone,
                'has_whatsapp'      => $hasWhatsapp,
                'items_subtotal'    => $itemsSubtotal,
                'discount_type'     => $discountType,
                'discount_value'    => $discountValue,
                'delivery_fee'      => $effectiveDeliveryFee,
                'total_amount'      => $totalAmount,
                'amount_paid'       => $amountPaid,
                'payment_status'    => $paymentStatus,
                'payment_method'    => $paymentMethod,
                'order_type'        => $orderType,
                'production_status' => $productionStatus,
                'order_reason'      => !empty($orderReason) ? $orderReason : null,
                'schedule_at'       => !empty($scheduleAt) ? $scheduleAt : null,
                'delivery_address'  => $deliveryAddress,
                'notes'             => $notes,
            ];

            if ($id > 0) {
                $wpdb->update("{$wpdb->prefix}be_orders", $orderData, ['id' => $id]);
                $orderId = $id;
            } else {
                $nextSeq = (int)$wpdb->get_var("SELECT MAX(CAST(sequential_id AS UNSIGNED)) FROM {$wpdb->prefix}be_orders") + 1;
                $orderData['sequential_id'] = (string)$nextSeq;
                $orderData['order_date'] = current_time('mysql');
                $wpdb->insert("{$wpdb->prefix}be_orders", $orderData);
                $orderId = (int)$wpdb->insert_id;
            }

            $wpdb->delete("{$wpdb->prefix}be_order_items", ['order_id' => $orderId]);
            if (!empty($items) && is_array($items)) {
                foreach ($items as $it) {
                    $pId = (int)($it['product_id'] ?? 0);
                    $pName = sanitize_text_field($it['product_name'] ?? 'Item');
                    $qty = max(0.01, (float)($it['quantity'] ?? 1.0));
                    $uPrice = (float)($it['unit_price'] ?? 0.0);

                    $wpdb->insert("{$wpdb->prefix}be_order_items", [
                        'order_id'     => $orderId,
                        'product_id'   => $pId > 0 ? $pId : null,
                        'product_name' => $pName,
                        'quantity'     => $qty,
                        'unit_price'   => $uPrice,
                        'total_price'  => $qty * $uPrice,
                    ]);
                }
            }

            $wpdb->query('COMMIT');
            wp_send_json_success(['message' => 'Pedido salvo com sucesso!', 'order_id' => $orderId]);
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }

    public static function ajaxDeleteOrder(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_order_items", ['order_id' => $id]);
            $wpdb->delete("{$wpdb->prefix}be_orders", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}