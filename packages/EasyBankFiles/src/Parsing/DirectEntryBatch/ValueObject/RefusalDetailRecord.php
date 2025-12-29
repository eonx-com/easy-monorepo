<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAccountName()
 * @method string getAccountNumber()
 * @method string getAmount()
 * @method string getBsb()
 * @method string getLodgmentReference()
 * @method string getOriginalDayOfReturn()
 * @method string getOriginalUserIdNumber()
 * @method string getRecordType()
 * @method string getRefusalCode()
 * @method string getRemitterName()
 * @method string getTraceAccountNumber()
 * @method string getTraceBsb()
 * @method string getTransactionCode()
 */
final class RefusalDetailRecord extends AbstractResult
{
    /**
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'accountName',
            'accountNumber',
            'amount',
            'bsb',
            'lodgmentReference',
            'originalDayOfReturn',
            'originalUserIdNumber',
            'recordType',
            'refusalCode',
            'remitterName',
            'traceAccountNumber',
            'traceBsb',
            'transactionCode',
        ];
    }
}
