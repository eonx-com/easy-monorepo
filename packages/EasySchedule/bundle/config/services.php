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

    $services->set(ScheduleDataCollector::class)
        ->tag('data_collector', [
            'id' => 'schedule.schedule_collector',
            'template' => '@EasySchedule/collector/schedule_collector.html.twig',
        ]);

    $services->set(ScheduleInterface::class, Schedule::class);

    $services->set(ScheduleRunnerInterface::class, ScheduleRunner::class);
};
