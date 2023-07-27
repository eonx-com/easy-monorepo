<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Groups;

use EonX\EasyBankFiles\Parsers\BaseResult;
use EonX\EasyBankFiles\Parsers\Nai\ControlTotal;

/**
 * @method string getCode()
 * @method string getNumberOfAccounts()
 */
final class Trailer extends BaseResult
{
    use ControlTotal;

    /**
     * Return group control total A.
     */
    public function getGroupControlTotalA(): float
    {
        return $this->formatAmount($this->data['groupControlTotalA']);
    }

    /**
     * Return group control total B.
     */
    public function getGroupControlTotalB(): float
    {
        return $this->formatAmount($this->data['groupControlTotalB']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['code', 'groupControlTotalA', 'groupControlTotalB', 'numberOfAccounts'];
    }
}
