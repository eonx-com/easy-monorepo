<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Stub\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBiller()
 * @method string getWhatAttribute()
 */
final class ResultStub extends AbstractResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['biller'];
    }
}
