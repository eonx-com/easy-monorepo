<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAccountBsb()
 * @method string getAccountNumber()
 * @method string getAmount()
 * @method string getBillerCode()
 * @method string getCustomerReferenceNumber()
 * @method string getReference1()
 * @method string getReference2()
 * @method string getReference3()
 * @method string getRestOfRecord()
 * @method string getReturnCode()
 * @method string getReturnCodeDescription()
 * @method string getTransactionReferenceNumber()
 */
final class DetailRecord extends AbstractResult
{
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
