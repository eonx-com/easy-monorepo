<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasySchedule\Runner;

use Carbon\Carbon;
use EonX\EasySwoole\Caching\Helper\CacheTableHelper;
use EonX\EasySwoole\Common\Enum\SwooleServerEvent;
use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;
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

    private const COLUMN_NAME_LAST_RUN = 'last_run';

    private const KEY_LAST_RUN_AT = 'last_run_at';

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

        $server->on(SwooleServerEvent::Request->value, static function (): void {
        });

        $table = SwooleTableHelper::create(
            size: 1,
            columnDefinitions: [
                new SwooleTableColumnDefinition(
                    name: self::COLUMN_NAME_LAST_RUN,
                    type: SwooleTableColumnType::String,
                    size: 15,
                ),
            ],
        );

        $server->addProcess(new Process(static function () use ($app, $table): void {
            $now = Carbon::now('UTC')->format('YmdHi');
            $lastRunAt = $table->exists(self::KEY_LAST_RUN_AT)
                ? $table->get(self::KEY_LAST_RUN_AT, self::COLUMN_NAME_LAST_RUN)
                : null;

            // Run schedule only once per minute
            if ($lastRunAt === $now) {
                \sleep(OptionHelper::getInteger('schedule_sleep', 'SWOOLE_SCHEDULE_SLEEP'));

                return;
            }

            $table->set(self::KEY_LAST_RUN_AT, [self::COLUMN_NAME_LAST_RUN => $now]);

            $app->run(new ArrayInput(['command' => 'schedule:run']));

            CacheTableHelper::tick();
        }));

        $server->start();

        return 0;
    }
}
