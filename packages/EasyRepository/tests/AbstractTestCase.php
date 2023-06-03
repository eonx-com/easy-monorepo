<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function getMethodAsPublic(string $className, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string|object $class
     */
    protected function mock($class, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }

    protected function mockRegistry(
        ?callable $managerExpectations = null,
        ?callable $repositoryExpectations = null,
    ): LegacyMockInterface {
        return $this->mock(
            ManagerRegistry::class,
            function (LegacyMockInterface $registry) use ($managerExpectations, $repositoryExpectations): void {
                $manager = $this->mock(EntityManagerInterface::class, $managerExpectations);
                $repository = $this->mock(ObjectRepository::class, $repositoryExpectations);

                $manager->shouldReceive('getRepository')
                    ->once()
                    ->with('my-entity-class')
                    ->andReturn($repository);
                $registry->shouldReceive('getManagerForClass')
                    ->once()
                    ->with('my-entity-class')
                    ->andReturn($manager);
            }
        );
    }
}
