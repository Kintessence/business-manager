<?php
declare(strict_types=1);

namespace BusinessEngine\Ingredients\DTOs;

final class IngredientDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $category,
        public readonly string $pkgType,
        public readonly float $pkgSize,
        public readonly string $unitType,
        public readonly string $useUnit,
        public readonly float $pkgCost,
        public readonly float $lossPct,
        public readonly string $allergens,
        public readonly float $stockQty,
        public readonly float $minStock,
        public readonly float $unitCostCalculated,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        $pkgCost = max(0.0, (float)($data['pkg_cost'] ?? 0.0));
        $pkgSize = max(0.0001, (float)($data['pkg_size'] ?? 1000.0));
        $lossPct = max(0.0, min(99.0, (float)($data['loss_pct'] ?? 0.0)));
        $pkgUnit = (string)($data['unit_type'] ?? 'g');
        $useUnit = (string)($data['use_unit'] ?? 'g');

        // Fator de conversão entre unidade de compra e unidade de uso
        $factor = 1.0;
        if ($pkgUnit === 'kg' && $useUnit === 'g') $factor = 0.001;
        elseif ($pkgUnit === 'g' && $useUnit === 'kg') $factor = 1000.0;
        elseif ($pkgUnit === 'L' && $useUnit === 'ml') $factor = 0.001;
        elseif ($pkgUnit === 'ml' && $useUnit === 'L') $factor = 1000.0;

        $baseCost = ($pkgCost / $pkgSize) * $factor;
        $correctionFactor = ($lossPct > 0.0) ? (1.0 / (1.0 - ($lossPct / 100.0))) : 1.0;
        $unitCostCalculated = round($baseCost * $correctionFactor, 6);

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            category: (string)($data['category'] ?? 'Ingrediente'),
            pkgType: (string)($data['pkg_type'] ?? 'Pacote'),
            pkgSize: $pkgSize,
            unitType: $pkgUnit,
            useUnit: $useUnit,
            pkgCost: $pkgCost,
            lossPct: $lossPct,
            allergens: (string)($data['allergens'] ?? ''),
            stockQty: (float)($data['stock_qty'] ?? 0.0),
            minStock: (float)($data['min_stock'] ?? 0.0),
            unitCostCalculated: $unitCostCalculated,
            metadata: isset($data['metadata']) && is_string($data['metadata']) ? (json_decode($data['metadata'], true) ?? []) : (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'category'   => $this->category,
            'pkg_type'   => $this->pkgType,
            'pkg_size'   => $this->pkgSize,
            'unit_type'  => $this->unitType,
            'use_unit'   => $this->useUnit,
            'pkg_cost'   => $this->pkgCost,
            'loss_pct'   => $this->lossPct,
            'allergens'  => $this->allergens,
            'stock_qty'  => $this->stockQty,
            'min_stock'  => $this->minStock,
            'metadata'   => json_encode($this->metadata, JSON_UNESCAPED_UNICODE),
        ];
    }
}