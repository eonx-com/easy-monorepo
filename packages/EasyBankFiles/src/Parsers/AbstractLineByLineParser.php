<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers;

abstract class AbstractLineByLineParser extends BaseParser
{
    /**
     * AbstractLineByLineParser constructor.
     */
    public function __construct(string $contents)
    {
        parent::__construct($contents);

        $this->process();
    }

    /**
     * Process line and parse data
     */
    abstract protected function processLine(int $lineNumber, string $line): void;

    /**
     * Process parsing
     */
    protected function process(): void
    {
        $contents = \explode(\PHP_EOL, $this->contents);
        $lineNumber = 1;

        foreach ($contents as $line) {
            $line = \trim($line);

            if ($line === '') {
                continue;
            }

            $this->processLine($lineNumber, $line);
            $lineNumber++;
        }
    }
}
