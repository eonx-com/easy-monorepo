<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryReturn\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAccountName()
 * @method string|null getAccountNumber()
 * @method string|null getAmount()
 * @method string|null getBsb()
 * @method string getIndicator()
 * @method string|null getLodgmentReference()
 * @method string getOriginalDayOfProcessing()
 * @method string getOriginalUserIdNumber()
 * @method string getRecordType()
 * @method string|null getRemitterName()
 * @method string|null getTraceAccountNumber()
 * @method string|null getTraceBsb()
 * @method string|null getTxnCode()
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
            'accountName',
            'accountNumber',
            'amount',
            'bsb',
            'indicator',
            'lodgmentReference',
            'originalDayOfProcessing',
            'originalUserIdNumber',
            'recordType',
            'remitterName',
            'traceAccountNumber',
            'traceBsb',
            'txnCode',
        ];
    }
}
