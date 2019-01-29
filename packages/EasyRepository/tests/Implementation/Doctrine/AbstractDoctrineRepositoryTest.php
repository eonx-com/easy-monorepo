<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Mockery\Mock;
use Mockery\MockInterface;
use StepTheFkUp\EasyRepository\Tests\AbstractTestCase;

final class AbstractDoctrineRepositoryTest extends AbstractTestCase
{
    /**
     * Repository should return exactly what the Doctrine repository returns.
     *
     * @return void
     */
    public function testAllReturnsExpectedArray(): void
    {
        $expected = [new \stdClass(), new \stdClass()];

        /** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
        $registry = $this->mockRegistry(null, function (MockInterface $repo) use ($expected): void {
            $repo->shouldReceive('findAll')->once()->withNoArgs()->andReturn($expected);
        });

        self::assertEquals($expected, (new DoctrineRepositoryStub($registry))->all());
    }

    /**
     * Repository should call the Doctrine object manager to delete given objects.
     *
     * @return void
     */
    public function testDeleteCallsRemoveOnObjectManager(): void
    {
        $tests = [new \stdClass(), [new \stdClass(), new \stdClass()]];

        foreach ($tests as $test) {
            /** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry($this->getManagerExpectations('remove', $test));

            (new DoctrineRepositoryStub($registry))->delete($test);
        }
    }

    /**
     * Repository should return exactly what the Doctrine repository returns.
     *
     * @return void
     */
    public function testFindReturnsExpectedValues(): void
    {
        $expected = ['found' => new \stdClass(), null];

        foreach ($expected as $identifier => $object) {
            /** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry(null, function (MockInterface $repo) use ($identifier, $object): void {
                $repo->shouldReceive('find')->once()->with($identifier)->andReturn($object);
            });

            self::assertEquals($object, (new DoctrineRepositoryStub($registry))->find($identifier));
        }
    }

    /**
     * Repository should call the Doctrine object manager to save given objects.
     *
     * @return void
     */
    public function testSaveCallsRemoveOnObjectManager(): void
    {
        $tests = [new \stdClass(), [new \stdClass(), new \stdClass()]];

        foreach ($tests as $test) {
            /** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry($this->getManagerExpectations('persist', $test));

            (new DoctrineRepositoryStub($registry))->save($test);
        }
    }

    /**
     * Get Doctrine object manager expectations for given method and objects.
     *
     * @param string $method
     * @param object|object[] $objects
     *
     * @return \Closure
     */
    private function getManagerExpectations(string $method, $objects): \Closure
    {
        return function (MockInterface $manager) use ($method, $objects): void {
            $times = \is_array($objects) ? \count($objects) : 1;

            $manager->shouldReceive($method)->times($times)->withArgs(function ($object): bool {
                self::assertInstanceOf(\stdClass::class, $object);

                return $object instanceof \stdClass;
            });
            $manager->shouldReceive('flush')->once()->withNoArgs();
        };
    }

    /**
     * Mock Doctrine manager registry for given manager and repository expectations.
     *
     * @param callable|null $managerExpectations
     * @param callable|null $repositoryExpectations
     *
     * @return \Mockery\MockInterface
     */
    private function mockRegistry(
        ?callable $managerExpectations = null,
        ?callable $repositoryExpectations = null
    ): MockInterface {
        $registry = $this->mock(
            ManagerRegistry::class,
            function (MockInterface $registry) use ($managerExpectations, $repositoryExpectations): void {
                $manager = $this->mock(ObjectManager::class, $managerExpectations);
                $repository = $this->mock(ObjectRepository::class, $repositoryExpectations);

                $manager->shouldReceive('getRepository')->once()->with('my-entity-class')->andReturn($repository);
                $registry->shouldReceive('getManager')->once()->with('my-entity-class')->andReturn($manager);
            });

        return $registry;
    }
}