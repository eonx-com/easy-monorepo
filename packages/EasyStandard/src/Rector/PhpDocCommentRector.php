<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \EonX\EasyStandard\Tests\Rector\PhpDocCommentRector\PhpDocCommentRectorTest
 */
final class PhpDocCommentRector extends AbstractRector
{
    /**
     * @var string[]
     */
    public $allowedEnd = ['.', ',', '?', ':', ')', '(', '}', '{', ']', '['];

    /**
     * @var bool
     */
    private $isMultilineTagNode;

    public function checkPhpDoc(PhpDocInfo $phpDocInfo): void
    {
        $children = $phpDocInfo->getPhpDocNode()
            ->children;

        foreach ($children as $phpDocChildNode) {
            /** @var PhpDocChildNode $phpDocChildNode */
            $content = (string)$phpDocChildNode;
            if (Strings::match($content, '#inheritdoc#i')) {
                continue;
            }

            if ($phpDocChildNode instanceof AttributeAwarePhpDocTextNode) {
                if ($this->isMultilineTagNode) {
                    if (Strings::endsWith($phpDocChildNode->text, ')')) {
                        $this->isMultilineTagNode = false;
                    }

                    continue;
                }

                $this->checkTextNode($phpDocChildNode);

                continue;
            }

            if ($phpDocChildNode instanceof AttributeAwarePhpDocTagNode) {
                $this->checkTagNode($phpDocChildNode);

                continue;
            }
        }

        $this->isMultilineTagNode = false;
    }

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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->walkNodeRecursive($node);

        return $node;
    }

    private function checkGenericTagValueNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode): void
    {
        /** @var GenericTagValueNode $value */
        $value = $attributeAwarePhpDocTagNode->value;

        if (Strings::startsWith($attributeAwarePhpDocTagNode->name, '@')) {
            $this->isMultilineTagNode = true;

            if (Strings::endsWith($value->value, ')')) {
                $this->isMultilineTagNode = false;
            }

            $firstValueLetter = Strings::substring($value->value, 0, 1);

            if (\in_array($firstValueLetter, ['\\', '('], true) === false) {
                $attributeAwarePhpDocTagNode->name .= ' ';
            }

            return;
        }

        if ($value->value === '') {
            return;
        }

        if ($this->isLineEndingWithAllowed($value->value)) {
            return;
        }

        $value->value = Strings::substring($value->value, 0, -1);
    }

    private function checkTagNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode): void
    {
        if ($attributeAwarePhpDocTagNode->value instanceof AttributeAwareGenericTagValueNode) {
            $this->checkGenericTagValueNode($attributeAwarePhpDocTagNode);
        }

        if ($attributeAwarePhpDocTagNode->value instanceof AttributeAwareVarTagValueNode) {
            $this->checkVarTagValueNode($attributeAwarePhpDocTagNode);
        }
    }

    private function checkTextNode(AttributeAwarePhpDocTextNode $attributeAwarePhpDocTextNode): void
    {
        if ($attributeAwarePhpDocTextNode->text === '') {
            $this->isMultilineTagNode = false;

            return;
        }

        $text = \explode(PHP_EOL, $attributeAwarePhpDocTextNode->text);
        $firstKey = array_key_first($text);
        $lastKey = array_key_last($text);

        \array_walk($text, 'trim');

        $text[$firstKey] = Strings::firstUpper($text[$firstKey]);

        if ($this->isLineEndingWithAllowed($text[$lastKey]) === false) {
            $text[$lastKey] .= '.';
        }

        $attributeAwarePhpDocTextNode->text = \implode(PHP_EOL, $text);
        $attributeAwarePhpDocTextNode->setAttribute('original_content', '');
    }

    private function checkVarTagValueNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode): void
    {
        /** @var AttributeAwareVarTagValueNode $varTagValueNode */
        $varTagValueNode = $attributeAwarePhpDocTagNode->value;

        if ($varTagValueNode->description === '' || $varTagValueNode->variableName === '') {
            return;
        }

        $varTagValueNode->description = Strings::firstLower(\trim($varTagValueNode->description));

        if ($this->isLineEndingWithAllowed($varTagValueNode->description)) {
            $varTagValueNode->description = Strings::substring($varTagValueNode->description, 0, -1);
        }
    }

    private function isLineEndingWithAllowed(string $docLineContent): bool
    {
        $lastCharacter = Strings::substring($docLineContent, -1);

        return \in_array($lastCharacter, $this->allowedEnd, true);
    }

    private function walkNodeRecursive(Node $node): void
    {
        if ($node->hasAttribute(AttributeKey::PHP_DOC_INFO)) {
            $this->checkPhpDoc($node->getAttribute(AttributeKey::PHP_DOC_INFO));
        }

        /** @var ClassLike $node */
        if (\in_array('stmts', $node->getSubNodeNames(), true) && $node->stmts !== null) {
            foreach ($node->stmts as $stmt) {
                $this->walkNodeRecursive($stmt);
            }
        }

        /** @var Foreach_ $node */
        if (\in_array('expr', $node->getSubNodeNames(), true) && $node->expr !== null) {
            $this->walkNodeRecursive($node->expr);
        }

        /** @var FuncCall $node */
        if ($node instanceof FuncCall) {
            foreach ($node->args as $arg) {
                $this->walkNodeRecursive($arg->value);
            }
        }

        /** @var Array_ $node */
        if ($node instanceof Array_ && $node->items !== null) {
            /** @var ArrayItem|null $item */
            foreach ($node->items as $item) {
                if ($item === null) {
                    continue;
                }

                if ($item->key !== null) {
                    $this->walkNodeRecursive($item->key);
                }

                $this->walkNodeRecursive($item->value);
            }
        }
    }
}
