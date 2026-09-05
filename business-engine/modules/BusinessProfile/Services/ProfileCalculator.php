<?php
declare(strict_types=1);

namespace BusinessEngine\BusinessProfile\Services;

final class ProfileCalculator
{
    public static function calculate(array $raw): array
    {
        $salary  = max(0.0, (float)($raw['owner_salary_target'] ?? 0.0));
        $fixed   = max(0.0, (float)($raw['fixed_expenses_total'] ?? 0.0));
        $days    = max(1, min(7, (int)($raw['work_days_per_week'] ?? 5)));
        $hours   = max(0.1, (float)($raw['work_hours_per_day'] ?? 8.0));
        $staff   = max(1, (int)($raw['production_staff_count'] ?? 1));
        $margin  = max(1.0, (float)($raw['target_net_margin'] ?? 25.0));
        $tax     = max(0.0, (float)($raw['tax_rate_percent'] ?? 0.0));
        $card    = max(0.0, (float)($raw['card_fee_percent'] ?? 0.0));

        // 85% de produtividade real
        $weeklyHours = $days * $hours;
        $monthlyHours = round($weeklyHours * 4.3333 * $staff * 0.85, 2);
        if ($monthlyHours <= 0.0) {
            $monthlyHours = 1.0;
        }

        $totalStructural = $salary + $fixed;
        $costPerHour = round($totalStructural / $monthlyHours, 4);
        $costPerMinute = round($costPerHour / 60.0, 6);

        $deductions = $tax + $card + 35.0; // 35% base insumos estimados
        $contributionMarginPct = max(5.0, 100.0 - $deductions);
        $breakEven = round($totalStructural / ($contributionMarginPct / 100.0), 2);

        return array_merge($raw, [
            'owner_salary_target'      => $salary,
            'fixed_expenses_total'     => $fixed,
            'work_days_per_week'       => $days,
            'work_hours_per_day'       => $hours,
            'production_staff_count'   => $staff,
            'target_net_margin'        => $margin,
            'tax_rate_percent'         => $tax,
            'card_fee_percent'         => $card,
            'monthly_productive_hours' => $monthlyHours,
            'cost_per_hour'            => $costPerHour,
            'cost_per_minute'          => $costPerMinute,
            'break_even_revenue'       => $breakEven,
        ]);
    }
}