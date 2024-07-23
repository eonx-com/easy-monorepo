<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Common\ValueObject;

/**
 * @method string getLine()
 * @method int getLineNumber()
 */
final class Error extends AbstractResult
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
