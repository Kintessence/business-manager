<?php
declare(strict_types=1);

namespace BusinessEngine\Orders\DTOs;

final class OrderDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $customerId,
        public readonly string $orderNumber,
        public readonly string $status,
        public readonly ?string $deliveryDate,
        public readonly string $deliveryTime,
        public readonly string $deliveryType,
        public readonly float $subtotalAmount,
        public readonly float $deliveryFee,
        public readonly float $discountAmount,
        public readonly float $totalAmount,
        public readonly string $paymentMethod,
        public readonly bool $isPaid,
        public readonly string $notes = '',
        public readonly array $items = [],
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data, array $items = []): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            customerId: (int)($data['customer_id'] ?? 0),
            orderNumber: (string)($data['order_number'] ?? ('PED-' . date('Ymd-His'))),
            status: (string)($data['status'] ?? 'pendente'),
            deliveryDate: !empty($data['delivery_date']) ? (string)$data['delivery_date'] : null,
            deliveryTime: (string)($data['delivery_time'] ?? ''),
            deliveryType: (string)($data['delivery_type'] ?? 'entrega'),
            subtotalAmount: max(0.0, (float)($data['subtotal_amount'] ?? 0.0)),
            deliveryFee: max(0.0, (float)($data['delivery_fee'] ?? 0.0)),
            discountAmount: max(0.0, (float)($data['discount_amount'] ?? 0.0)),
            totalAmount: max(0.0, (float)($data['total_amount'] ?? 0.0)),
            paymentMethod: (string)($data['payment_method'] ?? 'pix'),
            isPaid: (bool)($data['is_paid'] ?? false),
            notes: (string)($data['notes'] ?? ''),
            items: $items,
            metadata: isset($data['metadata']) && is_string($data['metadata']) ? (json_decode($data['metadata'], true) ?? []) : (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'customer_id'     => $this->customerId,
            'order_number'    => $this->orderNumber,
            'status'          => $this->status,
            'delivery_date'   => $this->deliveryDate,
            'delivery_time'   => $this->deliveryTime,
            'delivery_type'   => $this->deliveryType,
            'subtotal_amount' => $this->subtotalAmount,
            'delivery_fee'    => $this->deliveryFee,
            'discount_amount' => $this->discountAmount,
            'total_amount'    => $this->totalAmount,
            'payment_method'  => $this->paymentMethod,
            'is_paid'         => $this->isPaid ? 1 : 0,
            'notes'           => $this->notes,
            'metadata'        => json_encode($this->metadata, JSON_UNESCAPED_UNICODE),
        ];
    }
}