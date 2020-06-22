<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class UselessSingleAnnotationRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $annotations;

    /**
     * @param string[] $annotations
     */
    public function __construct(array $annotations = [])
    {
        $this->annotations = $annotations;
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var \PhpParser\Node\Stmt\ClassMethod $classMethod */
        $classMethod = $node;

        /** @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $dataProviderDocs */
        $dataProviderDocs = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

        /** @var AttributeAwareNodeInterface[] $children */
        $children = $dataProviderDocs->getPhpDocNode()->children;

        if (\count($children) === 1 &&
            \in_array($children[0]->getAttribute('original_content'), $this->annotations, true)) {
            $dataProviderDocs->getPhpDocNode()->children = [];
        }

        return $classMethod;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes PHPDoc completely if {@inheritDoc} is the only annotation presented.',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * {@inheritDoc}
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
