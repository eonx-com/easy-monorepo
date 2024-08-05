<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Common\Parser;

abstract class AbstractLineByLineParser extends AbstractParser
{
    protected const EMPTY_LINE_CODE = 'empty-line';

    /**
     * AbstractLineByLineParser constructor.
     */
    public function __construct(string $contents)
    {
        parent::__construct($contents);

        $this->process();
    }

    /**
     * Process line and parse data.
     */
    abstract protected function processLine(int $lineNumber, string $line): void;

    /**
     * Process parsing.
     */
    protected function process(): void
    {
        $contents = (array)\preg_split("/[\r\n]/", $this->contents);
        $lineNumber = 1;

        foreach ($contents as $line) {
            $line = \trim((string)$line);

            if ($line === '') {
                continue;
            }

            $this->processLine($lineNumber, $line);
            $lineNumber++;
        }
    }

    /**
     * Trim zeros from the left side of the string.
     */
    protected function trimLeftZeros(string $value): string
    {
        $value = \ltrim($value, '0');

        return $value === '' ? '0' : $value;
    }
}
