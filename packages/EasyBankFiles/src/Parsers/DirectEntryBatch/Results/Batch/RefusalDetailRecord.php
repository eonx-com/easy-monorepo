<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAccountName()
 * @method string|null getAccountNumber()
 * @method string|null getAmount()
 * @method string|null getBsb()
 * @method string getIndicator()
 * @method string|null getLodgmentReference()
 * @method string|null getOriginalDayOfReturn()
 * @method string|null getOriginalUserIdNumber()
 * @method string getRecordType()
 * @method string|null getRemitterName()
 * @method string|null getTraceAccountNumber()
 * @method string|null getTraceBsb()
 * @method string|null getTxnCode()
 */
final class RefusalDetailRecord extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'accountName',
            'accountNumber',
            'amount',
            'bsb',
            'indicator',
            'lodgmentReference',
            'originalDayOfReturn',
            'originalUserIdNumber',
            'recordType',
            'remitterName',
            'traceAccountNumber',
            'traceBsb',
            'txnCode',
        ];
    }
}
