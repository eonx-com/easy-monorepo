<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Bpay\Objects;

use EonX\EasyBankFiles\Generators\BaseObject;
use EonX\EasyBankFiles\Generators\Interfaces\GeneratorInterface;

/**
 * @method string getRecordType()
 * @method string getRestOfRecord()
 * @method string getTotalFileValue()
 * @method string getTotalNumberOfPayments()
 */
final class Trailer extends BaseObject
{
    /**
     * Get validation rules.
     *
     * @return string[]
     */
    public function getValidationRules(): array
    {
        return [
            'totalFileValue' => GeneratorInterface::VALIDATION_RULE_NUMERIC,
            'totalNumberOfPayments' => GeneratorInterface::VALIDATION_RULE_NUMERIC,
        ];
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     @see http://php.net/manual/en/function.str-pad.php
     */
    protected function getAttributesPaddingRules(): array
    {
        return [
            'restOfRecord' => [120],
            'totalFileValue' => [13, '0', \STR_PAD_LEFT],
            'totalNumberOfPayments' => [10, '0', \STR_PAD_LEFT],
        ];
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['recordType', 'totalNumberOfPayments', 'totalFileValue', 'restOfRecord'];
    }

    /**
     * Return record type.
     */
    protected function initRecordType(): string
    {
        return '9';
    }
}
