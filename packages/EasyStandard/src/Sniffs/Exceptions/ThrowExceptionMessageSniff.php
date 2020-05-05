<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Exceptions;

use Nette\Utils\Strings;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class ThrowExceptionMessageSniff implements Sniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $nextTokenPosition = TokenHelper::findNextEffective($phpcsFile, $stackPtr + 1);
        $nextToken = $tokens[$nextTokenPosition];

        if ($nextToken['code'] !== \T_NEW) {
            return;
        }

        $openParenthesisToken = TokenHelper::findNext($phpcsFile, [\T_OPEN_PARENTHESIS], $stackPtr);

        $messageTokenPosition = TokenHelper::findNextEffective($phpcsFile, $openParenthesisToken + 1);
        $messageToken = $tokens[$messageTokenPosition];

        if ($messageToken['code'] === \T_VARIABLE) {
            return;
        }

        if ($messageToken['code'] === \T_CLOSE_PARENTHESIS) {
            return;
        }

        if (Strings::startsWith($messageToken['content'], "'exceptions.") === false) {
            $phpcsFile->addErrorOnLine(
                "Exception message must be either a variable or a translation message, started with ['exceptions.']",
                $tokens[$stackPtr]['line'],
                'ThrowExceptionMessageSniff'
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
        return [
            \T_THROW,
        ];
    }
}
