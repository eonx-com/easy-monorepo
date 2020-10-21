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
                $this->checkTagNode($phpDocChildNode, $phpDocContent);
            }
        }

        return $node;
    }

    private function checkTagNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode, string $phpDocContent): void
    {
        if ($attributeAwarePhpDocTagNode->value instanceof AttributeAwareGenericTagValueNode === false) {
            return;
        }

        if (Strings::startsWith($attributeAwarePhpDocTagNode->name, '@')) {
            return;
        }

        $tagValueNode = $attributeAwarePhpDocTagNode->value;
        if ($tagValueNode->value === null) {
            return;
        }

        if ($this->shouldSkipDocLine($tagValueNode->value, $phpDocContent)) {
            return;
        }

        $tagValueNode->value = \substr($tagValueNode->value, 0, -1);
    }

    private function checkTextNode(
        AttributeAwarePhpDocTextNode $attributeAwarePhpDocTextNode,
        string $phpDocContent
    ): void {
        if ($attributeAwarePhpDocTextNode->text === '') {
            return;
        }

        if ($this->shouldSkipDocLine($attributeAwarePhpDocTextNode->text, $phpDocContent)) {
            return;
        }

        $attributeAwarePhpDocTextNode->text .= '.';
    }

    private function isLineEndingWithAllowed(string $docLineContent): bool
    {
        $lastCharacter = \substr($docLineContent, -1);

        return \in_array($lastCharacter, $this->allowedEnd, true);
    }

    private function shouldSkipDocLine(string $docLineContent, string $phpDocContent): bool
    {
        if ($this->isLineEndingWithAllowed($docLineContent)) {
            return true;
        }

        $extraSpaceLineTextPattern = '#\*\s{2,}' . preg_quote($docLineContent, '#') . '#';

        return (bool) Strings::match($phpDocContent, $extraSpaceLineTextPattern);
    }
}
