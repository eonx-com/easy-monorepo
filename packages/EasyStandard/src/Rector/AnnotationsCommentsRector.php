<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\AnnotationsCommentsRectorTest
 */
final class AnnotationsCommentsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $allowedEnd = ['.', '?'];

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

        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            /** @var PhpDocChildNode $phpDocChildNode */
            $content = (string) $phpDocChildNode;
            if (Strings::match($content, '#inheritdoc#i')) {
                continue;
            }

            if ($phpDocChildNode instanceof AttributeAwarePhpDocTextNode) {
                $this->checkTextNode($phpDocChildNode);
            }

            if ($phpDocChildNode instanceof AttributeAwarePhpDocTagNode) {
                $this->checkTagNode($phpDocChildNode);
            }
        }

        return $node;
    }

    private function checkTagNode(AttributeAwarePhpDocTagNode $child): void
    {
        if ($child->value instanceof AttributeAwareGenericTagValueNode === false) {
            return;
        }

        $tagValueNode = $child->value;
        if ($tagValueNode->value === null) {
            return;
        }

        if (\in_array(\substr($tagValueNode->value, -1), $this->allowedEnd, true)) {
            $tagValueNode->value = \substr($tagValueNode->value, 0, -1);
        }
    }

    private function checkTextNode(AttributeAwarePhpDocTextNode $child): void
    {
        if ($child->text === '') {
            return;
        }

        if (\in_array(\substr($child->text, -1), $this->allowedEnd, true) === false) {
            $child->text .= '.';
        }
    }
}
