<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasySchedule\Runner;

use Carbon\Carbon;
use EonX\EasySwoole\Caching\Helper\CacheTableHelper;
use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;
use EonX\EasySwoole\Common\Helper\AppRuntimeHelper;
use EonX\EasySwoole\Common\Helper\OptionHelper;
use EonX\EasySwoole\Common\Helper\SwooleTableHelper;
use EonX\EasySwoole\Common\ValueObject\SwooleTableColumnDefinition;
use Swoole\Http\Server;
use Swoole\Process;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Runtime\RunnerInterface;

final readonly class EasyScheduleSwooleRunner implements RunnerInterface
{
    public const ENABLED = 'EASY_SCHEDULE_ENABLED';

    private const LAST_RUN = 'last_run';

    public function __construct(
        private Application $application,
    ) {
    }

    public function run(): int
    {
        $app = $this->application;
        $server = new Server('0.0.0.0', 8080, \SWOOLE_BASE, \SWOOLE_SOCK_TCP);

        CacheTableHelper::createCacheTables(
            OptionHelper::getArray('cache_tables', 'SWOOLE_CACHE_TABLES'),
            OptionHelper::getInteger('cache_clear_after_tick_count', 'SWOOLE_CACHE_CLEAR_AFTER_TICK_COUNT'),
        );

        $server->on(AppRuntimeHelper::EVENT_REQUEST, static function (): void {
        });

        $table = SwooleTableHelper::create(
            size: 1,
            columnDefinitions: [
                new SwooleTableColumnDefinition(
                    name: self::LAST_RUN,
                    type: SwooleTableColumnType::String,
                    size: 15,
                ),
            ],
        );

        $server->addProcess(new Process(static function () use ($app, $table): void {
            $now = Carbon::now('UTC')->format('YmdHi');
            $lastRun = $table->exists(self::LAST_RUN) ? $table->get(self::LAST_RUN, self::LAST_RUN) : null;

            // Run schedule only once per minute
            if ($lastRun === $now) {
                \sleep(OptionHelper::getInteger('schedule_sleep', 'SWOOLE_SCHEDULE_SLEEP'));

                return;
            }

            $table->set(self::LAST_RUN, [self::LAST_RUN => $now]);

            $app->run(new ArrayInput(['command' => 'schedule:run']));

            CacheTableHelper::tick();
        }));

        $server->start();

        return 0;
    }
}
