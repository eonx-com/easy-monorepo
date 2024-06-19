<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Common\Parser;

abstract class AbstractParser
{
    public function __construct(
        protected string $contents,
    ) {
    }

    /**
     * Process parsing.
     */
    abstract protected function process(): void;
}
