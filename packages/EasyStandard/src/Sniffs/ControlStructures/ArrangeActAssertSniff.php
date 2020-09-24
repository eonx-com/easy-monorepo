<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class ArrangeActAssertSniff implements Sniff
{
    /**
     * @var int[]
     */
    private const ALLOWED_SPACES_COUNT = [1, 2];

    /**
     * @var string
     */
    public $testMethodPrefix = 'test';

    /**
     * @var string
     */
    public $testNamespace = 'App\Tests';

    /**
     * @var string[]
     */
    private const ANONYMOUS_STRUCTURES = ['T_CLOSURE', 'T_ANON_CLASS'];

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->shouldSkip($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $openTokenPosition = TokenHelper::findNext($phpcsFile, [\T_OPEN_CURLY_BRACKET], $stackPtr);
        if ($openTokenPosition === null) {
            return;
        }

        $closeTokenPosition = $tokens[$openTokenPosition]['bracket_closer'];

        if ($this->isSingleLineMethod($phpcsFile, $openTokenPosition, $closeTokenPosition) === true) {
            return;
        }

        $currentTokenPosition = $openTokenPosition;
        $previousLine = $tokens[$openTokenPosition]['line'];
        $emptyLines = 0;
        $inAnonymousStructure = false;
        $bracketsLevel = 0;

        while ($currentTokenPosition < $closeTokenPosition) {
            // Find next token skipping whitespaces
            $nextTokenPosition = TokenHelper::findNextExcluding($phpcsFile, [T_WHITESPACE], $currentTokenPosition + 1);
            if (\in_array($tokens[$currentTokenPosition]['type'], self::ANONYMOUS_STRUCTURES, true)) {
                $inAnonymousStructure = true;
            }

            if ($inAnonymousStructure && $tokens[$currentTokenPosition]['type'] === 'T_OPEN_CURLY_BRACKET') {
                $bracketsLevel++;
            }

            $currentLine = $tokens[$nextTokenPosition]['line'];
            if ($inAnonymousStructure === false && $currentLine - $previousLine > 1) {
                $emptyLines++;
            }

            $previousLine = $currentLine;
            if ($inAnonymousStructure && $tokens[$currentTokenPosition]['type'] === 'T_CLOSE_CURLY_BRACKET' && --$bracketsLevel === 0) {
                $inAnonymousStructure = false;
            }

            $currentTokenPosition = $nextTokenPosition;
        }

        if (\in_array($emptyLines, self::ALLOWED_SPACES_COUNT, true) === false) {
            $method = FunctionHelper::getName($phpcsFile, $stackPtr);
            $phpcsFile->addErrorOnLine(
                \sprintf(
                    'Test method must conform to AAA. Allowed amount of empty lines is between [%s].',
                    \implode(', ', self::ALLOWED_SPACES_COUNT)
                ) .
                " Method [{$method}] has [{$emptyLines}] empty lines.",
                $tokens[$stackPtr]['line'],
                'ArrangeActAssertSniff'
            );
        }
    }

    /**
     * Returns the token types that this sniff is interested in
     *
     * @return int[]
     */
    public function register(): array
    {
        return [\T_FUNCTION];
    }

    /**
     * Does the method have only one single line.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTokenPosition
     * @param int $closeTokenPosition
     */
    private function isSingleLineMethod(File $phpcsFile, int $openTokenPosition, int $closeTokenPosition): bool
    {
        $semicolons = TokenHelper::findNextAll($phpcsFile, [\T_SEMICOLON], $openTokenPosition, $closeTokenPosition);

        return count($semicolons) === 1;
    }

    /**
     * Should this method be skipped or not.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     */
    private function shouldSkip(File $phpcsFile, int $stackPtr): bool
    {
        $classFqn = NamespaceHelper::findCurrentNamespaceName($phpcsFile, $stackPtr);

        if ($classFqn === null) {
            return true;
        }

        if (StringHelper::startsWith($classFqn, $this->testNamespace) === false) {
            return true;
        }

        $functionName = FunctionHelper::getName($phpcsFile, $stackPtr);

        return StringHelper::startsWith($functionName, $this->testMethodPrefix) === false;
    }
}
