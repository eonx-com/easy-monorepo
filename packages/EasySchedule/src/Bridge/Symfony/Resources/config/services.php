<?php

declare(strict_types=1);

use EonX\EasySchedule\Bridge\Symfony\DataCollector\ScheduleDataCollector;
use EonX\EasySchedule\Command\ScheduleRunCommand;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleRunnerInterface;
use EonX\EasySchedule\Schedule;
use EonX\EasySchedule\ScheduleRunner;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ScheduleRunCommand::class);

    $services->set(ScheduleDataCollector::class)
        ->tag('data_collector', [
            'template' => '@EasySchedule/Collector/schedule_collector.html.twig',
            'id' => 'schedule.schedule_collector',
        ]);

    $services->set(ScheduleInterface::class, Schedule::class);

    $services->set(ScheduleRunnerInterface::class, ScheduleRunner::class);
};
