<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\ControlStructures;

use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;

final class ExceptionAssertionsSniff implements Sniff
{
    /**
     * @var string[]
     */
    private const THROWN_EXCEPTION_ASSERTION = 'assertThrownException';

    /**
     * @var string
     */
    public const FUNCTION_MISSING_THROWN_EXCEPTION_ASSERTION = 'MissingThrownExceptionAssertion';

    /**
     * @var string
     */
    public const FUNCTION_MISSING_REQUIRED_TRANSLATABLE_ASSERTIONS = 'MissingRequiredTranslatableAssertions';

    /**
     * @var string[]
     */
    public $requiredTranslatableAssertions = [
        'assertThrownExceptionMessage',
        'assertThrownExceptionMessageParams',
        'assertThrownExceptionUserMessage',
        'assertThrownExceptionUserMessageParams',
    ];

    /**
     * @var string
     */
    private const EXCEPTION_CALL_FUNCTION = 'safeCall';

    public function register(): array
    {
        return [
            \T_FUNCTION,
        ];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ($this->shouldSkip($tokens, $stackPtr)) {
            return;
        }

        $functionToken = $tokens[$stackPtr];
        $exceptionClass = null;

        for ($key = $functionToken['scope_opener']; $key < $functionToken['scope_closer']; $key++) {
            if ($tokens[$key]['content'] === self::THROWN_EXCEPTION_ASSERTION) {
                $exceptionClass = $tokens[$key + 2]['content'];
            }
        }

        if ($exceptionClass === null) {
            $method = FunctionHelper::getName($phpcsFile, $stackPtr);
            $phpcsFile->addErrorOnLine(
                \sprintf(
                    'Missing [%s] is missing thrown exception assertion [%s].',
                    $method,
                    self::THROWN_EXCEPTION_ASSERTION
                ),
                $tokens[$stackPtr]['line'],
                self::FUNCTION_MISSING_THROWN_EXCEPTION_ASSERTION
            );

            return;
        }

        $resolvedClassName = NamespaceHelper::resolveClassName($phpcsFile, $exceptionClass, $stackPtr);
        if (\is_subclass_of($resolvedClassName, TranslatableExceptionInterface::class) === false) {
            return;
        }

        $missingTranslatableAssertions = $this->requiredTranslatableAssertions;

        for ($key = $functionToken['scope_opener']; $key < $functionToken['scope_closer']; $key++) {
            $foundAssertion = \array_search($tokens[$key]['content'], $missingTranslatableAssertions, true);
            if ($foundAssertion !== false) {
                unset($missingTranslatableAssertions[$foundAssertion]);
            }
        }

        if (\count($missingTranslatableAssertions) > 0) {
            $method = FunctionHelper::getName($phpcsFile, $stackPtr);
            $phpcsFile->addErrorOnLine(
                \sprintf(
                    'Method [%s] is missing required assertions for translatable exception [%s].',
                    $method,
                    \implode(', ', $missingTranslatableAssertions)
                ),
                $tokens[$stackPtr]['line'],
                self::FUNCTION_MISSING_REQUIRED_TRANSLATABLE_ASSERTIONS
            );
        }
    }

    /**
     * @param mixed[] $tokens
     */
    private function shouldSkip(array $tokens, int $stackPtr): bool
    {
        $functionToken = $tokens[$stackPtr];

        if (isset($functionToken['scope_opener'], $functionToken['scope_closer']) === false) {
            return true;
        }

        for ($key = $functionToken['scope_opener']; $key < $functionToken['scope_closer']; $key++) {
            if ($tokens[$key]['content'] === self::EXCEPTION_CALL_FUNCTION) {
                return false;
            }
        }

        return true;
    }
}
