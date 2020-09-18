<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Nette\Utils\Strings;

final class AnnotationsCommentsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $ignore = [
        '{@inheritdoc}',
    ];

    /**
     * @var string[]
     */
    private $allowedEnd = [
        '.',
        '?',
    ];

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Corrects comments in annotations',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * Some class
 */
class SomeClass
{
}
PHP
                    ,
                    <<<'PHP'
/**
 * Some class.
*/
class SomeClass
{
}
PHP
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class, Property::class];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        foreach ($phpDocInfo->getPhpDocNode()->children as $child) {
            if (\in_array((string)$child, $this->ignore)) {
                continue;
            }

            if ($child instanceof AttributeAwarePhpDocTextNode) {
                $this->checkText($child);
            }

            if ($child instanceof AttributeAwarePhpDocTagNode) {
                $this->checkAttribute($child);
            }
        }

        return $node;
    }

    private function checkAttribute(AttributeAwarePhpDocTagNode $child): AttributeAwarePhpDocTagNode
    {
        if ($child->value->value === null) {
            return $child;
        }

        if (\in_array(substr($child->value->value, -1), $this->allowedEnd) === true) {
            $child->value->value = \substr($child->value->value, 0, -1);
        }

        return $child;
    }

    private function checkText(AttributeAwarePhpDocTextNode $child): AttributeAwarePhpDocTextNode
    {
        if ($child->text === '') {
            return $child;
        }

        if (\in_array(substr($child->text, -1), $this->allowedEnd) === false) {
            $child->text .= '.';
        }

        return $child;
    }
}
