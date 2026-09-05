<?php
declare(strict_types=1);

namespace BusinessEngine\Products\Repositories;

use BusinessEngine\Products\DTOs\ProductDTO;

final class ProductRepository
{
    private string $productsTable;
    private string $itemsTable;

    public function __construct()
    {
        global $wpdb;
        $this->productsTable = $wpdb->prefix . 'be_products';
        $this->itemsTable    = $wpdb->prefix . 'be_product_items';
    }

    /**
     * @return ProductDTO[]
     */
    public function getAll(string $search = '', string $role = ''): array
    {
        global $wpdb;
        $query = "SELECT * FROM {$this->productsTable} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        if (!empty($role)) {
            $query .= " AND strategic_role = %s";
            $params[] = $role;
        }

        $query .= " ORDER BY name ASC";

        $rows = !empty($params)
            ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)
            : $wpdb->get_results($query, ARRAY_A);

        return array_map(fn(array $row) => ProductDTO::fromArray($row), $rows ?: []);
    }

    public function findById(int $id): ?ProductDTO
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->productsTable} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        if (!$row) return null;

        $items = $wpdb->get_results(
            $wpdb->prepare("
                SELECT pi.*, 
                    CASE 
                        WHEN pi.item_type = 'recipe' THEN (SELECT r.name FROM {$wpdb->prefix}be_recipes r WHERE r.id = pi.item_id)
                        ELSE (SELECT s.name FROM {$wpdb->prefix}be_supplies s WHERE s.id = pi.item_id)
                    END as item_name
                FROM {$this->itemsTable} pi
                WHERE pi.product_id = %d
            ", $id),
            ARRAY_A
        );

        return ProductDTO::fromArray($row, $items ?: []);
    }

    public function save(ProductDTO $dto, array $itemsData = []): int
    {
        global $wpdb;
        $data = $dto->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id !== null && $id > 0) {
            $wpdb->update($this->productsTable, $data, ['id' => $id]);
            $productId = (int)$id;
        } else {
            $wpdb->insert($this->productsTable, $data);
            $productId = (int)$wpdb->insert_id;
        }

        if (!empty($itemsData)) {
            $wpdb->delete($this->itemsTable, ['product_id' => $productId], ['%d']);
            foreach ($itemsData as $item) {
                $wpdb->insert($this->itemsTable, [
                    'product_id'         => $productId,
                    'item_type'          => sanitize_text_field($item['item_type'] ?? 'recipe'),
                    'item_id'            => (int)($item['item_id'] ?? 0),
                    'quantity'           => (float)($item['quantity'] ?? 1.0),
                    'unit_cost_snapshot' => (float)($item['unit_cost_snapshot'] ?? 0.0),
                    'subtotal_cost'      => (float)($item['subtotal_cost'] ?? 0.0),
                ]);
            }
        }

        return $productId;
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        $wpdb->delete($this->itemsTable, ['product_id' => $id], ['%d']);
        $deleted = $wpdb->delete($this->productsTable, ['id' => $id], ['%d']);
        return $deleted !== false && $deleted > 0;
    }
}