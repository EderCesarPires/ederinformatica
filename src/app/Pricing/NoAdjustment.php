<?php
declare(strict_types=1);

namespace App\Pricing;

/** Estratégia nula: usada quando não há desconto nem acréscimo. */
final class NoAdjustment implements PriceAdjustmentStrategy
{
    public function apply(float $amount): float
    {
        return $amount;
    }

    public function label(): string
    {
        return 'Sem ajuste';
    }
}
