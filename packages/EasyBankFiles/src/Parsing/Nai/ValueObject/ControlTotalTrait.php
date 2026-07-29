<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

trait ControlTotalTrait
{
    /**
     * Format amount/total from string to float. The last two digits are the cents and are kept
     * as-is, including a leading zero (%02d) — dropping it (%d) both corrupts cents 01-09 and
     * makes different totals collide (e.g. 10001 and 10010).
     */
    private function formatAmount(string $amount): float
    {
        $length = \strlen($amount) - 2;

        return (float)\sprintf('%d.%02d', (int)\substr($amount, 0, $length), (int)\substr($amount, $length));
    }
}
