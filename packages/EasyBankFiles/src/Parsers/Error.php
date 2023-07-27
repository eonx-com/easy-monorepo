<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers;

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
