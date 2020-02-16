<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Listeners;

use Illuminate\Queue\Events\WorkerStopping;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class QueueWorkerStoppingListener
{
    /**
     * @var <int, string>
     */
    private static $reasons = [
        12 => 'Memory exceeded'
    ];

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $style;

    /**
     * QueueWorkerStoppingListener constructor.
     *
     * @param null|\Symfony\Component\Console\Input\InputInterface $input
     * @param null|\Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $this->style = new SymfonyStyle($input ?? new ArgvInput(), $output ?? new ConsoleOutput());
    }

    /**
     * Output worker stopping event with status.
     *
     * @param \Illuminate\Queue\Events\WorkerStopping $event
     *
     * @return void
     */
    public function handle(WorkerStopping $event): void
    {
        $reason = static::$reasons[$event->status] ?? null;

        $this->style->warning(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));
    }
}
