<?php
declare(strict_types=1);

namespace BusinessEngine\Ingredients\Repositories;

use BusinessEngine\Ingredients\DTOs\IngredientDTO;

final class IngredientRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'be_supplies';
    }

    /**
     * @return IngredientDTO[]
     */
    public function getAll(string $search = '', string $category = ''): array
    {
        global $wpdb;
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        if (!empty($category)) {
            $query .= " AND category = %s";
            $params[] = $category;
        }

        $query .= " ORDER BY name ASC";

        $rows = !empty($params)
            ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)
            : $wpdb->get_results($query, ARRAY_A);

        return array_map(fn(array $row) => IngredientDTO::fromArray($row), $rows ?: []);
    }

    public function findById(int $id): ?IngredientDTO
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        return $row ? IngredientDTO::fromArray($row) : null;
    }

    public function save(IngredientDTO $dto): int
    {
        global $wpdb;
        $data = $dto->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id !== null && $id > 0) {
            $wpdb->update($this->table, $data, ['id' => $id]);
            return $id;
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

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        global $wpdb;
        $results = $wpdb->get_col("SELECT DISTINCT category FROM {$this->table} WHERE category != '' ORDER BY category ASC");
        return !empty($results) ? $results : ['Ingrediente', 'Embalagem', 'Acabamento', 'Geral'];
    }
}