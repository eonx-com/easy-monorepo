<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Sniffs\Arrays;

use EonX\EasyStandard\Output\Printer;
use Error;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
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
     * @var bool
     */
    private $isChanged;

    /**
     * @var \EonX\EasyStandard\Output\Printer
     */
    private $prettyPrinter;

    /**
     * @param int $bracketOpenerPointer
     */
    public function process(File $phpcsFile, $bracketOpenerPointer): void
    {
        $this->isChanged = false;

        if ($this->shouldSkip($phpcsFile, $bracketOpenerPointer)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$bracketOpenerPointer];
        $bracketCloserPointer = $token['bracket_closer'] ?? $token['parenthesis_closer'];
        $code = $phpcsFile->getTokensAsString($bracketOpenerPointer, $bracketCloserPointer - $bracketOpenerPointer + 1);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse('<?php' . \PHP_EOL . $code . ';');
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
            'finish' => $tokens[$bracketCloserPointer]['line'],
        ];
        $this->prettyPrinter = new Printer();
        $refactoredArray = $this->refactor($array);

        if ($this->isChanged === false) {
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

            $newContent = $this->prettyPrinter->printNodes([$refactoredArray]);

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
        return [T_ARRAY, T_OPEN_SHORT_ARRAY];
    }

    /**
     * @param \PhpParser\Node\Expr\ArrayItem[] $items
     *
     * @return \PhpParser\Node\Expr\ArrayItem[]
     */
    private function fixMultiLineOutput(array $items, ?int $currentLine = null): array
    {
        $currentLine = $currentLine ?? 0;

        foreach ($items as $index => $arrayItem) {
            if ($arrayItem->value instanceof Array_) {
                /** @var \PhpParser\Node\Expr\ArrayItem[] $subItems */
                $subItems = $arrayItem->value->items;
                $arrayItem->value->items = $this->fixMultiLineOutput($subItems,
                    $arrayItem->value->getAttribute('startLine'));
                $items[$index] = $arrayItem;
            }

            if ($arrayItem->value instanceof MethodCall) {
                /** @var \PhpParser\Node\Expr\MethodCall $value */
                $value = $arrayItem->value;
                foreach ($value->args as $argIndex => $argument) {
                    if ($argument->value instanceof Array_) {
                        /** @var \PhpParser\Node\Expr\ArrayItem[] $subItems */
                        $subItems = $argument->value->items;
                        $argument->value->items = $this->fixMultiLineOutput($subItems,
                            $argument->value->getAttribute('startLine'));
                        $value->args[$argIndex] = $argument;
                    }
                }

                $items[$index] = $arrayItem;
            }

            $nextLine = (int)$arrayItem->getAttribute('startLine');
            if ($nextLine !== $currentLine) {
                $arrayItem->setAttribute('multiLine', true);
                $currentLine = $nextLine;
            }

            $items[$index] = $arrayItem;
        }

        return $items;
    }

    private function getArrayKeyAsString(ArrayItem $node): ?string
    {
        /** @var \PhpParser\Node\Expr $key */
        $key = $node->key;

        if ($key === null) {
            return null;
        }

        $nodeKeyName = $this->prettyPrinter->prettyPrint([$key]);

        return \strtolower(\trim($nodeKeyName, " \t\n\r\0\x0B\"'"));
    }

    /**
     * @param \PhpParser\Node\Expr\ArrayItem[] $items
     *
     * @return \PhpParser\Node\Expr\ArrayItem[]
     */
    private function getSortedItems(array $items): array
    {
        foreach ($items as $index => $arrayItem) {
            if ($arrayItem->value instanceof Array_) {
                $arrayItem->value = $this->refactor($arrayItem->value);

                $items[$index] = $arrayItem;
            }

            if ($arrayItem->value instanceof MethodCall) {
                /** @var \PhpParser\Node\Expr\MethodCall $value */
                $value = $arrayItem->value;
                foreach ($value->args as $argIndex => $argument) {
                    if ($argument->value instanceof Array_) {
                        $argument->value = $this->refactor($argument->value);

                        $value->args[$argIndex] = $argument;
                    }
                }

                $items[$index] = $arrayItem;
            }
        }

        \usort($items, function (ArrayItem $firstItem, ArrayItem $secondItem): int {
            $firstName = $this->getArrayKeyAsString($firstItem);
            $secondName = $this->getArrayKeyAsString($secondItem);
            if ($firstName === null || $secondName === null) {
                return 0;
            }

            return $firstName <=> $secondName;
        });

        return $items;
    }

    private function refactor(Array_ $node): Array_
    {
        /** @var \PhpParser\Node\Expr\ArrayItem[] $items */
        $items = $node->items;

        if (\count($items) === 0) {
            return $node;
        }

        $items = $this->getSortedItems($items);

        if ($node->items !== $items) {
            $this->isChanged = true;
        }

        $node->items = $this->fixMultiLineOutput($items, $node->getAttribute('startLine'));

        return $node;
    }

    private function setStartIndent(File $phpcsFile, int $bracketOpenerPointer): void
    {
        $token = $phpcsFile->getTokens()[$bracketOpenerPointer];
        $indentSize = 4;
        $indentLevel = (int)\floor(($token['column'] - 1) / $indentSize);
        $indentLevel *= $indentSize;

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

        $indentLevel = (int)\floor(($closeToken['column'] - 1) / $indentSize);
        $indentLevel *= $indentSize;

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
