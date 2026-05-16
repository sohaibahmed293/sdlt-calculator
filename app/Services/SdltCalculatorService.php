<?php

namespace App\Services;

use App\Enums\Scenario;
use InvalidArgumentException;

class SdltCalculatorService
{
    public const SURCHARGE_LABEL = 'Additional property surcharge (5% on full price)';

    /**
     * Calculate SDLT for a residential property purchase in England.
     *
     * @param  int      $pricePence  Purchase price in pence (e.g. £295,000 = 29500000)
     * @param  Scenario $scenario
     */
    public function calculate(int $pricePence, Scenario $scenario): array
    {
        if ($pricePence < 0) {
            throw new InvalidArgumentException('Price must not be negative.');
        }

        $rates        = $this->resolveBands($pricePence, $scenario);
        $scenarioUsed = $rates['scenario_used'];
        $breakdown    = $this->applyBands($pricePence, $rates['bands']);
        $totalPence   = array_sum(array_column($breakdown, 'tax_pence'));

        if ($scenario === Scenario::AdditionalProperty) {
            $surchargePence = $this->calculateSurcharge($pricePence);
            $breakdown[]    = [
                'label'          => self::SURCHARGE_LABEL,
                'taxable_pence'  => $pricePence,
                'taxable_pounds' => $this->formatPounds($pricePence),
                'rate_percent'   => '5%',
                'tax_pence'      => $surchargePence,
                'tax_pounds'     => $this->formatPounds($surchargePence),
            ];
            $totalPence += $surchargePence;
        }

        $effectiveRate = $pricePence > 0
            ? number_format(($totalPence / $pricePence) * 100, 2)
            : '0.00';

        return [
            'total_pence'    => $totalPence,
            'total_pounds'   => $this->formatPounds($totalPence),
            'effective_rate' => $effectiveRate,
            'breakdown'      => array_map(fn ($row) => array_filter(
                $row,
                fn ($k) => $k !== 'tax_pence' && $k !== 'taxable_pence',
                ARRAY_FILTER_USE_KEY
            ), $breakdown),
            'scenario_used'  => $scenarioUsed,
        ];
    }

    private function resolveBands(int $pricePence, Scenario $scenario): array
    {
        $ftbMax = config('sdlt.first_time_buyer_max_price');

        if ($scenario === Scenario::FirstTimeBuyer && $pricePence <= $ftbMax) {
            return [
                'bands'         => config('sdlt.first_time_buyer_rates'),
                'scenario_used' => Scenario::FirstTimeBuyer->value,
            ];
        }

        // FTB above the price cap gets no relief; falls back to standard rates.
        return [
            'bands'         => config('sdlt.standard_rates'),
            'scenario_used' => ($scenario === Scenario::FirstTimeBuyer)
                ? Scenario::Standard->value
                : $scenario->value,
        ];
    }

    private function applyBands(int $pricePence, array $bands): array
    {
        $rows = [];

        foreach ($bands as $band) {
            if ($pricePence <= $band['from']) {
                break;
            }

            $ceiling      = $band['to'] ?? PHP_INT_MAX;
            $taxablePence = min($pricePence, $ceiling) - $band['from'];
            $taxPence     = (int) round($taxablePence * $band['rate']);
            $ratePercent  = ($band['rate'] * 100);

            $rows[] = [
                'label'          => $this->bandLabel($band),
                'taxable_pence'  => $taxablePence,
                'taxable_pounds' => $this->formatPounds($taxablePence),
                'rate_percent'   => rtrim(rtrim(number_format($ratePercent, 2), '0'), '.') . '%',
                'tax_pence'      => $taxPence,
                'tax_pounds'     => $this->formatPounds($taxPence),
            ];
        }

        return $rows;
    }

    private function calculateSurcharge(int $pricePence): int
    {
        return (int) round($pricePence * config('sdlt.additional_property_surcharge'));
    }

    private function bandLabel(array $band): string
    {
        $from = $this->formatPounds($band['from']);

        if ($band['to'] === null) {
            return "The portion above {$from}";
        }

        $to = $this->formatPounds($band['to']);

        if ($band['from'] === 0) {
            return "Up to {$to}";
        }

        return "The portion from {$from} to {$to}";
    }

    private function formatPounds(int $pence): string
    {
        return '£' . number_format((int) ($pence / 100), 0, '.', ',');
    }
}
