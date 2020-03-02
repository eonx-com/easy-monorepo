<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\EasyAsyncExtension;
use EonX\EasyAsync\Bridge\Symfony\Events\EventDispatcher;
use EonX\EasyAsync\Exceptions\InvalidImplementationException;
use EonX\EasyAsync\Factories\JobFactory;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Generators\RamseyUuidGenerator;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\DataCleaner;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobLogPersister;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobPersister;
use EonX\EasyAsync\Interfaces\DataCleanerInterface;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use EonX\EasyAsync\Interfaces\JobFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\UuidGeneratorInterface;
use EonX\EasyAsync\Persisters\WithEventsJobPersister;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyAsyncExtensionTest extends AbstractTestCase
{
    /**
     * DataProvider for testLoad.
     *
     * @return iterable<mixed>
     */
    public function providerLoad(): iterable
    {
        yield 'Doctrine' => [
            ['implementation' => 'doctrine'],
            [
                'data_cleaner' => DataCleaner::class,
                'job_log_persister' => JobLogPersister::class,
                'job_persister' => JobPersister::class
            ],
        ];
    }

    /**
     * Extension should register expected services.
     *
     * @param mixed[] $config
     * @param mixed[] $implementationServices
     *
     * @return void
     *
     * @throws \Exception
     *
     * @dataProvider providerLoad
     */
    public function testLoad(array $config, array $implementationServices): void
    {
        $containerBuilder = new ContainerBuilder();
        $extension = new EasyAsyncExtension();

        $extension->load([$config], $containerBuilder);

        $services = [
            DateTimeGeneratorInterface::class => DateTimeGenerator::class,
            EventDispatcherInterface::class => EventDispatcher::class,
            JobFactoryInterface::class => JobFactory::class,
            JobLogFactoryInterface::class => JobLogFactory::class,
            UuidGeneratorInterface::class => RamseyUuidGenerator::class,
            'default_job_log_updater' => JobLogUpdater::class,
            DataCleanerInterface::class => $implementationServices['data_cleaner'],
            JobLogPersisterInterface::class => $implementationServices['job_log_persister'],
            'default_job_persister' => $implementationServices['job_persister'],
            JobPersisterInterface::class => WithEventsJobPersister::class,
            JobLogUpdaterInterface::class => WithEventsJobLogUpdater::class,
        ];

        foreach ($services as $abstract => $concrete) {
            self::assertTrue($containerBuilder->hasDefinition($abstract));
            self::assertEquals($concrete, $containerBuilder->getDefinition($abstract)->getClass());
        }
    }

    /**
     * Extension should throw an exception when given invalid implementation.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadWithInvalidImplementation(): void
    {
        $this->expectException(InvalidImplementationException::class);

        $containerBuilder = new ContainerBuilder();
        $extension = new EasyAsyncExtension();

        $extension->load([['implementation' => 'invalid']], $containerBuilder);
    }
}
