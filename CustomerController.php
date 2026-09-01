<?php
declare(strict_types=1);

namespace BusinessEngine\Customers\Controllers;

final class CustomerController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_be_save_customer', [self::class, 'ajaxSaveCustomer']);
        add_action('wp_ajax_be_delete_customer', [self::class, 'ajaxDeleteCustomer']);
        add_action('wp_ajax_be_update_customer_address', [self::class, 'ajaxUpdateAddress']);
        add_action('wp_ajax_be_get_customer_history', [self::class, 'ajaxGetCustomerHistory']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Gestão de Clientes & CRM',
            '👥 Clientes & CRM',
            'manage_options',
            'be-customers',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        global $wpdb;
        $action = sanitize_key($_GET['action'] ?? 'list');
        $id = (int)($_GET['id'] ?? 0);

        if ($action === 'new' || $action === 'edit') {
            $customer = null;
            if ($id > 0) {
                $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}be_customers WHERE id = %d", $id));
            }
            include BE_PLUGIN_DIR . 'modules/Customers/Views/customer-edit.php';
            return;
        }

        $search = sanitize_text_field($_GET['s'] ?? '');
        $query = "SELECT * FROM {$wpdb->prefix}be_customers";
        if (!empty($search)) {
            $query .= $wpdb->prepare(" WHERE name LIKE %s OR phone LIKE %s OR email LIKE %s", "%{$search}%", "%{$search}%", "%{$search}%");
        }
        $query .= " ORDER BY amount_spent DESC LIMIT 100";

        $customers = $wpdb->get_results($query);
        $totalCustomers = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_customers");
        $totalRevenue = (float)$wpdb->get_var("SELECT SUM(amount_spent) FROM {$wpdb->prefix}be_customers");
        $avgTicket = $totalCustomers > 0 ? ($totalRevenue / $totalCustomers) : 0.0;

        include BE_PLUGIN_DIR . 'modules/Customers/Views/customers-list.php';
    }

    public static function ajaxGetCustomerHistory(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        global $wpdb;
        $name = sanitize_text_field($_GET['name'] ?? '');
        $phone = sanitize_text_field($_GET['phone'] ?? '');

        if (empty($name) && empty($phone)) {
            wp_send_json_error(['message' => 'Cliente não identificado.']);
        }

        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT o.*, 
                (SELECT COUNT(*) FROM {$wpdb->prefix}be_order_items WHERE order_id = o.id) as items_count
            FROM {$wpdb->prefix}be_orders o
            WHERE o.customer_name = %s OR (o.customer_phone != '' AND o.customer_phone = %s)
            ORDER BY o.order_date DESC
        ", $name, $phone));

        wp_send_json_success(['orders' => $orders]);
    }

    public static function ajaxSaveCustomer(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $hasWhatsapp = isset($_POST['has_whatsapp']) ? (int)$_POST['has_whatsapp'] : 1;
        $email = sanitize_email($_POST['email'] ?? '');
        $instagram = sanitize_text_field($_POST['instagram'] ?? '');
        $birthday = sanitize_text_field($_POST['birthday'] ?? '');
        $address = sanitize_textarea_field($_POST['address'] ?? '');
        $defaultDiscount = (float)($_POST['default_discount'] ?? 0.0);
        $preferences = sanitize_textarea_field($_POST['preferences'] ?? '');

        if (empty($name)) {
            wp_send_json_error(['message' => 'Nome do cliente é obrigatório.'], 400);
        }

        $data = [
            'name'             => $name,
            'phone'            => $phone,
            'has_whatsapp'     => $hasWhatsapp,
            'email'            => $email,
            'instagram'        => $instagram,
            'birthday'         => $birthday,
            'address'          => $address,
            'default_discount' => $defaultDiscount,
            'preferences'      => $preferences,
        ];

        if ($id > 0) {
            $wpdb->update("{$wpdb->prefix}be_customers", $data, ['id' => $id]);
            $customerId = $id;
        } else {
            $wpdb->insert("{$wpdb->prefix}be_customers", $data);
            $customerId = (int)$wpdb->insert_id;
        }

        wp_send_json_success(['customer_id' => $customerId, 'redirect' => admin_url('admin.php?page=be-customers')]);
    }

    public static function ajaxUpdateAddress(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        global $wpdb;
        $customerName = sanitize_text_field($_POST['customer_name'] ?? '');
        $address = sanitize_textarea_field($_POST['address'] ?? '');

        if (!empty($customerName) && !empty($address)) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}be_customers SET address = %s WHERE name = %s",
                $address, $customerName
            ));
            wp_send_json_success(['message' => 'Endereço atualizado no cadastro do cliente.']);
        }
        wp_send_json_error(['message' => 'Cliente ou endereço não informado.']);
    }

    public static function ajaxDeleteCustomer(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Acesso negado.'], 403);
        }

        global $wpdb;
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $wpdb->delete("{$wpdb->prefix}be_customers", ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'ID inválido.']);
    }
}