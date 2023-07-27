<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getValue()
 * @method array getAttributes()
 */
final class Issue extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['value', 'attributes'];
    }
}
