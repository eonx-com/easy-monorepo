<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \EonX\EasyStandard\Tests\Rector\OrderArrayKeysAlphabeticallyRector\OrderArrayKeysAlphabeticallyRectorTest
 */
final class OrderArrayKeysAlphabeticallyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Sort arrays by keys alphabetically', [
            new CodeSample(
                <<<'PHP'
$array = [
    'iat' => \time(),
    'iss' => self::TEST_CONST,
    'sub' => $someObject->getValue(),
    SomeFile::TEST_CONST => 'abc',
    self::ANOTHER_TEST_CONST => [
        'fn' => $someObject->getFn(),
        'ln' => $someObject->getLn(),
        'email' => $someObject->getEmail(),
        'phone' => $someObject->getPhone(),
    ],
    'aud' => self::ANOTHER_TEST_CONST,
    'exp' => \time() + 3600,
];
PHP
                ,
                <<<'PHP'
$array = [
    'aud' => self::ANOTHER_TEST_CONST,
    'exp' => \time() + 3600,
    'iat' => \time(),
    'iss' => self::TEST_CONST,
    self::ANOTHER_TEST_CONST => [
        'email' => $someObject->getEmail(),
        'fn' => $someObject->getFn(),
        'ln' => $someObject->getLn(),
        'phone' => $someObject->getPhone(),
    ],
    SomeFile::TEST_CONST => 'abc',
    'sub' => $someObject->getValue(),
];
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var \PhpParser\Node\Expr\Array_ $node */
        if ($this->isAssociativeOnly($node)) {
            $node->items = $this->getSortedItems($node);
        }

        return $node;
    }

    private function getArrayKeyAsString(ArrayItem $node): string
    {
        switch ($node->key->getType()) {
            case 'Expr_ClassConstFetch':
                /** @var \PhpParser\Node\Expr\ClassConstFetch $keyNode */
                $keyNode = $node->key;
                /** @var \PhpParser\Node\Name $nameNode */
                $nameNode = $keyNode->class;
                $name = $nameNode->getLast() . '::' . $keyNode->name->name;
                break;
            default:
                $name = \trim($this->print($node->key), " \t\n\r\0\x0B\"'");
        }

        return \strtolower($name);
    }

    /**
     * @return ArrayItem[]
     */
    private function getSortedItems(Array_ $array): array
    {
        $items = $array->items;
        usort($items, function (ArrayItem $firstItem, ArrayItem $secondItem): int {
            $firstName = $this->getArrayKeyAsString($firstItem);
            $secondName = $this->getArrayKeyAsString($secondItem);

            return $firstName <=> $secondName;
        });

        return $items;
    }

    /**
     * @param \PhpParser\Node\Expr\Array_ $array
     *
     * @return bool
     */
    private function isAssociativeOnly(Array_ $array): bool
    {
        $isAssociative = 1;

        foreach ($array->items as $arrayItem) {
            $isAssociative &= $arrayItem->key !== null;
        }

        return (bool)$isAssociative;
    }
}
