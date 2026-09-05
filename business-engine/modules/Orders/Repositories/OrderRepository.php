<?php
declare(strict_types=1);

namespace BusinessEngine\Orders\Repositories;

use BusinessEngine\Orders\DTOs\OrderDTO;

final class OrderRepository
{
    private string $ordersTable;
    private string $itemsTable;
    private string $customersTable;

    public function __construct()
    {
        global $wpdb;
        $this->ordersTable    = $wpdb->prefix . 'be_orders';
        $this->itemsTable     = $wpdb->prefix . 'be_order_items';
        $this->customersTable = $wpdb->prefix . 'be_customers';
    }

    /**
     * @return array
     */
    public function getAll(string $search = '', string $status = ''): array
    {
        global $wpdb;
        $query = "
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.neighborhood as customer_neighborhood
            FROM {$this->ordersTable} o
            LEFT JOIN {$this->customersTable} c ON c.id = o.customer_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (o.order_number LIKE %s OR c.name LIKE %s OR c.phone LIKE %s)";
            $wildcard = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($status)) {
            $query .= " AND o.status = %s";
            $params[] = $status;
        }

        $query .= " ORDER BY o.id DESC";

        $rows = !empty($params)
            ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)
            : $wpdb->get_results($query, ARRAY_A);

        return $rows ?: [];
    }

    public function findById(int $id): ?OrderDTO
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->ordersTable} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        if (!$row) return null;

        $items = $wpdb->get_results(
            $wpdb->prepare("
                SELECT oi.*, p.name as product_name
                FROM {$this->itemsTable} oi
                LEFT JOIN {$wpdb->prefix}be_products p ON p.id = oi.product_id
                WHERE oi.order_id = %d
            ", $id),
            ARRAY_A
        );

        return OrderDTO::fromArray($row, $items ?: []);
    }

    public function save(OrderDTO $dto, array $itemsData = []): int
    {
        global $wpdb;
        $data = $dto->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id !== null && $id > 0) {
            $wpdb->update($this->ordersTable, $data, ['id' => $id]);
            $orderId = (int)$id;
        } else {
            $wpdb->insert($this->ordersTable, $data);
            $orderId = (int)$wpdb->insert_id;
        }

        if (!empty($itemsData)) {
            $wpdb->delete($this->itemsTable, ['order_id' => $orderId], ['%d']);
            foreach ($itemsData as $item) {
                $wpdb->insert($this->itemsTable, [
                    'order_id'            => $orderId,
                    'product_id'          => (int)($item['product_id'] ?? 0),
                    'quantity'            => (float)($item['quantity'] ?? 1.0),
                    'unit_price_snapshot' => (float)($item['unit_price_snapshot'] ?? 0.0),
                    'subtotal'            => (float)($item['subtotal'] ?? 0.0),
                ]);
            }
        }

        return $orderId;
    }

    public function updateStatus(int $id, string $status): bool
    {
        global $wpdb;
        $res = $wpdb->update($this->ordersTable, ['status' => $status], ['id' => $id]);
        return $res !== false;
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        $wpdb->delete($this->itemsTable, ['order_id' => $id], ['%d']);
        $deleted = $wpdb->delete($this->ordersTable, ['id' => $id], ['%d']);
        return $deleted !== false && $deleted > 0;
    }
}