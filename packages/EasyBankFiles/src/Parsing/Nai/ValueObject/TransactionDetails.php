<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getDescription()
 * @method string getParticulars()
 * @method string getType()
 */
final class TransactionDetails extends AbstractResult
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
