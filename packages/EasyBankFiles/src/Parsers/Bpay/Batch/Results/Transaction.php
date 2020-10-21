<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAccountBsb()
 * @method string|null getAccountNumber()
 * @method string|null getAmount()
 * @method string|null getBillerCode()
 * @method string|null getCustomerReferenceNumber()
 * @method string|null getReference1()
 * @method string|null getReference2()
 * @method string|null getReference3()
 * @method string|null getRestOfRecord()
 * @method string|null getReturnCode()
 * @method string|null getReturnCodeDescription()
 * @method string|null getTransactionReferenceNumber()
 */
final class Transaction extends BaseResult
{
    /**
     * Convert amount into decimal and return.
     */
    public function getAmountDecimal(): ?string
    {
        $value = $this->data['amount'] ?? null;

        // If value isn't set, return
        if ($value === null) {
            return null;
        }

        // Decimal is implied by the last 2 digits
        $dollars = \substr(\ltrim($value, '0'), 0, -2);
        $cents = \substr($value, -2);

        return \sprintf('%d.%d', $dollars, $cents);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'accountBsb',
            'accountNumber',
            'amount',
            'billerCode',
            'customerReferenceNumber',
            'reference1',
            'reference2',
            'reference3',
            'restOfRecord',
            'returnCode',
            'returnCodeDescription',
            'transactionReferenceNumber',
        ];
    }
}
