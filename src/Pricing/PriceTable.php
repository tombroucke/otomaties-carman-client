<?php

namespace Otomaties\CarmanClient\Pricing;

/**
 * Looks up prices in a Carman price table (a price per rental period)
 */
class PriceTable
{
    /**
     * The row of the shortest period that covers the given number of days. Longer rentals than
     * the longest period are extrapolated from the longest period.
     *
     * @return array{period_days: float, price: float, km_included: int, extra_km_price: float}|null
     */
    public static function lookup(array $prices, int $days): ?array
    {
        $prices = collect($prices)
            ->sortBy(fn (array $price) => (float) $price['period_days'])
            ->values();

        if ($prices->isEmpty()) {
            return null;
        }

        $row = $prices->first(fn (array $price) => (float) $price['period_days'] >= $days);
        $factor = 1;

        if (! $row) {
            $row = $prices->last();
            $factor = $days / max((float) $row['period_days'], 1);
        }

        return [
            'period_days' => (float) $row['period_days'] * $factor,
            'price' => (float) $row['price'] * $factor,
            'km_included' => (int) round(($row['km_included'] ?? 0) * $factor),
            'extra_km_price' => (float) ($row['extra_km_price'] ?? 0),
        ];
    }

    public static function price(array $prices, int $days): float
    {
        return self::lookup($prices, $days)['price'] ?? 0.0;
    }

    /**
     * Format an amount, e.g. "€ 40" or "€ 772,41"
     */
    public static function format(float $amount): string
    {
        $amount = round($amount, 2);

        return '€ '.number_format_i18n($amount, fmod($amount, 1) ? 2 : 0);
    }
}
