<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry;

trait HasAmounts
{
    /**
     * Create amount value in dollars from string.
     */
    protected function createAmount(string $value): string
    {
        // because withholding tax has 8 digits instead of 10
        $value = \str_pad($value, 10, '0', \STR_PAD_LEFT);
        // get the dollar and the cent amount
        $dollar = (int)\substr($value, 0, 8);
        $cent = \substr($value, 8, 2);

        return \sprintf('%s.%s', $dollar, $cent);
    }
}
