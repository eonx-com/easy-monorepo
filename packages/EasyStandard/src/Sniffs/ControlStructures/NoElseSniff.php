<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @deprecated use smart PHPStan instead: https://github.com/symplify/coding-standard/blob/master/docs/phpstan_rules.md#object-calisthenics-rules
 */
final class NoElseSniff implements Sniff
{
    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $phpcsFile->addError('Else statement must not be used', $stackPtr, 'NoElse');
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_ELSE];
    }
}
