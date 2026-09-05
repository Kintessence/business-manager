<?php
declare(strict_types=1);

namespace BusinessEngine\Products\DTOs;

final class ProductDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $sku,
        public readonly string $strategicRole,
        public readonly float $directCost,
        public readonly float $finalPrice,
        public readonly float $targetMarginPct,
        public readonly bool $isActive,
        public readonly float $stockQty,
        public readonly array $items = [],
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data, array $items = []): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            sku: !empty($data['sku']) ? (string)$data['sku'] : null,
            strategicRole: (string)($data['strategic_role'] ?? 'carro_chefe'),
            directCost: max(0.0, (float)($data['direct_cost'] ?? 0.0)),
            finalPrice: max(0.0, (float)($data['final_price'] ?? 0.0)),
            targetMarginPct: max(1.0, (float)($data['target_margin_pct'] ?? 25.0)),
            isActive: (bool)($data['is_active'] ?? true),
            stockQty: (float)($data['stock_qty'] ?? 0.0),
            items: $items,
            metadata: isset($data['metadata']) && is_string($data['metadata']) ? (json_decode($data['metadata'], true) ?? []) : (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'sku'               => $this->sku,
            'strategic_role'    => $this->strategicRole,
            'direct_cost'       => $this->directCost,
            'final_price'       => $this->finalPrice,
            'target_margin_pct' => $this->targetMarginPct,
            'is_active'         => $this->isActive ? 1 : 0,
            'stock_qty'         => $this->stockQty,
            'metadata'          => json_encode($this->metadata, JSON_UNESCAPED_UNICODE),
        ];
    }
}