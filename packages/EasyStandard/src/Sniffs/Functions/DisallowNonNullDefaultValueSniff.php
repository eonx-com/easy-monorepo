<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use function in_array;
use const T_CLOSE_SHORT_ARRAY;
use const T_DOUBLE_COLON;
use const T_NS_SEPARATOR;
use const T_STRING;

final class DisallowNonNullDefaultValueSniff implements Sniff
{
    /**
     * @var string
     */
    public const FUNCTION_INCORRECT_DEFAULT_VALUE_FOR_ARRAY = 'IncorrectDefaultValueForArray';

    /**
     * @var mixed[]
     */
    public const REPLACEABLE_TOKENS = [
        T_CLOSE_SHORT_ARRAY,
        T_STRING,
        T_DOUBLE_COLON,
        T_NS_SEPARATOR,
    ];

    public function process(File $phpcsFile, $functionPointer): void
    {
        $parameters = $phpcsFile->getMethodParameters($functionPointer);
        $tokens = $phpcsFile->getTokens();

        foreach ($parameters as $parameter) {
            if (isset($parameter['default']) === false && $parameter['nullable_type'] === false) {
                continue;
            }

            $phpcsFile->fixer->beginChangeset();

            if (isset($parameter['default']) === false && $parameter['nullable_type']) {
                $fix = $phpcsFile->addFixableError(
                    'The default value should be `null`',
                    $parameter['content'],
                    self::FUNCTION_INCORRECT_DEFAULT_VALUE_FOR_ARRAY
                );

                if (!$fix) {
                    continue;
                }

                $phpcsFile->addErrorOnLine(
                    'The default value should be `null`',
                    $tokens[$parameter['token']]['line'],
                    self::FUNCTION_INCORRECT_DEFAULT_VALUE_FOR_ARRAY
                );

                $phpcsFile->fixer->addContent($parameter['token'], ' = null');
            }

            if (isset($parameter['default']) && $parameter['default'] !== 'null') {

                $fix = $phpcsFile->addFixableError(
                    'The default value should be `null`',
                    $parameter['content'],
                    self::FUNCTION_INCORRECT_DEFAULT_VALUE_FOR_ARRAY
                );

                if (!$fix) {
                    continue;
                }

                $phpcsFile->addErrorOnLine(
                    'The default value should be `null`',
                    $tokens[$parameter['token']]['line'],
                    self::FUNCTION_INCORRECT_DEFAULT_VALUE_FOR_ARRAY
                );

                $defaultTokenPtr = $parameter['default_token'];
                $nextPointer = TokenHelper::findNextEffective($phpcsFile, $defaultTokenPtr + 1);

                if (in_array($tokens[$nextPointer]['code'], self::REPLACEABLE_TOKENS, true)) {
                    $phpcsFile->fixer->replaceToken($nextPointer, '');
                }

                $phpcsFile->fixer->replaceToken($defaultTokenPtr, 'null');

                if ($parameter['type_hint_token'] !== false && $parameter['type_hint'][0] !== '?') {
                    $phpcsFile->fixer->addContent($parameter['type_hint_token'] - 1, '?');
                }
            }

            $phpcsFile->fixer->endChangeset();
        }
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return TokenHelper::$functionTokenCodes;
    }
}
