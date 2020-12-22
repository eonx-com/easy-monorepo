<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Output;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\PrettyPrinter\Standard;

final class Printer extends Standard
{
    /**
     * @param \PhpParser\Node[] $stmts Array of statements
     */
    public function printNodes(array $stmts): string
    {
        return ltrim($this->handleMagicTokens($this->pStmts($stmts, false)));
    }

    public function setStartIndentLevel(int $level): void
    {
        $this->setIndentLevel($level);
    }

    protected function pExpr_Array(Array_ $node): string
    {
        $syntax = $node->getAttribute(
            'kind',
            $this->options['shortArraySyntax'] ? Array_::KIND_SHORT : Array_::KIND_LONG
        );
        /** @var \PhpParser\Node\Expr\ArrayItem[] $items */
        $items = $node->items;
        if ($syntax === Array_::KIND_SHORT) {
            return '[' . $this->pMaybeMultiline($items, true) . ']';
        }

        return 'array(' . $this->pMaybeMultiline($items, true) . ')';
    }

    /**
     * @param \PhpParser\Node[] $nodes
     */
    protected function pMaybeMultiline(array $nodes, ?bool $trailingComma = null): string
    {
        $trailingComma = $trailingComma ?? false;

        if ($this->hasMultiLineNodes($nodes) === false) {
            return $this->pCommaSeparated($nodes);
        }

        return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
    }

    /**
     * @param Node[] $nodes
     */
    private function hasMultiLineNodes(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node->hasAttribute('multiLine')) {
                return true;
            }
        }

        return false;
    }
}
