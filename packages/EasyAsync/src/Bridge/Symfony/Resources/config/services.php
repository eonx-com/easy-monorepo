<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Factories\JobFactory;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\JobFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Persisters\WithEventsJobPersister;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DateTimeGeneratorInterface::class, DateTimeGenerator::class);

    $services->set(JobFactoryInterface::class, JobFactory::class);

    $services->set(JobLogFactoryInterface::class, JobLogFactory::class);

    $services->set('default_job_log_updater', JobLogUpdater::class);

    $services->set(JobLogUpdaterInterface::class, WithEventsJobLogUpdater::class)
        ->bind('$decorated', service('default_job_log_updater'));

    $services->set(JobPersisterInterface::class, WithEventsJobPersister::class)
        ->bind('$decorated', service('default_job_persister'));
};
