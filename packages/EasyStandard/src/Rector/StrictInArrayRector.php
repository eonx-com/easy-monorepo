<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @SuppressWarnings("unused") Class is used by Rector
 */
final class StrictInArrayRector extends AbstractRector
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Makes in_array calls strict', [
            new CodeSample('in_array($value, $items);', 'in_array($value, $items, true);'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * {@inheritdoc}
     */
    public function refactor(Node $node): ?Node
    {
        /** @var \PhpParser\Node\Expr\FuncCall $funcCall */
        $funcCall = $node;

        if ($this->isName($node, 'in_array') === false) {
            return null;
        }

        if (\count($funcCall->args) === 2) {
            $funcCall->args[2] = $this->createArg($this->createTrue());
        }

        return $node;
    }
}
