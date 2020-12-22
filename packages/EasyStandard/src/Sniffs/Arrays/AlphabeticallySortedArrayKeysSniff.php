<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Arrays;

use EonX\EasyStandard\Output\Printer;
use Error;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\ParserFactory;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class AlphabeticallySortedArrayKeysSniff implements Sniff
{
    /**
     * @var string
     */
    private const ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY = 'ArrayKeysNotSortedAlphabetically';

    /**
     * @var string
     */
    private const FILE_PARSE_ERROR = 'FileParseError';

    /**
     * A list of patterns to be checked to skip the array.
     * Specify a token type (e.g. `T_FUNCTION` or `T_CLASS`) as a key
     * and an array of regex patterns as a value to skip an array in the
     * corresponding tokens (functions, classes).
     *
     * Example: `[T_FUNCTION => ['/^someFunction/']]`.
     *
     * @var mixed[]
     */
    public $skipPatterns = [];

    /**
     * @var mixed[]
     */
    private static $parsedLine = [];

    /**
     * @var \EonX\EasyStandard\Output\Printer
     */
    private $prettyPrinter;

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
            $phpcsFile->addErrorOnLine(
                "Parse error: {$error->getMessage()}",
                $token['line'],
                self::FILE_PARSE_ERROR
            );

            return;
        }

        if ($ast === null) {
            $phpcsFile->addErrorOnLine(
                'Unknown error while parsing the code',
                $token['line'],
                self::FILE_PARSE_ERROR
            );

            return;
        }

        /** @var \PhpParser\Node\Stmt\Expression $stmtExpr */
        $stmtExpr = $ast[0];
        /** @var \PhpParser\Node\Expr\Array_ $array */
        $array = $stmtExpr->expr;

        if ($array->items === null || \count($array->items) <= 1) {
            return;
        }

        if (isset(self::$parsedLine[$phpcsFile->getFilename()]) === false) {
            self::$parsedLine[$phpcsFile->getFilename()] = [];
        }

        self::$parsedLine[$phpcsFile->getFilename()][] = [
            'start' => $token['line'],
            'finish' => $tokens[$bracketCloserPointer]
        ];
        $this->prettyPrinter = new Printer();
        $array = $this->refactor($array);

        if ($array->hasAttribute('isChanged') === false) {
            return;
        }

        $phpcsFile->addErrorOnLine(
            'The array keys should be sorted alphabetically',
            $token['line'],
            self::ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY
        );

        $fix = $phpcsFile->addFixableError(
            'The array keys should be sorted alphabetically',
            $bracketOpenerPointer,
            self::ARRAY_KEYS_NOT_SORTED_ALPHABETICALLY
        );

        if ($fix !== false) {
            $this->setStartIndent($phpcsFile, $bracketOpenerPointer);

            $newContent = $this->prettyPrinter->printNodes([$array]);

            $phpcsFile->fixer->beginChangeset();

            for ($iterator = $bracketOpenerPointer; $iterator <= $bracketCloserPointer; $iterator++) {
                $phpcsFile->fixer->replaceToken($iterator, '');
            }
            $phpcsFile->fixer->replaceToken($bracketOpenerPointer, $newContent);

            $phpcsFile->fixer->endChangeset();
        }
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
        $currentLine = (int)$items[0]->getAttribute('startLine');

        foreach ($items as $index => $arrayItem) {
            if ($index === 0) {
                continue;
            }

            $nextLine = (int)$arrayItem->getAttribute('startLine');
            if ($nextLine !== $currentLine) {
                $arrayItem->setAttribute('multiLine', true);
                $currentLine = $nextLine;
            }

            if ($arrayItem->value instanceof Array_ && \count($arrayItem->value->items) > 0) {
                /** @var ArrayItem[] $subItems */
                $subItems = $arrayItem->value->items;
                $arrayItem->value->items = $this->fixMultiLineOutput($subItems);
            }

            $items[$index] = $arrayItem;
        }

        return $items;
    }

    private function getArrayKeyAsString(ArrayItem $node): string
    {
        /** @var \PhpParser\Node\Expr $key */
        $key = $node->key;
        $nodeKeyName = $this->prettyPrinter->prettyPrint([$key]);

        return \strtolower(\trim($nodeKeyName, " \t\n\r\0\x0B\"'"));
    }

    /**
     * @param ArrayItem[] $items
     *
     * @return ArrayItem[]
     */
    private function getSortedItems(array $items): array
    {
        foreach ($items as $index => $arrayItem) {
            if ($arrayItem->value instanceof Array_) {
                $subItems = $arrayItem->value->items;
                if (\count($subItems) > 1) {
                    $arrayItem->value->items = $this->getSortedItems($subItems);
                }

                $items[$index] = $arrayItem;
            }
        }

        if ($this->isAssociativeOnly($items) === false) {
            return $items;
        }

        \usort($items, function (ArrayItem $firstItem, ArrayItem $secondItem): int {
            $firstName = $this->getArrayKeyAsString($firstItem);
            $secondName = $this->getArrayKeyAsString($secondItem);

            return $firstName <=> $secondName;
        });

        return $items;
    }

    /**
     * @param ArrayItem[] $items
     *
     * @return bool
     */
    private function isAssociativeOnly(array $items): bool
    {
        $isAssociative = 1;

        foreach ($items as $arrayItem) {
            $isAssociative &= $arrayItem->key !== null;
        }

        return (bool)$isAssociative;
    }

    private function refactor(Array_ $node): Array_
    {
        /** @var ArrayItem[] $items */
        $items = $node->items;

        if ($this->isAssociativeOnly($items)) {
            $items = $this->getSortedItems($items);

            if ($node->items !== $items) {
                $node->items = $this->fixMultiLineOutput($items);
                $node->setAttribute('isChanged', true);
            }
        }

        return $node;
    }

    private function setStartIndent(File $phpcsFile, int $bracketOpenerPointer): void
    {
        $token = $phpcsFile->getTokens()[$bracketOpenerPointer];
        $indentLevel = (int)\floor(($token['column'] - 1) / 4);
        $indentLevel *= 4;

        $closePointer = $token['bracket_closer'] ?? $token['parenthesis_closer'];

        if ($closePointer === null) {
            $this->prettyPrinter->setStartIndentLevel($indentLevel);

            return;
        }

        $closeToken = $phpcsFile->getTokens()[$closePointer];

        if ($token['line'] === $closeToken['line']) {
            $this->prettyPrinter->setStartIndentLevel($indentLevel);

            return;
        }

        $indentLevel = (int)\floor(($closeToken['column'] - 1) / 4);
        $indentLevel *= 4;

        $this->prettyPrinter->setStartIndentLevel($indentLevel);
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

        if (isset(self::$parsedLine[$phpcsFile->getFilename()])) {
            $tokens = $phpcsFile->getTokens();
            $token = $tokens[$bracketOpenerPointer];
            $bracketCloserPointer = $token['bracket_closer'] ?? $token['parenthesis_closer'];
            $startLine = $token['line'];
            $finishLine = $tokens[$bracketCloserPointer]['line'];

            foreach (self::$parsedLine[$phpcsFile->getFilename()] as $parsedLine) {
                if ($startLine >= $parsedLine['start'] && $finishLine <= $parsedLine['finish']) {
                    return true;
                }
            }
        }

        return false;
    }
}
