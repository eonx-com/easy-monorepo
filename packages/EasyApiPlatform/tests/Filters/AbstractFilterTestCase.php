<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Filters;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyApiPlatform\Tests\Stubs\App\ApiResource\Dummy;
use EonX\EasyApiPlatform\Tests\Stubs\App\Kernel\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractFilterTestCase extends KernelTestCase
{
    protected string $alias = 'o';

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
        self::bootKernel();

        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $managerRegistry */
        $managerRegistry = self::$kernel->getContainer()->get('doctrine');

        $this->managerRegistry = $managerRegistry;
        /** @var \Doctrine\Persistence\ObjectManager $manager */
        $manager = $this->managerRegistry->getManagerForClass(Dummy::class);
        /** @var \Doctrine\ORM\EntityRepository<\stdClass> $repository */
        $repository = $manager->getRepository(Dummy::class);
        $this->repository = $repository;
    }

    /**
     * @return iterable<mixed>
     */
    abstract public function provideApplyTestData(): iterable;

    /**
     * @param mixed[]|null $properties
     * @param mixed[] $filterParameters
     * @param mixed[] $expectedParameters
     * @param class-string<\stdClass>|null $resourceClass
     *
     * @dataProvider provideApplyTestData
     */
    public function testApply(
        ?array $properties,
        array $filterParameters,
        string $expectedDql,
        array $expectedParameters = null,
        callable $filterFactory = null,
        string $resourceClass = null
    ): void {
        if ($filterFactory === null) {
            $filterFactory = function (ManagerRegistry $managerRegistry, array $properties = null): FilterInterface {
                $filterClass = $this->filterClass;

                return new $filterClass($managerRegistry, null, $properties);
            };
        }

        $repository = $this->repository;
        if ($resourceClass) {
            /** @var \Doctrine\Persistence\ObjectManager $manager */
            $manager = $this->managerRegistry->getManagerForClass($resourceClass);
            /** @var \Doctrine\ORM\EntityRepository<\stdClass> $repository */
            $repository = $manager->getRepository($resourceClass);
        }
        $resourceClass = $resourceClass ?: $this->resourceClass;
        $queryBuilder = $repository->createQueryBuilder($this->alias);
        $filterCallable = $filterFactory($this->managerRegistry, $properties);
        $filterCallable->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            $resourceClass,
            null,
            ['filters' => $filterParameters]
        );

        $this->assertEquals($expectedDql, $queryBuilder->getQuery()->getDQL());

        if ($expectedParameters === null) {
            return;
        }

        foreach ($expectedParameters as $parameterName => $expectedParameterValue) {
            /** @var \Doctrine\ORM\Query\Parameter $queryParameter */
            $queryParameter = $queryBuilder->getQuery()
                ->getParameter($parameterName);

            $this->assertNotNull($queryParameter, \sprintf('Expected query parameter "%s" to be set', $parameterName));
            $this->assertEquals(
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

    /**
     * @param mixed[] $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true);
    }
}
