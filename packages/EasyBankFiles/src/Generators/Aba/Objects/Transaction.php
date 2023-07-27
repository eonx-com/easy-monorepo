<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Aba\Objects;

use EonX\EasyBankFiles\Generators\BaseObject;
use EonX\EasyBankFiles\Generators\Interfaces\GeneratorInterface;

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
final class Transaction extends BaseObject
{
    public const CODE_GENERAL_CREDIT = 50;

    public const CODE_GENERAL_DEBIT = 13;

    /**
     * Get validation rules.
     *
     * @return string[]
     */
    public function getValidationRules(): array
    {
        return [
            'accountNumber' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'amount' => GeneratorInterface::VALIDATION_RULE_NUMERIC,
            'amountOfWithholdingTax' => GeneratorInterface::VALIDATION_RULE_NUMERIC,
            'bsbNumber' => GeneratorInterface::VALIDATION_RULE_BSB,
            'lodgementReference' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'nameOfRemitter' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'titleOfAccount' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'traceAccountNumber' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'traceBsb' => GeneratorInterface::VALIDATION_RULE_BSB,
            'transactionCode' => GeneratorInterface::class,
        ];
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
