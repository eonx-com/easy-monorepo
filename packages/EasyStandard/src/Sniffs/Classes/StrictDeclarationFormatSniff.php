<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class StrictDeclarationFormatSniff implements Sniff
{
    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // Get tokens
        $tokens = $phpcsFile->getTokens();

        // If declaration doesn't exist, skip
        $declarationPtr = $phpcsFile->findNext(\T_DECLARE, $stackPtr);

        if (\is_int($declarationPtr) === false) {
            return;
        }

        /** @var int $declarationPtr */
        $openingTag = $tokens[$stackPtr];
        $declaration = $tokens[$declarationPtr];

        // If not a strict type declaration, skip
        $declarationType = $tokens[(int)$phpcsFile->findNext(\T_STRING, $declarationPtr)]['content'] ?? '';

        if (\mb_strtolower($declarationType) !== 'strict_types') {
            return;
        }

        // Check that the declaration immediately follows the opening tag
        if ($declaration['line'] !== $openingTag['line'] + 1) {
            $phpcsFile->addError(
                'Strict type declaration must be on the line immediately following the opening tag',
                $stackPtr,
                'StrictDeclarationFormat'
            );
        }

        // Ensure there are no leading spaces
        if ($declaration['column'] !== 1) {
            $phpcsFile->addError(
                'Strict type declaration must be on a new line with no leading whitespace',
                $stackPtr,
                'StrictDeclarationFormat'
            );
        }

        // Get pointers
        $openParenthesisPtr = $phpcsFile->findNext(\T_OPEN_PARENTHESIS, $declarationPtr);
        $stringPtr = $phpcsFile->findNext(\T_STRING, (int)$openParenthesisPtr);
        $equalsPtr = $phpcsFile->findNext(\T_EQUAL, (int)$stringPtr);
        $valuePtr = $phpcsFile->findNext(\T_LNUMBER, (int)$equalsPtr);
        $closeParenthesisPtr = $phpcsFile->findNext(\T_CLOSE_PARENTHESIS, (int)$valuePtr);
        $semicolonPtr = $phpcsFile->findNext(\T_SEMICOLON, (int)$closeParenthesisPtr);

        // Get data
        $openParenthesis = $tokens[(int)$openParenthesisPtr];
        $string = $tokens[(int)$stringPtr];
        $equals = $tokens[(int)$equalsPtr];
        $value = $tokens[(int)$valuePtr];
        $closeParenthesis = $tokens[(int)$closeParenthesisPtr];
        $semicolon = $tokens[(int)$semicolonPtr];

        // Ensure declaration is exactly as expected
        if (\is_int($openParenthesisPtr) === false ||
            \is_int($stringPtr) === false ||
            \is_int($equalsPtr) === false ||
            \is_int($valuePtr) === false ||
            \is_int($closeParenthesisPtr) === false ||
            \is_int($semicolonPtr) === false ||
            $string['content'] !== 'strict_types' ||
            $value['content'] !== '1' ||
            $declaration['line'] !== $openParenthesis['line'] ||
            $declaration['line'] !== $string['line'] ||
            $declaration['line'] !== $equals['line'] ||
            $declaration['line'] !== $value['line'] ||
            $declaration['line'] !== $closeParenthesis['line'] ||
            $declaration['line'] !== $semicolon['line'] ||
            $openParenthesis['column'] !== $declaration['column'] + $declaration['length'] ||
            $string['column'] !== $openParenthesis['column'] + $openParenthesis['length'] ||
            $equals['column'] !== $string['column'] + $string['length'] ||
            $value['column'] !== $equals['column'] + $equals['length'] ||
            $closeParenthesis['column'] !== $value['column'] + $value['length'] ||
            $semicolon['column'] !== $closeParenthesis['column'] + $closeParenthesis['length']
        ) {
            $phpcsFile->addError(
                'Strict type declaration invalid, the only acceptable format is `declare(strict_types=1);`',
                $stackPtr,
                'StrictDeclarationFormat'
            );
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
