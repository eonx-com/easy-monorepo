<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Bpay\Objects;

use EonX\EasyBankFiles\Generators\BaseObject;
use EonX\EasyBankFiles\Generators\Interfaces\GeneratorInterface;

/**
 * @method string getBatchCustomerId()
 * @method string getCustomerShortName()
 * @method string getProcessingDate()
 * @method string getRecordType()
 * @method string getRestOfRecord()
 */
final class Header extends BaseObject
{
    /**
     * Get validation rules.
     *
     * @return string[]
     */
    public function getValidationRules(): array
    {
        return [
            'customerShortName' => GeneratorInterface::VALIDATION_RULE_ALPHA,
            'processingDate' => GeneratorInterface::VALIDATION_RULE_DATE,
        ];
    }

    /**
     * Get attributes padding configuration as [<attribute> => [<length>, <string>, <type>]].
     *
     * @return int[][]
     *
     * @see http://php.net/manual/en/function.str-pad.php
     */
    protected function getAttributesPaddingRules(): array
    {
        return [
            'batchCustomerId' => [16],
            'customerShortName' => [20],
            'restOfRecord' => [99],
        ];
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['recordType', 'batchCustomerId', 'customerShortName', 'processingDate', 'restOfRecord'];
    }

    /**
     * Return record type.
     */
    protected function initRecordType(): string
    {
        return '1';
    }
}
