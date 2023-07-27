<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySchedule\Bridge\Symfony\DataCollector\ScheduleDataCollector;
use EonX\EasySchedule\Command\ScheduleRunCommand;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleRunnerInterface;
use EonX\EasySchedule\Schedule;
use EonX\EasySchedule\ScheduleRunner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ScheduleRunCommand::class);

    $services->set(ScheduleDataCollector::class)
        ->tag('data_collector', [
            'id' => 'schedule.schedule_collector',
            'template' => '@EasyScheduleSymfony/Collector/schedule_collector.html.twig',
        ]);

    $services->set(ScheduleInterface::class, Schedule::class);

    $services->set(ScheduleRunnerInterface::class, ScheduleRunner::class);
};
