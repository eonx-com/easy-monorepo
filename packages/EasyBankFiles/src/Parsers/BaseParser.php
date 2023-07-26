<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers;

abstract class BaseParser
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
