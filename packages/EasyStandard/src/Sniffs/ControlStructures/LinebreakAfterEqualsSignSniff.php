<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class LinebreakAfterEqualsSignSniff implements Sniff
{
    /**
     * @var int
     */
    private const IDENTATION_LENGTH = 4;

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

        $fix = $phpcsFile->addFixableError(
            'The line can\'t be broken just after the equals sign.',
            $tokens[$equalsSignPointer]['line'],
            self::LINEBREAK_AFTER_EQUALS_SIGN
        );

        if ($fix === false) {
            return;
        }

        $phpcsFile->fixer->replaceToken($equalsSignPointer + 1, ' ');
        $phpcsFile->fixer->replaceToken($equalsSignPointer + 2, '');

        $endOfExpressionPointer = TokenHelper::findNext($phpcsFile, \T_SEMICOLON, $equalsSignPointer);

        $whitespaces = TokenHelper::findNextAll(
            $phpcsFile,
            \T_WHITESPACE,
            $equalsSignPointer,
            $endOfExpressionPointer
        );

        $firstLineIdentationPointer = TokenHelper::findFirstTokenOnLine($phpcsFile, $equalsSignPointer);
        $firstLineIdentationContent = $tokens[$firstLineIdentationPointer]['type'] === 'T_WHITESPACE' ?
            $tokens[$firstLineIdentationPointer]['content'] :
            '';
        $firstLineIdentationLength = \strlen($firstLineIdentationContent);

        foreach ($whitespaces as $whitespace) {
            $whitespaceContent = $tokens[$whitespace]['content'];
            if ($tokens[$whitespace]['length'] > 1 && ctype_space($whitespaceContent)) {
                $phpcsFile->fixer->replaceToken(
                    $whitespace,
                    \str_pad($firstLineIdentationContent, $firstLineIdentationLength + self::IDENTATION_LENGTH, ' ')
                );
            }
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [\T_EQUAL];
    }
}
