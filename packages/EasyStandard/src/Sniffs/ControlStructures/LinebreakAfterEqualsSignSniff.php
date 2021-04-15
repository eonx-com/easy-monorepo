<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class LinebreakAfterEqualsSignSniff implements Sniff
{
    /**
     * @var string
     */
    private const LINEBREAK_AFTER_EQUALS_SIGN = 'LinebreakAfterEqualsSign';

    /**
     * @param int $equalsSignPointer
     */
    public function process(File $phpcsFile, $equalsSignPointer): void
    {
        $tokens = $phpcsFile->getTokens();

        $afterEqualsSignToken = $tokens[$equalsSignPointer + 1];

        if ($afterEqualsSignToken['content'] !== "\n") {
            return;
        }

        $phpcsFile->addErrorOnLine(
            'The line must not be broken right after the equals sign.',
            $tokens[$equalsSignPointer]['line'],
            self::LINEBREAK_AFTER_EQUALS_SIGN
        );
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_EQUAL];
    }
}
