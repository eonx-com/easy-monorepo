<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getOriginalDayOfProcessing()
 * @method string getOriginalUserIdNumber()
 * @method string getRecordType()
 * @method string getReturnCode()
 * @method string|null getAccountName()
 * @method string|null getAccountNumber()
 * @method string|null getAmount()
 * @method string|null getBsb()
 * @method string|null getLodgmentReference()
 * @method string|null getRemitterName()
 * @method string|null getTraceAccountNumber()
 * @method string|null getTraceBsb()
 * @method string|null getTransactionCode()
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
            'accountName',
            'accountNumber',
            'amount',
            'bsb',
            'lodgmentReference',
            'originalDayOfProcessing',
            'originalUserIdNumber',
            'recordType',
            'remitterName',
            'returnCode',
            'traceAccountNumber',
            'traceBsb',
            'transactionCode',
        ];
    }
}
