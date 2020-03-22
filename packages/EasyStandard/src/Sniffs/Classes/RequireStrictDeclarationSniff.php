<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class RequireStrictDeclarationSniff implements Sniff
{
    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // Get tokens
        $tokens = $phpcsFile->getTokens();

        // From opening tag, find declaration
        $declarationPtr = $phpcsFile->findNext(\T_DECLARE, $stackPtr);

        // If file doesn't contain any declarations, error out
        if (\is_int($declarationPtr) === false) {
            $phpcsFile->addError('Strict type declaration not found in file', $stackPtr, 'RequireStrictDeclaration');

            return;
        }

        // Cycle through declarations and attempt to find strict_types declaration
        $pointer = $stackPtr;

        while ($pointer !== false) {
            $stringPtr = $phpcsFile->findNext(\T_STRING, $pointer);

            // If string isn't found, skip
            if (\is_int($stringPtr) === false) {
                ++$pointer;

                continue;
            }

            // Get declaration string
            $declarationType = $tokens[$stringPtr]['content'] ?? '';

            // If not strict, skip
            if (\mb_strtolower($declarationType) !== 'strict_types') {
                $pointer = $phpcsFile->findNext(\T_DECLARE, $stringPtr);

                continue;
            }

            // Found
            break;
        }

        // If not found, error out
        if ($pointer === false) {
            $phpcsFile->addError('Strict type declaration not found in file', $stackPtr, 'RequireStrictDeclaration');

            return;
        }
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_OPEN_TAG];
    }
}
