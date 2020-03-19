<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Laravel\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use EonX\EasyAsync\Bridge\Laravel\Events\EventDispatcher;
use EonX\EasyAsync\Bridge\Laravel\Providers\EasyAsyncServiceProvider;
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
use EonX\EasyAsync\Tests\AbstractLumenTestCase;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;
use Laravel\Lumen\Application;

final class EasyAsyncServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestServiceProvider(): iterable
    {
        yield 'Doctrine' => [
            'doctrine',
            [
                'data_cleaner' => DataCleaner::class,
                'job_log_persister' => JobLogPersister::class,
                'job_persister' => JobPersister::class,
            ],
            static function (Application $app): void {
                $app->instance(Connection::class, new Connection([], new Driver()));
            },
        ];
    }

    public function testInvalidImplementation(): void
    {
        $this->expectException(InvalidImplementationException::class);

        $app = $this->createApplication();
        $app->get('config')->set('easy-async.implementation', 'invalid');

        $app->register(EasyAsyncServiceProvider::class);
    }

    /**
     * @param mixed[] $implementationServices
     *
     * @dataProvider providerTestServiceProvider
     */
    public function testServiceProvider(
        string $implementation,
        array $implementationServices,
        ?callable $dependencies = null
    ): void {
        $app = $this->createApplication();
        $app->get('config')->set('easy-async.implementation', $implementation);

        if ($dependencies !== null) {
            \call_user_func($dependencies, $app);
        }

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $serviceProvider = new EasyAsyncServiceProvider($app);
        $serviceProvider->boot();

        $app->register($serviceProvider);

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
            self::assertInstanceOf($concrete, $app->get($abstract));
        }
    }
}
