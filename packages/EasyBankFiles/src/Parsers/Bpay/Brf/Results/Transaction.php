<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method getBillerCode()
 * @method getCustomerReferenceNumber()
 * @method getPaymentInstructionType()
 * @method getTransactionReferenceNumber()
 * @method getOriginalReferenceNumber()
 * @method getErrorCorrectionReason()
 * @method getPaymentTime()
 * @method getFiller()
 */
final class Transaction extends BaseResult
{
    /**
     * Convert amount into float and return as dollars
     */
    public function getAmount(): float
    {
        return (float)(((int)$this->data['amount']) / 100);
    }

    /**
     * Convert to DateTime object and return
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function getPaymentDate(): DateTime
    {
        return new DateTime($this->data['paymentDate']);
    }

    /**
     * Convert to DateTime object and return
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function getSettlementDate(): DateTime
    {
        return new DateTime($this->data['settlementDate']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'billerCode',
            'customerReferenceNumber',
            'paymentInstructionType',
            'transactionReferenceNumber',
            'originalReferenceNumber',
            'errorCorrectionReason',
            'amount',
            'paymentDate',
            'paymentTime',
            'settlementDate',
            'filler',
        ];
    }
}
