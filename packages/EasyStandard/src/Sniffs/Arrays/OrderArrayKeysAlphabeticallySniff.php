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

final class OrderArrayKeysAlphabeticallySniff implements Sniff
{
    /**
     * @var string
     */
    public const ARRAY_KEYS_SORT_ALPHABETICALLY = 'ArrayKeysSortAlphabetically';

    /**
     * @var string
     */
    private const COMMENT_CONTENT = '[comment]';

    /**
     * @var mixed[]
     */
    public $skipPatterns = [
        T_FUNCTION => ['/provide[A-Z]/'],
    ];

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
                self::ARRAY_KEYS_SORT_ALPHABETICALLY
            );
            $phpcsFile->fixer->endChangeset();

            return;
        }

        /** @var \PhpParser\Node\Stmt\Expression $stmtExpr */
        $stmtExpr = $ast[0];
        /** @var \PhpParser\Node\Expr\Array_ $array */
        $array = $stmtExpr->expr;

        $array = $this->refactor($array);

        if ($array !== null && $array->hasAttribute('isChanged') === false) {
            return;
        }

        $prettyPrinter = new Standard();
        $newContent = $prettyPrinter->prettyPrint([$array]);
        $newContent = \str_replace('    ' . self::COMMENT_CONTENT . PHP_EOL, '', $newContent);

        $phpcsFile->fixer->beginChangeset();

        $fix = $phpcsFile->addFixableError(
            'Array\'s keys should be sorted alphabetically',
            $token['content'],
            self::ARRAY_KEYS_SORT_ALPHABETICALLY
        );

        if ($fix !== false) {
            $phpcsFile->addErrorOnLine(
                'Array\'s keys should be sorted alphabetically',
                $token['line'],
                self::ARRAY_KEYS_SORT_ALPHABETICALLY
            );

            $phpcsFile->fixer->replaceToken($bracketOpenerPointer, $newContent);
            for ($bracketOpenerPointer++; $bracketOpenerPointer <= $bracketCloserPointer; $bracketOpenerPointer++) {
                $phpcsFile->fixer->replaceToken($bracketOpenerPointer, '');
            }
        }

        $phpcsFile->fixer->endChangeset();
    }

    public function refactor(?Array_ $node = null): ?Array_
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

    /**
     * @return mixed[]
     */
    public function register(): array
    {
        return [
            T_OPEN_SHORT_ARRAY,
            T_ARRAY,
        ];
    }

    public function shouldSkip(File $phpcsFile, int $bracketOpenerPointer): bool
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

    /**
     * @param ArrayItem[] $items
     *
     * @return ArrayItem[]
     */
    private function fixMultiLineOutput(array $items): array
    {
        $currentLine = (int)\current($items)->key->getAttribute('startLine');

        foreach ($items as $index => $arrayItem) {
            if ($index === 0) {
                continue;
            }

            $nextLine = (int)$arrayItem->key->getAttribute('startLine');
            if ($nextLine !== $currentLine) {
                $arrayItem->setAttribute('comments', [new Comment(self::COMMENT_CONTENT)]);
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
}
