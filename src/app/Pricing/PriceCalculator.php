<?php
declare(strict_types=1);

namespace App\Pricing;

/**
 * Contexto do Strategy: soma os itens e aplica os ajustes em sequência.
 */
final class PriceCalculator
{
    /** @var PriceAdjustmentStrategy[] */
    private array $adjustments = [];

    public function addAdjustment(PriceAdjustmentStrategy $adjustment): self
    {
        $this->adjustments[] = $adjustment;
        return $this;
    }

    /** @param array $items lista de ['quantity' => int, 'unit_price' => float] */
    public function subtotal(array $items): float
    {
        return round(array_reduce(
            $items,
            fn (float $sum, array $i) => $sum + $i['quantity'] * $i['unit_price'],
            0.0
        ), 2);
    }

    public function total(array $items): float
    {
        $amount = $this->subtotal($items);
        foreach ($this->adjustments as $adjustment) {
            $amount = $adjustment->apply($amount);
        }
        return max(0.0, round($amount, 2));
    }
}
