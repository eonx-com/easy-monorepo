<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Bpay\ValueObject;

use EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule;
use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

/**
 * @method string getRecordType()
 * @method string getRestOfRecord()
 * @method string getTotalFileValue()
 * @method string getTotalNumberOfPayments()
 */
final class Trailer extends AbstractObject
{
    public function getValidationRules(): array
    {
        return [
            'totalFileValue' => ValidationRule::Numeric,
            'totalNumberOfPayments' => ValidationRule::Numeric,
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
