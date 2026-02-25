<?php

namespace App\Services;

class TrafficLightService
{
    public static function calculate(array $case, int $warningDays): array
    {
        $today = new \DateTimeImmutable('today');

        if (!empty($case['fecha_termino_real']) || ($case['status'] ?? '') === 'Cerrado') {
            return ['days_remaining' => null, 'traffic_light' => 'gray', 'is_overdue' => false, 'status' => 'Cerrado'];
        }

        $due = new \DateTimeImmutable($case['fecha_vencimiento']);
        $days = (int)$today->diff($due)->format('%r%a');

        if ($days <= 0) {
            return ['days_remaining' => $days, 'traffic_light' => 'red', 'is_overdue' => true, 'status' => 'Vencido'];
        }
        if ($days <= $warningDays) {
            return ['days_remaining' => $days, 'traffic_light' => 'yellow', 'is_overdue' => false, 'status' => 'En Riesgo'];
        }
        return ['days_remaining' => $days, 'traffic_light' => 'green', 'is_overdue' => false, 'status' => 'Abierto'];
    }
}
