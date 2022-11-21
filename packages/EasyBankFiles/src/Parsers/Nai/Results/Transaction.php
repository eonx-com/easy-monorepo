<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

/**
 * @method string getAmount()
 * @method string getCode()
 * @method string getFundsType()
 * @method string getReferenceNumber()
 * @method string getText()
 * @method string getTransactionCode()
 * @method \EonX\EasyBankFiles\Parsers\Nai\Results\Transactions\Details getTransactionDetails()
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
            'fundsType',
            'referenceNumber',
            'text',
            'transactionCode',
            'transactionDetails',
        ];
    }
}
