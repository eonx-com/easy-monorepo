<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasySchedule;

use Swoole\Constant;
use Swoole\Process;
use Swoole\Server;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Runtime\RunnerInterface;

final class EasyScheduleSwooleRunner implements RunnerInterface
{
    public const ENABLED = 'EASY_SCHEDULE_ENABLED';

    public function __construct(private readonly Application $application)
    {
    }

    public function run(): int
    {
        $app = $this->application;
        $server = new Server('0.0.0.0', 8080, \SWOOLE_BASE, \SWOOLE_SOCK_TCP);
        $server->on(Constant::EVENT_REQUEST, static function (): void {
        });

        $server->addProcess(new Process(static function () use ($app): void {
            $app->run(new ArrayInput([
                'command' => 'schedule:run',
            ]));

            \sleep(60);
        }));

        $server->start();

        return 0;
    }
}
