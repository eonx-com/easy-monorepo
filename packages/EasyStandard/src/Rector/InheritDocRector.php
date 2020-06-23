<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class InheritDocRector extends AbstractRector
{
    /**
     * @var string
     */
    private const INHERITDOC_INCORRECT_ANNOTATION = '{@inheritdoc}';

    /**
     * @var string
     */
    private const INHERITDOC_CORRECT_ANNOTATION = '{@inheritDoc}';

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var \PhpParser\Node\Stmt\ClassMethod $classMethod */
        $classMethod = $node;

        /** @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

        /** @var AttributeAwarePhpDocTextNode[] $children */
        $children = $phpDocInfo->getPhpDocNode()->children;

        foreach ($children as $key => $child) {
            if ($child->getAttribute('original_content') === self::INHERITDOC_INCORRECT_ANNOTATION) {
                $children[$key]->text = self::INHERITDOC_CORRECT_ANNOTATION;
            }
        }

        $phpDocInfo->getPhpDocNode()->children = $children;

        return $classMethod;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Replaces {@inheritdoc} annotation with {@inheritDoc}.',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * {@inheritdoc}
*/
public function provideSomeData(): array
{
}
PHP
                    ,
                    <<<'PHP'
public function provideSomeData(): array
{
}
PHP
                ),
            ]
        );
    }
}
