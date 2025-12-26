<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAccountName()
 * @method string getAccountNumber()
 * @method string getAmount()
 * @method string getBsb()
 * @method string getIndicator()
 * @method string getLodgmentReference()
 * @method string getRecordType()
 * @method string getRemitterName()
 * @method string getTraceAccountNumber()
 * @method string getTraceBsb()
 * @method string getTransactionCode()
 * @method string getAmountOfWithholdingTax()
 */
final class PaymentDetailRecord extends AbstractResult
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
            'indicator',
            'lodgmentReference',
            'recordType',
            'remitterName',
            'traceAccountNumber',
            'traceBsb',
            'transactionCode',
            'amountOfWithholdingTax',
        ];
    }
}
