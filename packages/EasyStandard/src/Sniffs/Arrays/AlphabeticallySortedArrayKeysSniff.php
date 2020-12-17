<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Arrays;

use Error;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpParser\Comment;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class AlphabeticallySortedArrayKeysSniff implements Sniff
{
    /**
     * @var string
     */
    public const ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY = 'ArrayKeysNotSortedAlphabetically';

    /**
     * @var string
     */
    public const ARRAY_PROCESS_ERROR = 'ArrayProcessError';

    /**
     * @var string
     */
    public const FILE_PARSE_ERROR = 'FileParseError';

    /**
     * This comment is temporarily added to the lines of a multiline array to keep it multiline after fixing the order.
     *
     * @var string
     */
    private const TEMP_COMMENT_CONTENT = '[temp comment]';

    /**
     * A list of patterns to be checked to skip the array.
     * Specify a token type (e.g. `T_FUNCTION` or `T_CLASS`) as a key
     * and an array of regex patterns as a value to skip an array in the
     * corresponding tokens (functions, classes).
     *
     * Example: `[T_FUNCTION => ['/^someFunction/']]`
     *
     * @var mixed[]
     */
    public $skipPatterns = [];

    /**
     * @param int $bracketOpenerPointer
     */
    public function process(File $phpcsFile, $bracketOpenerPointer): void
    {
        if ($this->shouldSkip($phpcsFile, $bracketOpenerPointer)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$bracketOpenerPointer];
        $bracketCloserPointer = $token['bracket_closer'] ?? $token['parenthesis_closer'];
        $code = $phpcsFile->getTokensAsString($bracketOpenerPointer, $bracketCloserPointer - $bracketOpenerPointer + 1);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse('<?php' . PHP_EOL . $code . ';');
        } catch (Error $error) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->addErrorOnLine(
                "Parse error: {$error->getMessage()}",
                $token['line'],
                self::FILE_PARSE_ERROR
            );
            $phpcsFile->fixer->endChangeset();

            return;
        }

        if ($ast === null) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->addErrorOnLine(
                'Unknown error while parsing the codee',
                $token['line'],
                self::FILE_PARSE_ERROR
            );
            $phpcsFile->fixer->endChangeset();

            return;
        }

        /** @var \PhpParser\Node\Stmt\Expression $stmtExpr */
        $stmtExpr = $ast[0];
        /** @var \PhpParser\Node\Expr\Array_ $array */
        $array = $stmtExpr->expr;

        $array = $this->refactor($array);

        if ($array === null) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->addErrorOnLine(
                'Unknown error while processing the array',
                $token['line'],
                self::ARRAY_PROCESS_ERROR
            );
            $phpcsFile->fixer->endChangeset();

            return;
        }

        if ($array->hasAttribute('isChanged') === false) {
            return;
        }

        $prettyPrinter = new Standard();
        $newContent = $prettyPrinter->prettyPrint([$array]);
        $newContent = \str_replace('    ' . self::TEMP_COMMENT_CONTENT . PHP_EOL, '', $newContent);

        $phpcsFile->fixer->beginChangeset();

        $fix = $phpcsFile->addFixableError(
            'The array keys should be sorted alphabetically',
            $token['content'],
            self::ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY
        );

        if ($fix !== false) {
            $phpcsFile->addErrorOnLine(
                'The array keys should be sorted alphabetically',
                $token['line'],
                self::ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY
            );

            $phpcsFile->fixer->replaceToken($bracketOpenerPointer, $newContent);
            for ($bracketOpenerPointer++; $bracketOpenerPointer <= $bracketCloserPointer; $bracketOpenerPointer++) {
                $phpcsFile->fixer->replaceToken($bracketOpenerPointer, '');
            }
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [T_OPEN_SHORT_ARRAY, T_ARRAY];
    }

    /**
     * @param ArrayItem[] $items
     *
     * @return ArrayItem[]
     */
    private function fixMultiLineOutput(array $items): array
    {
        /** @var \PhpParser\Node\Expr $currentKey */
        $currentKey = $items[0]->key;
        $currentLine = (int)$currentKey->getAttribute('startLine');

        foreach ($items as $index => $arrayItem) {
            if ($index === 0) {
                continue;
            }

            /** @var \PhpParser\Node\Expr $currentKey */
            $currentKey = $arrayItem->key;
            $nextLine = (int)$currentKey->getAttribute('startLine');
            if ($nextLine !== $currentLine) {
                $arrayItem->setAttribute('comments', [new Comment(self::TEMP_COMMENT_CONTENT)]);
                $currentLine = $nextLine;
            }
        }

        return $items;
    }

    private function getArrayKeyAsString(ArrayItem $node): string
    {
        /** @var \PhpParser\Node\Expr $keyNode */
        $keyNode = $node->key;
        switch ($keyNode->getType()) {
            case 'Expr_ClassConstFetch':
                /** @var \PhpParser\Node\Expr\ClassConstFetch $classConstNode */
                $classConstNode = $node->key;
                /** @var \PhpParser\Node\Name $nameNode */
                $nameNode = $classConstNode->class;
                /** @var \PhpParser\Node\Identifier $identifier */
                $identifier = $classConstNode->name;
                $name = $nameNode->getLast() . '::' . $identifier->name;
                break;
            default:
                /** @var \PhpParser\Node\Scalar\String_ $stringNode */
                $stringNode = $node->key;
                $name = \trim($stringNode->value, " \t\n\r\0\x0B\"'");
        }

        return \strtolower($name);
    }

    /**
     * @return ArrayItem[]
     */
    private function getSortedItems(Array_ $array): array
    {
        $items = $array->items;
        usort($items, function (ArrayItem $firstItem, ArrayItem $secondItem): int {
            $firstName = $this->getArrayKeyAsString($firstItem);
            $secondName = $this->getArrayKeyAsString($secondItem);

            return $firstName <=> $secondName;
        });

        return \array_filter($items);
    }

    /**
     * @param \PhpParser\Node\Expr\Array_ $array
     *
     * @return bool
     */
    private function isAssociativeOnly(Array_ $array): bool
    {
        $isAssociative = 1;

        /** @var \PhpParser\Node\Expr\ArrayItem $arrayItem */
        foreach ($array->items as $arrayItem) {
            $isAssociative &= $arrayItem->key !== null;
        }

        return (bool)$isAssociative;
    }

    private function refactor(?Array_ $node = null): ?Array_
    {
        if ($node === null) {
            return null;
        }

        if ($this->isAssociativeOnly($node)) {
            $items = $this->getSortedItems($node);

            if ($items !== $node->items) {
                $node->items = $this->fixMultiLineOutput($items);
                $node->setAttribute('isChanged', true);
            }
        }

        return $node;
    }

    private function shouldSkip(File $phpcsFile, int $bracketOpenerPointer): bool
    {
        $tokens = $phpcsFile->getTokens();

        foreach ($this->skipPatterns as $tokenType => $patterns) {
            $sourcePointer = TokenHelper::findPrevious($phpcsFile, [$tokenType], $bracketOpenerPointer);
            $namePointer = TokenHelper::findNextEffective($phpcsFile, $sourcePointer + 1, $bracketOpenerPointer);
            $name = $tokens[$namePointer]['content'];
            foreach ($patterns as $pattern) {
                if (\preg_match($pattern, $name)) {
                    return true;
                }
            }
        }

        return false;
    }
}
