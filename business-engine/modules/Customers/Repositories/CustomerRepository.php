<?php
declare(strict_types=1);

namespace BusinessEngine\Customers\Repositories;

use BusinessEngine\Customers\DTOs\CustomerDTO;

final class CustomerRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'be_customers';
    }

    /**
     * @return CustomerDTO[]
     */
    public function getAll(string $search = '', string $channel = ''): array
    {
        global $wpdb;
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (name LIKE %s OR phone LIKE %s OR neighborhood LIKE %s)";
            $wildcard = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($channel)) {
            $query .= " AND channel = %s";
            $params[] = $channel;
        }

        $query .= " ORDER BY name ASC";

        $rows = !empty($params)
            ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)
            : $wpdb->get_results($query, ARRAY_A);

        return array_map(fn(array $row) => CustomerDTO::fromArray($row), $rows ?: []);
    }

    public function findById(int $id): ?CustomerDTO
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        return $row ? CustomerDTO::fromArray($row) : null;
    }

    public function save(CustomerDTO $dto): int
    {
        global $wpdb;
        $data = $dto->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id !== null && $id > 0) {
            $wpdb->update($this->table, $data, ['id' => $id]);
            return (int)$id;
        }

        $wpdb->insert($this->table, $data);
        return (int)$wpdb->insert_id;
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        $deleted = $wpdb->delete($this->table, ['id' => $id], ['%d']);
        return $deleted !== false && $deleted > 0;
    }
}