<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAmountOfApprovals()
 * @method string getAmountOfDeclines()
 * @method string getAmountOfPayments()
 * @method string getRestOfRecord()
 * @method string getNumberOfApprovals()
 * @method string getNumberOfDeclines()
 * @method string getNumberOfPayments()
 */
final class TrailerRecord extends AbstractResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'amountOfApprovals',
            'amountOfDeclines',
            'amountOfPayments',
            'numberOfApprovals',
            'numberOfDeclines',
            'numberOfPayments',
            'restOfRecord',
        ];
    }
}
