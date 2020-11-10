<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getLine()
 * @method int getLineNumber()
 */
final class Error extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['line', 'lineNumber'];
    }
}
