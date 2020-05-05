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
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if ($this->shouldSkip($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $openTokenPosition = TokenHelper::findNext($phpcsFile, [\T_OPEN_CURLY_BRACKET], $stackPtr);
        $closeTokenPosition = $tokens[$openTokenPosition]['bracket_closer'];

        if ($this->isSingleLineMethod($phpcsFile, $openTokenPosition, $closeTokenPosition) === true) {
            return;
        }

        $currentTokenPosition = $openTokenPosition;
        $previousLine = $tokens[$openTokenPosition]['line'];
        $emptyLines = 0;

        while ($currentTokenPosition < $closeTokenPosition) {
            // Find next token skipping whitespaces
            $nextTokenPosition = TokenHelper::findNextExcluding($phpcsFile, [T_WHITESPACE], $currentTokenPosition + 1);
            $currentLine = $tokens[$nextTokenPosition]['line'];
            if ($currentLine - $previousLine > 1) {
                $emptyLines++;
            }
            $previousLine = $currentLine;
            $currentTokenPosition = $nextTokenPosition;
        }

        if (\in_array($emptyLines, self::ALLOWED_SPACES_COUNT, true)) {
            return;
        }

        $method = FunctionHelper::getName($phpcsFile, $stackPtr);
        $phpcsFile->addErrorOnLine(
            "Test method must conform to AAA. Allowed amount of empty lines is 1 or 2." .
            " Method [{$method}] has [{$emptyLines}] empty lines.",
            $tokens[$stackPtr]['line'],
            'ArrangeActAssertSniff'
        );
    }

    /**
     * Returns the token types that this sniff is interested in
     *
     * @return int[]
     */
    public function register(): array
    {
        return [
            \T_FUNCTION,
        ];
    }

    /**
     * Does the method have only one single line.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $openTokenPosition
     * @param int $closeTokenPosition
     *
     * @return bool
     */
    private function isSingleLineMethod(File $phpcsFile, int $openTokenPosition, int $closeTokenPosition)
    {
        $semicolons = TokenHelper::findNextAll($phpcsFile, [\T_SEMICOLON], $openTokenPosition, $closeTokenPosition);

        return (count($semicolons) === 1);
    }

    /**
     * Should this method be skipped or not.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     *
     * @return bool
     */
    private function shouldSkip(File $phpcsFile, int $stackPtr): bool
    {
        $classFqn = NamespaceHelper::findCurrentNamespaceName($phpcsFile, $stackPtr);

        if (StringHelper::startsWith($classFqn, $this->testNamespace) === false) {
            return true;
        }

        $functionName = FunctionHelper::getName($phpcsFile, $stackPtr);

        if (StringHelper::startsWith($functionName, $this->testMethodPrefix) === false) {
            return true;
        }

        return false;
    }
}
