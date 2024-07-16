<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getAmountOfApprovals()
 * @method string|null getAmountOfDeclines()
 * @method string|null getAmountOfPayments()
 * @method string|null getRestOfRecord()
 * @method string|null getNumberOfApprovals()
 * @method string|null getNumberOfDeclines()
 * @method string|null getNumberOfPayments()
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
