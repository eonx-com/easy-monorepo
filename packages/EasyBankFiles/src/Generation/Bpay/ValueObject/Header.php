<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Bpay\ValueObject;

use EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule;
use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

/**
 * @method string getBatchCustomerId()
 * @method string getCustomerShortName()
 * @method string getProcessingDate()
 * @method string getRecordType()
 * @method string getRestOfRecord()
 */
final class Header extends AbstractObject
{
    public function getValidationRules(): array
    {
        return [
            'customerShortName' => ValidationRule::Alpha,
            'processingDate' => ValidationRule::Date,
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
