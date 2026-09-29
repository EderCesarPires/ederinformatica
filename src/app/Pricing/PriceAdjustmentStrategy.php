<?php
declare(strict_types=1);

namespace App\Pricing;

/**
 * PADRÃO DE PROJETO: Strategy
 *
 * Cada estratégia sabe aplicar um tipo de ajuste sobre um valor. O
 * PriceCalculator (contexto) aplica uma cadeia de estratégias sem saber
 * como cada uma funciona — novos ajustes (ex.: cupom fixo, taxa de
 * urgência) podem ser adicionados sem alterar o calculador.
 */
interface PriceAdjustmentStrategy
{
    public function apply(float $amount): float;

    public function label(): string;
}
