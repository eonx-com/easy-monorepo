<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

/**
 * @method string getAmount()
 * @method string getCode()
 * @method ?string getCustomerReferenceNumber()
 * @method string getFundsType()
 * @method ?string getImmediateAvailabilityAmount()
 * @method ?string getOneDayAvailabilityAmount()
 * @method ?string getPlusTwoDayAvailabilityAmount()
 * @method string getReferenceNumber()
 * @method string getText()
 * @method string getTransactionCode()
 * @method TransactionDetails getTransactionDetails()
 * @method ?string getValueDate()
 * @method ?string getValueTime()
 */
final class Transaction extends AbstractNaiResult
{
    /**
     * Get account.
     */
    public function getAccount(): ?Account
    {
        return $this->context->getAccount($this->data['account']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'account',
            'amount',
            'code',
            'customerReferenceNumber',
            'fundsType',
            'immediateAvailabilityAmount',
            'oneDayAvailabilityAmount',
            'plusTwoDayAvailabilityAmount',
            'referenceNumber',
            'text',
            'transactionCode',
            'transactionDetails',
            'valueDate',
            'valueTime',
        ];
    }
}
