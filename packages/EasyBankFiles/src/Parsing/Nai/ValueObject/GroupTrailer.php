<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getCode()
 * @method string getNumberOfAccounts()
 */
final class GroupTrailer extends AbstractResult
{
    use ControlTotalTrait;

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
