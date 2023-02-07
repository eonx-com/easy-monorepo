<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getBsb()
 * @method string|null getNumberPayments()
 * @method string|null getTotalCreditAmount()
 * @method string|null getTotalDebitAmount()
 * @method string|null getTotalNetAmount()
 */
final class Trailer extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['bsb', 'numberPayments', 'totalNetAmount', 'totalCreditAmount', 'totalDebitAmount'];
    }
}
