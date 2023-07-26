<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Command;

use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleRunnerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ScheduleRunCommand extends Command
{
    public function __construct(
        private ScheduleRunnerInterface $runner,
        private ScheduleInterface $schedule,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('schedule:run')
            ->setDescription('Run scheduled commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->schedule->setApplication($this->getApplication());

        $this->runner->run($this->schedule, $output);

        return 0;
    }
}
