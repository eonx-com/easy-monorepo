<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @codeCoverageIgnore
 *
 * @SuppressWarnings("unused") Class is used by Rector
 */
final class ExplicitBoolCompareRector extends AbstractRector
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Makes bool conditions more pretty', [
            new CodeSample(
                <<<'PHP'
final class SomeController
{
    public function run($items)
    {
        if (\is_array([]) === true) {
            return 'is array';
        }
    }
}
PHP
                ,
                <<<'PHP'
final class SomeController
{
    public function run($items)
    {
        if (\is_array([])) {
            return 'is array';
        }
    }
}
PHP
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeTypes(): array
    {
        return [If_::class, ElseIf_::class];
    }

    /**
     * {@inheritdoc}
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->cond instanceof BooleanNot) {
            $conditionNode = $node->cond->expr;
            $isNegated = true;
        } else {
            $conditionNode = $node->cond;
            $isNegated = false;
        }

        if ($this->isStaticType($conditionNode, BooleanType::class) === false) {
            return null;
        }

        $newConditionNode = $this->getNewConditionNode($isNegated, $conditionNode);

        if ($newConditionNode === null) {
            return null;
        }

        $node->cond = $newConditionNode;

        return $node;
    }

    /**
     * Returns new condition node.
     *
     * @return \PhpParser\Node\Expr
     */
    private function getNewConditionNode(bool $isNegated, Expr $expr): Expr
    {
        if ($isNegated === false) {
            $left = $expr->left;
            $right = $expr->right;

            if ($this->isValidNotNegated($left, $right) && (\mb_strtolower((string)$right->name) === 'true')) {
                return $left;
            }
        }

        if ($isNegated === true) {
            return new Expr\BinaryOp\Identical($expr, $this->createFalse());
        }

        return $expr;
    }

    /**
     * Returns true if left and right is valid not negated nodes.
     *
     * @param mixed[] $left
     * @param mixed[] $right
     *
     * @return bool
     */
    private function isValidNotNegated($left, $right): bool
    {
        return (
            ($left instanceof FuncCall || $left instanceof MethodCall || $left instanceof Instanceof_) &&
            ($right instanceof Expr\ConstFetch)
        );
    }
}
