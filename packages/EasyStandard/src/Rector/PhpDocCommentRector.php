<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
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
    public $allowedEnd = ['.', ',', '?', '!', ':', ')', '(', '}', '{', ']', '['];

    /**
     * @var bool
     */
    private $isMultilineTagNode = false;

    /**
     * @var bool
     */
    private $isMultilineTextNode = false;

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
 * some class
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
        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node->hasAttribute(AttributeKey::PHP_DOC_INFO)) {
            $this->checkPhpDoc($node->getAttribute(AttributeKey::PHP_DOC_INFO));
        }

        return $node;
    }

    private function checkGenericTagValueNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode): void
    {
        if ($this->isMultilineTagNode && Strings::startsWith($attributeAwarePhpDocTagNode->name, '@')) {
            return;
        }

        /** @var GenericTagValueNode $value */
        $value = $attributeAwarePhpDocTagNode->value;
        if (isset($value->value) === false) {
            return;
        }

        $checkLastLetter = Strings::endsWith($value->value, ')');
        $checkFirstLetter = Strings::startsWith($value->value, '(') || Strings::startsWith($value->value, '\\');

        if ($checkFirstLetter && $checkLastLetter) {
            return;
        }

        $valueAsArray = (array)\explode(')', $value->value);

        if (\count($valueAsArray) === 2) {
            if ($this->isLineEndingWithAllowed($valueAsArray[1])) {
                $valueAsArray[1] = Strings::substring($valueAsArray[1], 0, -1);
            }

            $valueAsArray[1] = Strings::firstLower(Strings::trim($valueAsArray[1]));

            $newValue = implode(') ', $valueAsArray);

            if ($value->value !== $newValue) {
                $firstValueLetter = Strings::substring($value->value, 0, 1);

                if (\in_array($firstValueLetter, ['\\', '('], true) === false) {
                    $attributeAwarePhpDocTagNode->name .= ' ';
                }

                $value->value = $newValue;
            }
        }
    }

    /**
     * @param mixed[] $children
     */
    private function checkIsMultilineNode(array $children, int $index): void
    {
        $phpDocChildNode = $children[$index];

        if ($phpDocChildNode instanceof AttributeAwarePhpDocTextNode) {
            if ($this->isMultilineTagNode && \in_array($phpDocChildNode->text, ['', ')'], true)) {
                $this->isMultilineTagNode = false;
            }

            $nextChildren = $children[$index + 1] ?? null;

            if ($nextChildren === null) {
                $this->isMultilineTextNode = false;

                return;
            }

            if ($nextChildren instanceof AttributeAwarePhpDocTextNode) {
                if ($nextChildren->text !== '') {
                    $this->isMultilineTextNode = true;
                }

                if ($nextChildren->text === '') {
                    $this->isMultilineTextNode = false;
                }
            }

            if ($nextChildren instanceof AttributeAwarePhpDocTagNode) {
                $this->isMultilineTextNode = false;
            }
        }

        if ($phpDocChildNode instanceof AttributeAwarePhpDocTagNode) {
            $value = $phpDocChildNode->value;
            $nextChildren = $children[$index + 1] ?? null;

            if ((isset($value->value) && $value->value === '') || $nextChildren === null) {
                $this->isMultilineTagNode = false;

                return;
            }

            if ($value instanceof AttributeAwareGenericTagValueNode) {
                $containsEol = Strings::contains($value->value, \PHP_EOL);
                $lastLetter = Strings::substring($value->value, -1, 1);
                if ($containsEol || \in_array($lastLetter, ['(', '{'], true)) {
                    $this->isMultilineTagNode = true;
                }
            }

            if ($nextChildren instanceof AttributeAwarePhpDocTextNode) {
                if ($nextChildren->text !== '') {
                    $this->isMultilineTagNode = true;
                }

                if ($nextChildren->text === '') {
                    $this->isMultilineTagNode = false;
                }
            }
        }
    }

    private function checkPhpDoc(PhpDocInfo $phpDocInfo): void
    {
        $children = $phpDocInfo->getPhpDocNode()
            ->children;

        /** @var PhpDocChildNode $phpDocChildNode */
        foreach ($children as $index => $phpDocChildNode) {
            $this->checkIsMultilineNode($children, $index);
            $this->checkPhpDocChildNode($phpDocChildNode);
        }
    }

    private function checkPhpDocChildNode(PhpDocChildNode $phpDocChildNode): void
    {
        if ($phpDocChildNode instanceof AttributeAwarePhpDocTextNode) {
            $this->checkTextNode($phpDocChildNode);
        }

        if ($phpDocChildNode instanceof AttributeAwarePhpDocTagNode) {
            $this->checkTagNode($phpDocChildNode);
        }
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
        if ($this->isMultilineTagNode || $attributeAwarePhpDocTextNode->text === '') {
            return;
        }

        $text = (array)\explode(PHP_EOL, $attributeAwarePhpDocTextNode->text);
        $firstKey = array_key_first($text);
        $lastKey = array_key_last($text);

        foreach ($text as $index => $value) {
            $text[$index] = Strings::trim($value);
        }

        $text[$firstKey] = Strings::firstUpper($text[$firstKey]);

        if ($this->isMultilineTextNode === false && $this->isLineEndingWithAllowed($text[$lastKey]) === false) {
            $text[$lastKey] .= '.';
        }

        $newText = \implode(PHP_EOL, $text);

        if ($newText !== $attributeAwarePhpDocTextNode->getAttribute('original_content')) {
            $attributeAwarePhpDocTextNode->text = $newText;
            $attributeAwarePhpDocTextNode->setAttribute('original_content', '');
        }
    }

    private function checkVarTagValueNode(AttributeAwarePhpDocTagNode $attributeAwarePhpDocTagNode): void
    {
        /** @var AttributeAwareVarTagValueNode $varTagValueNode */
        $varTagValueNode = $attributeAwarePhpDocTagNode->value;

        if ($varTagValueNode->description === '' || $varTagValueNode->variableName === '') {
            return;
        }

        $newDescription = Strings::firstLower(Strings::trim($varTagValueNode->description));

        if ($this->isLineEndingWithAllowed($newDescription)) {
            $newDescription = Strings::substring($newDescription, 0, -1);
        }

        if ($newDescription !== $varTagValueNode->description) {
            $varTagValueNode->description = $newDescription;
        }
    }

    private function isLineEndingWithAllowed(string $docLineContent): bool
    {
        $lastCharacter = Strings::substring($docLineContent, -1);

        return \in_array($lastCharacter, $this->allowedEnd, true);
    }
}
