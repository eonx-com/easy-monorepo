<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasySchedule;

use Carbon\Carbon;
use Swoole\Constant;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Table;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Runtime\RunnerInterface;

final class EasyScheduleSwooleRunner implements RunnerInterface
{
    public const ENABLED = 'EASY_SCHEDULE_ENABLED';

    private const LAST_RUN = 'last_run';

    public function __construct(private readonly Application $application)
    {
    }

    public function run(): int
    {
        $app = $this->application;
        $server = new Server('0.0.0.0', 8080, \SWOOLE_BASE, \SWOOLE_SOCK_TCP);
        $server->on(Constant::EVENT_REQUEST, static function (): void {
        });

        $table = new Table(1);
        $table->column(self::LAST_RUN, Table::TYPE_STRING, 15);
        $table->create();

        $server->addProcess(new Process(static function () use ($app, $table): void {
            $now = Carbon::now('UTC')->format('YmdHi');
            $lastRun = $table->exists(self::LAST_RUN) ? $table->get(self::LAST_RUN, self::LAST_RUN) : null;

            // Run schedule only once per minute
            if ($lastRun === $now) {
                return;
            }

            $table->set(self::LAST_RUN, [self::LAST_RUN => $now]);

            $app->run(new ArrayInput(['command' => 'schedule:run']));
        }));

        $server->start();

        return 0;
    }
}
