<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

final class RestoreDefaultNullToNullableTypeParameterRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add null default to parameters with PHP 7.4 nullable type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct(?string $value)
    {
         $this->value = $value;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function __construct(?string $value = null)
    {
         $this->value = $value;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES) === false) {
            return null;
        }

        foreach ($node->params as $param) {
            if ($this->shouldSkip($param)) {
                continue;
            }

            $param->default = $this->createNull();
        }

        return $node;
    }

    private function shouldSkip(Param $param): bool
    {
        if ($param->type instanceof NullableType === false) {
            return true;
        }

        return $param->default !== null;
    }
}
