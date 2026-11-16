<?php

namespace Otomaties\CarmanClient\Pricing;

use Illuminate\Support\Collection;

/**
 * Estimated cost of a reservation. Season prices are not taken into account, the final price is
 * calculated by Carman.
 */
class Estimate
{
    private ?array $rental;

    /**
     * @param  array  $vehicle  A vehicle from Rental::vehicle()
     * @param  array<int, array{accessory: array, quantity: int}>  $accessories
     * @param  array|null  $insurance  An insurance from Rental::insurances()
     * @param  float  $discountPercentage  Discount on the whole reservation
     */
    public function __construct(
        private array $vehicle,
        private int $days,
        private array $accessories = [],
        private ?array $insurance = null,
        private float $discountPercentage = 0,
    ) {
        $this->rental = PriceTable::lookup($vehicle['price_table'], $days);
    }

    public function days(): int
    {
        return $this->days;
    }

    public function rentalPrice(): float
    {
        return $this->rental['price'] ?? 0.0;
    }

    public function insurance(): ?array
    {
        if (! $this->insurance) {
            return null;
        }

        return [
            'name' => $this->insurance['name'],
            'total' => $this->insurance['is_free'] ? 0.0 : PriceTable::price($this->insurance['prices'], $this->days),
        ];
    }

    /**
     * @return Collection<int, array{name: string, quantity: int, total: float}>
     */
    public function accessories(): Collection
    {
        return collect($this->accessories)
            ->map(function (array $line) {
                $accessory = $line['accessory'];
                $price = $accessory['is_free'] ? 0.0 : PriceTable::price($accessory['prices'], $this->days);

                if ($accessory['max_price'] !== null) {
                    $price = min($price, $accessory['max_price']);
                }

                return [
                    'name' => $accessory['name'],
                    'quantity' => $line['quantity'],
                    'total' => $price * $line['quantity'],
                ];
            })
            ->values();
    }

    public function subtotal(): float
    {
        return $this->rentalPrice()
            + ($this->insurance()['total'] ?? 0)
            + $this->accessories()->sum('total');
    }

    public function discountPercentage(): float
    {
        return $this->discountPercentage;
    }

    public function discount(): float
    {
        return round($this->subtotal() * $this->discountPercentage / 100, 2);
    }

    public function total(): float
    {
        return $this->subtotal() - $this->discount();
    }

    public function kmIncluded(): int
    {
        return $this->rental['km_included'] ?? 0;
    }

    public function extraKmPrice(): float
    {
        return $this->rental['extra_km_price'] ?? 0.0;
    }

    public function deposit(): float
    {
        return $this->vehicle['deposit'];
    }
}
