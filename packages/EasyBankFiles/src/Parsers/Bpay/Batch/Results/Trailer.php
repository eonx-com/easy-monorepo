<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAmountOfApprovals()
 * @method string|null getAmountOfDeclines()
 * @method string|null getAmountOfPayments()
 * @method string|null getRestOfRecord()
 * @method string|null getNumberOfApprovals()
 * @method string|null getNumberOfDeclines()
 * @method string|null getNumberOfPayments()
 */
final class Trailer extends BaseResult
{
    /**
     * Get the amount of approvals as a decimal.
     */
    public function getAmountOfApprovalsDecimal(): ?string
    {
        return $this->getTrailerAmountDecimal('amountOfApprovals');
    }

    /**
     * Get the amount of declines as a decimal.
     */
    public function getAmountOfDeclinesDecimal(): ?string
    {
        return $this->getTrailerAmountDecimal('amountOfDeclines');
    }

    /**
     * Get the amount of payments as a decimal.
     */
    public function getAmountOfPaymentsDecimal(): ?string
    {
        return $this->getTrailerAmountDecimal('amountOfPayments');
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'amountOfApprovals',
            'amountOfDeclines',
            'amountOfPayments',
            'numberOfApprovals',
            'numberOfDeclines',
            'numberOfPayments',
            'restOfRecord',
        ];
    }

    /**
     * Get the amount as a decimal.
     *
     * @param string $attribute The attribute to get the decimal amount from
     */
    private function getTrailerAmountDecimal(string $attribute): ?string
    {
        $value = $this->data[$attribute] ?? null;

        // If value isn't set, return
        if ($value === null) {
            return null;
        }

        // Decimal is implied by the last 2 digits
        $dollars = \substr(\ltrim($value, '0'), 0, -2);
        $cents = \substr($value, -2);

        return \sprintf('%d.%d', $dollars, $cents);
    }
}
