<?php
declare(strict_types=1);

namespace BusinessEngine\Customers\DTOs;

final class CustomerDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $channel,
        public readonly string $address,
        public readonly string $neighborhood,
        public readonly string $notes = '',
        public readonly int $totalOrders = 0,
        public readonly float $totalSpent = 0.0,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            phone: preg_replace('/[^0-9]/', '', (string)($data['phone'] ?? '')),
            channel: (string)($data['channel'] ?? 'whatsapp'),
            address: (string)($data['address'] ?? ''),
            neighborhood: (string)($data['neighborhood'] ?? ''),
            notes: (string)($data['notes'] ?? ''),
            totalOrders: (int)($data['total_orders'] ?? 0),
            totalSpent: (float)($data['total_spent'] ?? 0.0),
            metadata: isset($data['metadata']) && is_string($data['metadata']) ? (json_decode($data['metadata'], true) ?? []) : (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'channel'      => $this->channel,
            'address'      => $this->address,
            'neighborhood' => $this->neighborhood,
            'notes'        => $this->notes,
            'metadata'     => json_encode($this->metadata, JSON_UNESCAPED_UNICODE),
        ];
    }
}