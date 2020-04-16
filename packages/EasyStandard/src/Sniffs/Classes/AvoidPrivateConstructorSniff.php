<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class AvoidPrivateConstructorSniff implements Sniff
{
    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $method = $phpcsFile->getDeclarationName($stackPtr);

        if ($method !== '__construct') {
            return;
        }

        $properties = $phpcsFile->getMethodProperties($stackPtr);
        if ($properties['scope'] === 'private') {
            $phpcsFile->addError('Private constructors should be avoided', $stackPtr, 'AvoidPrivateConstructors');
        }
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_FUNCTION];
    }
}
