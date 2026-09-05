<?php
declare(strict_types=1);

namespace BusinessEngine\Recipes\Repositories;

use BusinessEngine\Recipes\DTOs\RecipeDTO;

final class RecipeRepository
{
    private string $recipesTable;
    private string $itemsTable;
    private string $suppliesTable;

    public function __construct()
    {
        global $wpdb;
        $this->recipesTable  = $wpdb->prefix . 'be_recipes';
        $this->itemsTable    = $wpdb->prefix . 'be_recipe_items';
        $this->suppliesTable = $wpdb->prefix . 'be_supplies';
    }

    /**
     * @return RecipeDTO[]
     */
    public function getAll(string $search = '', string $category = ''): array
    {
        global $wpdb;
        $query = "SELECT * FROM {$this->recipesTable} WHERE 1=1";
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

        $recipes = [];
        foreach ($rows ?: [] as $row) {
            $id = (int)$row['id'];
            
            // Recálculo dinâmico caso unit_cost ou total_cost estejam zerados no registro
            $suppliesSum = (float)$wpdb->get_var($wpdb->prepare("
                SELECT COALESCE(SUM(ri.quantity * s.unit_cost_calculated), 0)
                FROM {$this->itemsTable} ri
                INNER JOIN {$this->suppliesTable} s ON s.id = ri.supply_id
                WHERE ri.recipe_id = %d
            ", $id));

            $yieldQty = max(0.0001, (float)($row['yield_qty'] ?? 1.0));
            $laborCost = (float)($row['labor_cost_calculated'] ?? 0.0);
            $totalCost = (float)$row['total_cost'];

            if ($totalCost <= 0 && $suppliesSum > 0) {
                $totalCost = $suppliesSum + $laborCost;
                $row['total_cost'] = $totalCost;
                $row['unit_cost'] = round($totalCost / $yieldQty, 6);
                $row['supplies_cost_calculated'] = $suppliesSum;
            }

            $recipes[] = RecipeDTO::fromArray($row);
        }

        return $recipes;
    }

    public function findById(int $id): ?RecipeDTO
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->recipesTable} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        if (!$row) return null;

        $items = $wpdb->get_results(
            $wpdb->prepare("
                SELECT ri.*, s.name as supply_name, s.use_unit as supply_unit, s.unit_cost_calculated as current_unit_cost
                FROM {$this->itemsTable} ri
                LEFT JOIN {$this->suppliesTable} s ON s.id = ri.supply_id
                WHERE ri.recipe_id = %d
            ", $id),
            ARRAY_A
        );

        return RecipeDTO::fromArray($row, $items ?: []);
    }

    public function save(RecipeDTO $dto, array $itemsData = []): int
    {
        global $wpdb;
        $data = $dto->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id !== null && $id > 0) {
            $wpdb->update($this->recipesTable, $data, ['id' => $id]);
            $recipeId = (int)$id;
        } else {
            $wpdb->insert($this->recipesTable, $data);
            $recipeId = (int)$wpdb->insert_id;
        }

        if (!empty($itemsData)) {
            $wpdb->delete($this->itemsTable, ['recipe_id' => $recipeId], ['%d']);
            foreach ($itemsData as $item) {
                $wpdb->insert($this->itemsTable, [
                    'recipe_id'          => $recipeId,
                    'supply_id'          => (int)($item['supply_id'] ?? 0),
                    'quantity'           => (float)($item['quantity'] ?? 0.0),
                    'unit'               => sanitize_text_field($item['unit'] ?? 'g'),
                    'unit_cost_snapshot' => (float)($item['unit_cost_snapshot'] ?? 0.0),
                    'subtotal_cost'      => (float)($item['subtotal_cost'] ?? 0.0),
                ]);
            }
        }

        return $recipeId;
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        $wpdb->delete($this->itemsTable, ['recipe_id' => $id], ['%d']);
        $deleted = $wpdb->delete($this->recipesTable, ['id' => $id], ['%d']);
        return $deleted !== false && $deleted > 0;
    }

    public function getCategories(): array
    {
        global $wpdb;
        $results = $wpdb->get_col("SELECT DISTINCT category FROM {$this->recipesTable} WHERE category != '' ORDER BY category ASC");
        return !empty($results) ? $results : ['Massas', 'Recheios', 'Coberturas', 'Montagens', 'Geral'];
    }
}