<?php
declare(strict_types=1);

namespace BusinessEngine\BusinessProfile\DTOs;

final class BusinessProfileDTO
{
    public function __construct(
        public readonly float $ownerSalaryTarget,
        public readonly float $fixedExpensesTotal,
        public readonly int $workDaysPerWeek,
        public readonly float $workHoursPerDay,
        public readonly int $productionStaffCount,
        public readonly float $targetNetMargin,
        public readonly float $taxRatePercent,
        public readonly float $cardFeePercent,
        public readonly float $monthlyProductiveHours,
        public readonly float $costPerHour,
        public readonly float $costPerMinute,
        public readonly float $breakEvenRevenue,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ownerSalaryTarget: (float)($data['owner_salary_target'] ?? 3000.0),
            fixedExpensesTotal: (float)($data['fixed_expenses_total'] ?? 800.0),
            workDaysPerWeek: (int)($data['work_days_per_week'] ?? 5),
            workHoursPerDay: (float)($data['work_hours_per_day'] ?? 8.0),
            productionStaffCount: max(1, (int)($data['production_staff_count'] ?? 1)),
            targetNetMargin: (float)($data['target_net_margin'] ?? 25.0),
            taxRatePercent: (float)($data['tax_rate_percent'] ?? 4.0),
            cardFeePercent: (float)($data['card_fee_percent'] ?? 3.5),
            monthlyProductiveHours: (float)($data['monthly_productive_hours'] ?? 147.33),
            costPerHour: (float)($data['cost_per_hour'] ?? 25.79),
            costPerMinute: (float)($data['cost_per_minute'] ?? 0.4298),
            breakEvenRevenue: (float)($data['break_even_revenue'] ?? 5629.62),
            metadata: (array)($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'owner_salary_target'      => $this->ownerSalaryTarget,
            'fixed_expenses_total'     => $this->fixedExpensesTotal,
            'work_days_per_week'       => $this->workDaysPerWeek,
            'work_hours_per_day'       => $this->workHoursPerDay,
            'production_staff_count'   => $this->productionStaffCount,
            'target_net_margin'        => $this->targetNetMargin,
            'tax_rate_percent'         => $this->taxRatePercent,
            'card_fee_percent'         => $this->cardFeePercent,
            'monthly_productive_hours' => $this->monthlyProductiveHours,
            'cost_per_hour'            => $this->costPerHour,
            'cost_per_minute'          => $this->costPerMinute,
            'break_even_revenue'       => $this->breakEvenRevenue,
            'metadata'                 => $this->metadata,
        ];
    }
}