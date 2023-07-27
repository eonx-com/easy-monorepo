<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Transactions;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getDescription()
 * @method string getParticulars()
 * @method string getType()
 */
final class Details extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['description', 'particulars', 'type'];
    }
}
