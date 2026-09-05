<?php
declare(strict_types=1);

namespace BusinessEngine\Recipes\DTOs;

final class RecipeDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $category,
        public readonly float $yieldQty,
        public readonly string $yieldUnit,
        public readonly int $prepTimeMinutes,
        public readonly float $laborCostCalculated,
        public readonly float $suppliesCostCalculated,
        public readonly float $totalCost,
        public readonly float $unitCost,
        public readonly string $instructions = '',
        public readonly array $items = [],
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data, array $items = []): self
    {
        $yieldQty = max(0.0001, (float)($data['yield_qty'] ?? 1.0));
        $suppliesCost = (float)($data['supplies_cost_calculated'] ?? 0.0);
        $laborCost = (float)($data['labor_cost_calculated'] ?? 0.0);
        $totalCost = (float)($data['total_cost'] ?? ($suppliesCost + $laborCost));
        $unitCost = (float)($data['unit_cost'] ?? ($yieldQty > 0 ? $totalCost / $yieldQty : 0.0));

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            category: (string)($data['category'] ?? 'Geral'),
            yieldQty: $yieldQty,
            yieldUnit: (string)($data['yield_unit'] ?? 'porções'),
            prepTimeMinutes: max(0, (int)($data['prep_time_minutes'] ?? 0)),
            laborCostCalculated: $laborCost,
            suppliesCostCalculated: $suppliesCost,
            totalCost: $totalCost,
            unitCost: round($unitCost, 6),
            instructions: (string)($data['instructions'] ?? ''),
            items: $items,
            metadata: isset($data['metadata']) && is_string($data['metadata']) ? (json_decode($data['metadata'], true) ?? []) : (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'category'                 => $this->category,
            'yield_qty'                => $this->yieldQty,
            'yield_unit'               => $this->yieldUnit,
            'prep_time_minutes'        => $this->prepTimeMinutes,
            'labor_cost_calculated'    => $this->laborCostCalculated,
            'supplies_cost_calculated' => $this->suppliesCostCalculated,
            'total_cost'               => $this->totalCost,
            'unit_cost'                => $this->unitCost,
            'instructions'             => $this->instructions,
            'metadata'                 => json_encode($this->metadata, JSON_UNESCAPED_UNICODE),
        ];
    }
}