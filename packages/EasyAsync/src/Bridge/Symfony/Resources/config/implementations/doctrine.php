<?php

declare(strict_types=1);

use EonX\EasyAsync\Implementations\Doctrine\DBAL\DataCleaner;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobLogPersister;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobPersister;
use EonX\EasyAsync\Interfaces\DataCleanerInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DataCleanerInterface::class, DataCleaner::class);

    $services->set(JobLogPersisterInterface::class, JobLogPersister::class)
        ->bind('$table','%easy_async_job_logs_table%');

    $services->set('default_job_persister', JobPersister::class)
        ->bind('$table', '%easy_async_jobs_table%');
};
