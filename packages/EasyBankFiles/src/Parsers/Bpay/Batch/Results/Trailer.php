<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getAmountOfApprovals()
 * @method string|null getAmountOfDeclines()
 * @method string|null getAmountOfPayments()
 * @method string|null getRestOfRecord()
 * @method string|null getNumberOfApprovals()
 * @method string|null getNumberOfDeclines()
 * @method string|null getNumberOfPayments()
 */
final class Trailer extends BaseResult
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
