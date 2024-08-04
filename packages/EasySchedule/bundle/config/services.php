<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySchedule\Command\ScheduleRunCommand;
use EonX\EasySchedule\DataCollector\ScheduleDataCollector;
use EonX\EasySchedule\Runner\ScheduleRunner;
use EonX\EasySchedule\Runner\ScheduleRunnerInterface;
use EonX\EasySchedule\Schedule\Schedule;
use EonX\EasySchedule\Schedule\ScheduleInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ScheduleRunCommand::class);

    $services->set(ScheduleDataCollector::class);

    $services->set(ScheduleInterface::class, Schedule::class);

    $services->set(ScheduleRunnerInterface::class, ScheduleRunner::class);
};
