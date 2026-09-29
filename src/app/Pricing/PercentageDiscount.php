<?php
declare(strict_types=1);

namespace App\Pricing;

final class PercentageDiscount implements PriceAdjustmentStrategy
{
    public function __construct(private float $percent) {}

    public function apply(float $amount): float
    {
        return $amount - ($amount * $this->percent / 100);
    }

    public function label(): string
    {
        return "Desconto de {$this->percent}%";
    }
}
