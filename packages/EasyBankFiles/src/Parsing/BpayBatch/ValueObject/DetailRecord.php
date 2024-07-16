<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

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
