<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Unit\Common\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyApiPlatform\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class AbstractFilterTestCase extends AbstractUnitTestCase
{
    protected static string $alias = 'o';

    /**
     * @var class-string<\ApiPlatform\Doctrine\Orm\Filter\FilterInterface>
     */
    protected string $filterClass;

    protected Registry $managerRegistry;

    /**
     * @var \Doctrine\ORM\EntityRepository<\stdClass>
     */
    protected EntityRepository $repository;

    protected string $resourceClass;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $managerRegistry */
        $managerRegistry = self::getContainer()->get('doctrine');

        $this->managerRegistry = $managerRegistry;
        /** @var \Doctrine\Persistence\ObjectManager $manager */
        $manager = $this->managerRegistry->getManagerForClass($this->resourceClass);
        /** @var \Doctrine\ORM\EntityRepository<\stdClass> $repository */
        $repository = $manager->getRepository($this->resourceClass);
        $this->repository = $repository;
    }

    /**
     * @see testApply
     */
    abstract public static function provideApplyTestData(): iterable;

    /**
     * @param class-string<\stdClass>|null $resourceClass
     */
    #[DataProvider('provideApplyTestData')]
    public function testApply(
        ?array $properties,
        array $filterParameters,
        string $expectedDql,
        ?array $expectedParameters = null,
        ?callable $filterFactory = null,
        ?string $resourceClass = null,
    ): void {
        if ($filterFactory === null) {
            $filterFactory = function (ManagerRegistry $managerRegistry, ?array $properties = null): FilterInterface {
                $filterClass = $this->filterClass;

                return new $filterClass($managerRegistry, null, $properties);
            };
        }

        $repository = $this->repository;
        if ($resourceClass !== null) {
            /** @var \Doctrine\Persistence\ObjectManager $manager */
            $manager = $this->managerRegistry->getManagerForClass($resourceClass);
            /** @var \Doctrine\ORM\EntityRepository<\stdClass> $repository */
            $repository = $manager->getRepository($resourceClass);
        }
        $resourceClass = $resourceClass ?: $this->resourceClass;
        $queryBuilder = $repository->createQueryBuilder(static::$alias);
        $filterCallable = $filterFactory($this->managerRegistry, $properties);
        $filterCallable->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $resourceClass,
            null,
            ['filters' => $filterParameters]
        );

        static::assertEquals($expectedDql, $queryBuilder->getQuery()->getDQL());

        if ($expectedParameters === null) {
            return;
        }

        self::assertCount(
            $queryBuilder->getQuery()->getParameters()->count(),
            $expectedParameters,
            'Please assert query parameters.'
        );

        foreach ($expectedParameters as $parameterName => $expectedParameterValue) {
            /** @var \Doctrine\ORM\Query\Parameter $queryParameter */
            $queryParameter = $queryBuilder->getQuery()
                ->getParameter($parameterName);

            static::assertNotNull($queryParameter, \sprintf('Expected query parameter "%s" to be set', $parameterName));
            static::assertEquals(
                $expectedParameterValue,
                $queryParameter->getValue(),
                \sprintf(
                    'Expected query parameter "%s" to be "%s"',
                    $parameterName,
                    \var_export($expectedParameterValue, true)
                )
            );
        }
    }
}
