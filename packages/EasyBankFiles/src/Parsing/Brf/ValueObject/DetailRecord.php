<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\ValueObject;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getAmount()
 * @method string|null getBillerCode()
 * @method string|null getCustomerReferenceNumber()
 * @method string|null getErrorCorrectionReason()
 * @method string|null getFiller()
 * @method string|null getOriginalReferenceNumber()
 * @method string|null getPaymentDate()
 * @method string|null getPaymentInstructionType()
 * @method string|null getPaymentTime()
 * @method string|null getSettlementDate()
 * @method string|null getTransactionReferenceNumber()
 */
final class DetailRecord extends AbstractResult
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
            'amount',
            'billerCode',
            'customerReferenceNumber',
            'errorCorrectionReason',
            'filler',
            'originalReferenceNumber',
            'paymentDate',
            'paymentInstructionType',
            'paymentTime',
            'settlementDate',
            'transactionReferenceNumber',
        ];
    }
}
