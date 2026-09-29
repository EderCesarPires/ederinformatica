<?php
declare(strict_types=1);

namespace App\Pricing;

/**
 * PADRÃO DE PROJETO: Factory (Simple Factory)
 *
 * Centraliza a criação das estratégias de ajuste de preço a partir
 * dos dados do formulário. Quem usa não precisa conhecer as classes
 * concretas.
 */
final class AdjustmentFactory
{
    /** @return PriceAdjustmentStrategy[] */
    public static function fromPercentages(float $discount, float $surcharge): array
    {
        return [
            self::make('discount', $discount),
            self::make('surcharge', $surcharge),
        ];
    }

    public static function make(string $type, float $percent): PriceAdjustmentStrategy
    {
        if ($percent <= 0) {
            return new NoAdjustment();
        }

        return match ($type) {
            'discount'  => new PercentageDiscount($percent),
            'surcharge' => new PercentageSurcharge($percent),
            default     => throw new \InvalidArgumentException("Tipo de ajuste inválido: {$type}"),
        };
    }
}
