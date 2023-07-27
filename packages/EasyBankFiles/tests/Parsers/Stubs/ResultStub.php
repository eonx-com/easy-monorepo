<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Stubs;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getBiller()
 * @method string getWhatAttribute()
 */
final class ResultStub extends BaseResult
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
