<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\ValueObject;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAmount()
 * @method string getBillerCode()
 * @method string getCustomerReferenceNumber()
 * @method string getErrorCorrectionReason()
 * @method string getFiller()
 * @method string getOriginalReferenceNumber()
 * @method string getPaymentDate()
 * @method string getPaymentInstructionType()
 * @method string getPaymentTime()
 * @method string getSettlementDate()
 * @method string getTransactionReferenceNumber()
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
