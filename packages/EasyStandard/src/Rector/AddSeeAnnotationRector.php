<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AddSeeAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private const DATA_PROVIDER_TAG = 'dataProvider';

    /**
     * @var string
     */
    private const SEE_TAG = 'see';

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds @see annotation for data providers',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * Provides some data.
 * 
 * @return mixed[]
*/
public function provideSomeData(): array
{
}
PHP
                    ,
                    <<<'PHP'
/**
 * Provides some data.
 * 
 * @return mixed[]
 * 
 * @see testMethod
*/
public function provideSomeData(): array
{
}
PHP
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * {@inheritDoc}
     *
     * @param \PhpParser\Node $node
     *
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isInTestClass($node) === false) {
            return null;
        }

        /** @var \PhpParser\Node\Stmt\Class_ $class */
        $class = $node;

        $this->checkTestMethodsWithDataProvider($class);

        return $node;
    }

    /**
     * Checks dataProvider method has `@see` annotation with test method name.
     */
    private function checkDataProviderMethod(ClassMethod $dataProviderMethod, string $testMethodName): void
    {
        /** @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $dataProviderDocs */
        $dataProviderDocs = $dataProviderMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

        if ($dataProviderDocs->hasByName(self::SEE_TAG) === false) {
            if ($dataProviderDocs->isEmpty() === false) {
                $emptyLine = new AttributeAwarePhpDocTagNode('', new GenericTagValueNode(''));
                $dataProviderDocs->addPhpDocTagNode($emptyLine);
            }

            $dataProviderDocs->addPhpDocTagNode($this->createSeePhpDocTagNode($testMethodName));

            return;
        }

        if (Strings::match($dataProviderDocs->getOriginalContent(), '/(@see ' . $testMethodName . ')(.*?)/') === null) {
            $dataProviderDocs->addPhpDocTagNode($this->createSeePhpDocTagNode($testMethodName));
        }
    }

    /**
     * Checks test method.
     */
    private function checkTestMethod(Class_ $class, ClassMethod $classMethod): void
    {
        /** @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

        $dataProviderTags = $phpDocInfo->getTagsByName(self::DATA_PROVIDER_TAG);

        if ($dataProviderTags === []) {
            return;
        }

        foreach ($dataProviderTags as $dataProviderTag) {
            $dataProviderMethod = $class->getMethod((string)$dataProviderTag->value);

            if ($dataProviderMethod === null) {
                continue;
            }

            $this->checkDataProviderMethod($dataProviderMethod, (string)$classMethod->name);
        }
    }

    /**
     * Checks test methods with @dataProvider.
     */
    private function checkTestMethodsWithDataProvider(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->shouldSkipMethod($classMethod)) {
                continue;
            }

            $this->checkTestMethod($class, $classMethod);
        }
    }

    /**
     * Creates `@see` PHPDoc tag.
     */
    private function createSeePhpDocTagNode(string $testMethod): PhpDocTagNode
    {
        return new AttributeAwarePhpDocTagNode('@' . self::SEE_TAG, new GenericTagValueNode($testMethod));
    }

    /**
     * Returns true if method should be skipped.
     */
    private function shouldSkipMethod(ClassMethod $classMethod): bool
    {
        $shouldSkip = false;

        if ($classMethod->isPublic() === false) {
            $shouldSkip = true;
        }

        if (Strings::startsWith((string)$classMethod->name, 'test') === false) {
            $shouldSkip = true;
        }

        if ($classMethod->getDocComment() === null) {
            $shouldSkip = true;
        }

        return $shouldSkip;
    }
}
