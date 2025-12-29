<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    protected function getMethodAsPublic(string $className, string $methodName): ReflectionMethod
    {
        return (new ReflectionClass($className))->getMethod($methodName);
    }

    /**
     * @template TMock of object
     *
     * @param class-string<TMock> $target
     *
     * @return \Mockery\LegacyMockInterface&\Mockery\MockInterface&TMock
     */
    protected function mock(mixed $target, ?callable $expectations = null): object
    {
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&TMock $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }

    /**
     * @return \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Doctrine\Persistence\ManagerRegistry
     */
    protected function mockRegistry(
        ?callable $managerExpectations = null,
        ?callable $repositoryExpectations = null,
    ): object {
        return $this->mock(
            ManagerRegistry::class,
            function (LegacyMockInterface $registry) use ($managerExpectations, $repositoryExpectations): void {
                $manager = $this->mock(EntityManagerInterface::class, $managerExpectations);
                $repository = $this->mock(EntityRepository::class, $repositoryExpectations);

                $manager->shouldReceive('getRepository')
                    ->once()
                    ->with(stdClass::class)
                    ->andReturn($repository);
                $registry->shouldReceive('getManagerForClass')
                    ->once()
                    ->with(stdClass::class)
                    ->andReturn($manager);
            }
        );
    }
}
