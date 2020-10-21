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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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
    private $allowedEnd = ['.', '?', ':', ')', '(', '}', '{', '}'];

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

    /**
     * @param Class_|ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $phpDocContent = $phpDocInfo->getOriginalContent();

        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            /** @var PhpDocChildNode $phpDocChildNode */
            $content = (string) $phpDocChildNode;
            if (Strings::match($content, '#inheritdoc#i')) {
                continue;
            }

            if ($phpDocChildNode instanceof AttributeAwarePhpDocTextNode) {
                $this->checkTextNode($phpDocChildNode, $phpDocContent);
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

    private function checkTextNode(AttributeAwarePhpDocTextNode $attributeAwarePhpDocTextNode, string $phpDocContent): void
    {
        if ($attributeAwarePhpDocTextNode->text === '') {
            return;
        }

        if ($this->isLineEndingWithAllowed($attributeAwarePhpDocTextNode)) {
            return;
        }

        $lineText = $attributeAwarePhpDocTextNode->text;

        $extraSpaceLineTextPattern = '#\*\s{2,}' . preg_quote($lineText, '#') . '#';
        if (Strings::match($phpDocContent, $extraSpaceLineTextPattern)) {
            return;
        }

        $attributeAwarePhpDocTextNode->text .= '.';
    }

    private function isLineEndingWithAllowed(AttributeAwarePhpDocTextNode $attributeAwarePhpDocTextNode): bool
    {
        $lastCharacter = \substr($attributeAwarePhpDocTextNode->text, -1);

        return \in_array($lastCharacter, $this->allowedEnd, true);
    }
}
