<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

trait ControlTotalTrait
{
    /**
     * The on-file value is an integer number of implied cents that may carry a leading sign,
     * so the amount is that value divided by 100.
     */
    private function formatAmount(string $amount): float
    {
        return (float)$amount / 100;
    }
}
