<?php
declare(strict_types=1);

namespace BusinessEngine\Orders\Admin;

use BusinessEngine\Orders\Repositories\OrderRepository;
use BusinessEngine\Orders\DTOs\OrderDTO;
use BusinessEngine\Customers\Repositories\CustomerRepository;
use BusinessEngine\Products\Repositories\ProductRepository;

final class OrderController
{
    private OrderRepository $repository;
    private CustomerRepository $customerRepository;
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->repository = new OrderRepository();
        $this->customerRepository = new CustomerRepository();
        $this->productRepository = new ProductRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu'], 25);
        add_action('wp_ajax_be_save_order', [$this, 'ajaxSave']);
        add_action('wp_ajax_be_quick_update_order_status', [$this, 'ajaxQuickStatus']);
        add_action('wp_ajax_be_get_order_details', [$this, 'ajaxGetDetails']);
        add_action('wp_ajax_be_delete_order', [$this, 'ajaxDelete']);
    }

    public function addMenu(): void
    {
        $menuTitle = function_exists('be_term') ? be_term('orders_plural') : 'Histórico de Pedidos';
        add_submenu_page(
            'business-engine',
            $menuTitle,
            $menuTitle,
            'manage_options',
            'be-orders',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        $search = sanitize_text_field($_GET['s'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $orders = $this->repository->getAll($search, $status);
        $customers = $this->customerRepository->getAll();
        $products = $this->productRepository->getAll();

        require BE_PLUGIN_DIR . 'modules/Orders/Views/orders-list.php';
    }

    public function ajaxGetDetails(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_GET['id'] ?? 0);
        $order = $this->repository->findById($id);

        if (!$order) {
            wp_send_json_error(['message' => 'Pedido não encontrado.']);
            return;
        }

        wp_send_json_success([
            'order' => $order->toArray(),
            'items' => $order->items,
        ]);
    }

    public function ajaxQuickStatus(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? 'pendente');

        if ($id <= 0 || empty($status)) {
            wp_send_json_error(['message' => 'Dados inválidos.'], 400);
        }

        if ($this->repository->updateStatus($id, $status)) {
            wp_send_json_success(['message' => 'Status atualizado com sucesso!']);
        } else {
            wp_send_json_error(['message' => 'Erro ao atualizar status.']);
        }
    }

    public function ajaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $raw = $_POST['order'] ?? [];
        $items = $_POST['items'] ?? [];

        if (empty($raw['customer_id'])) {
            wp_send_json_error(['message' => 'Selecione um cliente para vincular ao pedido.']);
            return;
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)($item['subtotal'] ?? 0.0);
        }

        $deliveryFee = max(0.0, (float)($raw['delivery_fee'] ?? 0.0));
        $discountAmount = max(0.0, (float)($raw['discount_amount'] ?? 0.0));
        $totalAmount = max(0.0, ($subtotal + $deliveryFee) - $discountAmount);

        $raw['subtotal_amount'] = $subtotal;
        $raw['total_amount'] = $totalAmount;

        $dto = OrderDTO::fromArray($raw);
        $id = $this->repository->save($dto, $items);

        wp_send_json_success([
            'id'      => $id,
            'message' => 'Pedido salvo com sucesso!',
        ]);
    }

    public function ajaxDelete(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->repository->delete($id)) {
            wp_send_json_success(['message' => 'Pedido removido com sucesso.']);
        } else {
            wp_send_json_error(['message' => 'Erro ao remover pedido.']);
        }
    }
}