<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ack\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getValue()
 * @method array getAttributes()
 */
final class Issue extends AbstractResult
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
