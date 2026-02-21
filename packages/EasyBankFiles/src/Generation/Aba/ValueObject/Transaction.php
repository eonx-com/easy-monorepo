<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Aba\ValueObject;

use EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule;
use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

/**
 * @method string getAccountNumber()
 * @method string getAmount()
 * @method string getAmountOfWithholdingTax()
 * @method string getBsbNumber()
 * @method string getIndicator()
 * @method string getLodgementReference()
 * @method string getNameOfRemitter()
 * @method string getRecordType()
 * @method string getTitleOfAccount()
 * @method string getTraceAccountNumber()
 * @method string getTraceBsb()
 * @method string|int getTransactionCode()
 */
final class Transaction extends AbstractObject
{
    private const int TRANSACTION_CODE_CREDIT = 50;

    private const int TRANSACTION_CODE_DEBIT = 13;

    public function getValidationRules(): array
    {
        return [
            'accountNumber' => ValidationRule::Alpha,
            'amount' => ValidationRule::Numeric,
            'amountOfWithholdingTax' => ValidationRule::Numeric,
            'bsbNumber' => ValidationRule::Bsb,
            'lodgementReference' => ValidationRule::Alpha,
            'nameOfRemitter' => ValidationRule::Alpha,
            'titleOfAccount' => ValidationRule::Alpha,
            'traceAccountNumber' => ValidationRule::Alpha,
            'traceBsb' => ValidationRule::Bsb,
            'transactionCode' => ValidationRule::Required,
        ];
    }

    public function isCredit(): bool
    {
        return (int)$this->getTransactionCode() === self::TRANSACTION_CODE_CREDIT;
    }

    public function isDebit(): bool
    {
        return (int)$this->getTransactionCode() === self::TRANSACTION_CODE_DEBIT;
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     * @see http://php.net/manual/en/function.str-pad.php
     */
    protected function getAttributesPaddingRules(): array
    {
        return [
            'accountNumber' => [9, ' ', \STR_PAD_LEFT],
            'amount' => [10, '0', \STR_PAD_LEFT],
            'amountOfWithholdingTax' => [8, '0', \STR_PAD_LEFT],
            'indicator' => [1],
            'lodgementReference' => [18],
            'nameOfRemitter' => [16],
            'titleOfAccount' => [32],
            'traceAccountNumber' => [9, ' ', \STR_PAD_LEFT],
        ];
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'recordType',
            'bsbNumber',
            'accountNumber',
            'indicator',
            'transactionCode',
            'amount',
            'titleOfAccount',
            'lodgementReference',
            'traceBsb',
            'traceAccountNumber',
            'nameOfRemitter',
            'amountOfWithholdingTax',
        ];
    }

    /**
     * Return record type.
     */
    protected function initRecordType(): string
    {
        return '1';
    }
}
