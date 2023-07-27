<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf\Results;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAmount()
 * @method string|null getBillerCode()
 * @method string|null getCustomerReferenceNumber()
 * @method string|null getPaymentInstructionType()
 * @method string|null getTransactionReferenceNumber()
 * @method string|null getOriginalReferenceNumber()
 * @method string|null getErrorCorrectionReason()
 * @method string|null getPaymentDate()
 * @method string|null getPaymentTime()
 * @method string|null getRestOfRecord()
 * @method string|null getSettlementDate()
 */
final class Transaction extends BaseResult
{
    /**
     * Convert to DateTime object and return.
     */
    public function getPaymentDateObject(): ?DateTimeInterface
    {
        $value = $this->data['paymentDate'];

        if (
            \is_string($value) === false ||
            \ctype_digit($value) === false
        ) {
            return null;
        }

        return new DateTime($value);
    }

    /**
     * Convert to DateTime object and return.
     */
    public function getSettlementDateObject(): ?DateTimeInterface
    {
        $value = $this->data['settlementDate'];

        if (
            \is_string($value) === false ||
            \ctype_digit($value) === false
        ) {
            return null;
        }

        return new DateTime($value);
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
            'restOfRecord',
            'settlementDate',
        ];
    }
}
