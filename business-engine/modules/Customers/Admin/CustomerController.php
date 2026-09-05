<?php
declare(strict_types=1);

namespace BusinessEngine\Customers\Admin;

use BusinessEngine\Customers\Repositories\CustomerRepository;
use BusinessEngine\Customers\DTOs\CustomerDTO;

final class CustomerController
{
    private CustomerRepository $repository;

    public function __construct()
    {
        $this->repository = new CustomerRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu'], 22);
        add_action('wp_ajax_be_save_customer', [$this, 'ajaxSave']);
        add_action('wp_ajax_be_quick_update_customer', [$this, 'ajaxQuickUpdate']);
        add_action('wp_ajax_be_get_customer_details', [$this, 'ajaxGetDetails']);
        add_action('wp_ajax_be_delete_customer', [$this, 'ajaxDelete']);
    }

    public function addMenu(): void
    {
        $menuTitle = function_exists('be_term') ? be_term('customers_plural') : 'Clientes & CRM';
        add_submenu_page(
            'business-engine',
            $menuTitle,
            $menuTitle,
            'manage_options',
            'be-customers',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        global $wpdb;
        $search = sanitize_text_field($_GET['s'] ?? '');
        $channel = sanitize_text_field($_GET['channel'] ?? '');
        $customers = $this->repository->getAll($search, $channel);

        $ordersCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders");

        require BE_PLUGIN_DIR . 'modules/Customers/Views/customers-list.php';
    }

    public function ajaxGetDetails(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_GET['id'] ?? 0);
        $customer = $this->repository->findById($id);

        if (!$customer) {
            wp_send_json_error(['message' => 'Cliente não encontrado.']);
            return;
        }

        wp_send_json_success(['customer' => $customer->toArray()]);
    }

    public function ajaxQuickUpdate(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', (string)($_POST['phone'] ?? ''));
        $neighborhood = sanitize_text_field($_POST['neighborhood'] ?? '');
        $channel = sanitize_text_field($_POST['channel'] ?? 'whatsapp');

        if ($id <= 0 || empty($name)) {
            wp_send_json_error(['message' => 'Dados inválidos.'], 400);
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'be_customers',
            [
                'name'         => $name,
                'phone'        => $phone,
                'neighborhood' => $neighborhood,
                'channel'      => $channel,
            ],
            ['id' => $id]
        );

        wp_send_json_success(['message' => 'Atualizado com sucesso!']);
    }

    public function ajaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $raw = $_POST['customer'] ?? [];

        if (empty(trim($raw['name'] ?? ''))) {
            wp_send_json_error(['message' => 'O nome do cliente é obrigatório.']);
            return;
        }

        $dto = CustomerDTO::fromArray($raw);
        $id = $this->repository->save($dto);

        wp_send_json_success([
            'id'      => $id,
            'message' => 'Cliente salvo com sucesso!',
        ]);
    }

    public function ajaxDelete(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->repository->delete($id)) {
            wp_send_json_success(['message' => 'Cliente removido com sucesso.']);
        } else {
            wp_send_json_error(['message' => 'Erro ao remover cliente.']);
        }
    }
}