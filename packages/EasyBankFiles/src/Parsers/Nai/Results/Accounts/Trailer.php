<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Accounts;

use EonX\EasyBankFiles\Parsers\BaseResult;
use EonX\EasyBankFiles\Parsers\Nai\ControlTotal;

/**
 * @method string getCode()
 */
final class Trailer extends BaseResult
{
    use ControlTotal;

    /**
     * Get account control total A.
     */
    public function getAccountControlTotalA(): float
    {
        return $this->formatAmount($this->data['accountControlTotalA']);
    }

    /**
     * Get account control total B.
     */
    public function getAccountControlTotalB(): float
    {
        return $this->formatAmount($this->data['accountControlTotalB']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['code', 'accountControlTotalA', 'accountControlTotalB'];
    }
}
