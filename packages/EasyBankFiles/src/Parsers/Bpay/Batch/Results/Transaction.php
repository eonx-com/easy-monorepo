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
