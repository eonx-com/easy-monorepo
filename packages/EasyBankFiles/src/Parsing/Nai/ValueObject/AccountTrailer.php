<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getCode()
 */
final class AccountTrailer extends AbstractResult
{
    use ControlTotalTrait;

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
