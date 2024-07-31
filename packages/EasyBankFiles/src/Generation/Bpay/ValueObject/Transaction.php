<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Bpay\ValueObject;

use EonX\EasyBankFiles\Generation\Common\Enum\ValidationRule;
use EonX\EasyBankFiles\Generation\Common\ValueObject\AbstractObject;

/**
 * @method string getAmount()
 * @method string getBillerCode()
 * @method string getPaymentAccountBsb()
 * @method string getPaymentAccountNumber()
 * @method string getCustomerReferenceNumber()
 */
final class Transaction extends AbstractObject
{
    public function getValidationRules(): array
    {
        return [
            'amount' => ValidationRule::Numeric,
            'billerCode' => ValidationRule::Numeric,
            'customerReferenceNumber' => ValidationRule::Alpha,
            'paymentAccountBSB' => ValidationRule::Numeric,
            'paymentAccountNumber' => ValidationRule::Numeric,
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
            'amount' => [13, '0', \STR_PAD_LEFT],
            'billerCode' => [10, '0', \STR_PAD_LEFT],
            'customerReferenceNumber' => [20],
            'lodgementReference1' => [10],
            'lodgementReference2' => [20],
            'lodgementReference3' => [50],
            'restOfRecord' => [5],
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
            'billerCode',
            'paymentAccountBSB',
            'paymentAccountNumber',
            'customerReferenceNumber',
            'amount',
            'lodgementReference1',
            'lodgementReference2',
            'lodgementReference3',
            'restOfRecord',
        ];
    }

    /**
     * Return record type.
     */
    protected function initRecordType(): string
    {
        return '2';
    }
}
