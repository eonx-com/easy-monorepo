<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai;

trait ControlTotal
{
    /**
     * Format amount/total from string to float.
     */
    private function formatAmount(string $amount): float
    {
        $length = \strlen($amount) - 2;

        return (float)\sprintf('%d.%d', (int)\substr($amount, 0, $length), (int)\substr($amount, $length));
    }
}
