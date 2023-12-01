<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use Carbon\CarbonImmutable;
use EonX\EasySwoole\Helpers\CacheTableHelper;
use EonX\EasySwoole\Helpers\HttpFoundationHelper;
use EonX\EasySwoole\Helpers\OptionHelper;
use EonX\EasySwoole\Helpers\OutputHelper;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Process as SwooleProcess;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Runtime\RunnerInterface;

use function Symfony\Component\String\u;

final class EasySwooleRunner implements RunnerInterface
{
    private const LOG_PATTERN = "| ---- PAGEY ---- | %s | ---- PAGEY ---- |";

    public function __construct(
        private readonly HttpKernelInterface $app,
    ) {
    }

    public function run(): int
    {
        $app = $this->app;
        $server = $this->createSwooleHttpServer();
        $responseChunkSize = OptionHelper::getInteger('response_chunk_size');

        CacheTableHelper::createCacheTables(
            OptionHelper::getArray('cache_tables'),
            OptionHelper::getInteger('cache_clear_after_tick_count'),
        );

        $server->on(
            'request',
            static function (Request $request, Response $response) use ($app, $server, $responseChunkSize): void {
                $memoryUsage = \memory_get_usage(true) / 1024 / 1024;

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'Memory: ' . $memoryUsage));

                $hfRequest = HttpFoundationHelper::fromSwooleRequest($request);

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After $hfRequest'));

                $hfRequest->attributes->set(RequestAttributesInterface::EASY_SWOOLE_ENABLED, true);
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After swoole enable attribute'));

                $hfRequest->attributes->set(
                    RequestAttributesInterface::EASY_SWOOLE_REQUEST_START_TIME,
                    CarbonImmutable::now('UTC')
                );
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After request start time attribute'));

                $hfRequest->attributes->set(RequestAttributesInterface::EASY_SWOOLE_WORKER_ID, $server->getWorkerId());
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After worker id attribute'));

                // Surround handle with output buffering to support echo, var_dump, etc
                \ob_start();
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After ob_start()'));

                try {
                    $hfResponse = $app->handle($hfRequest);
                } catch (\Throwable $throwable) {
                    OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'Throwable from $app->handle()'));
                    OutputHelper::writeln(\sprintf(self::LOG_PATTERN, $throwable->getMessage()));

                    throw $throwable;
                }

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After $app->handle()'));

                $bufferedOutput = \ob_get_contents();
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After ob_get_contents'));

                \ob_end_clean();
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After ob_end_clean'));

                HttpFoundationHelper::reflectHttpFoundationResponse(
                    $hfResponse,
                    $response,
                    $responseChunkSize,
                    \is_string($bufferedOutput) && $bufferedOutput !== '' ? $bufferedOutput : null
                );

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After HttpFoundationHelper::reflectHttpFoundationResponse()'));

                if ($app instanceof TerminableInterface) {
                    $app->terminate($hfRequest, $hfResponse);
                }

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After $app->terminate'));

                // Stop worker if app state compromised
                if ($hfRequest->attributes->get(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, false)) {
                    $server->stop($server->getWorkerId(), OptionHelper::getBoolean('worker_stop_wait_event'));
                }

                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After state compromised'));

                CacheTableHelper::tick();
                OutputHelper::writeln(\sprintf(self::LOG_PATTERN, 'After CacheTableHelper::tick()'));
            }
        );

        $server->start();

        return 0;
    }

    private function createSwooleHttpServer(): Server
    {
        $host = OptionHelper::getString('host');
        $port = OptionHelper::getInteger('port');
        $mode = OptionHelper::getInteger('mode');
        $sockType = OptionHelper::getInteger('sock_type');
        $settings = OptionHelper::getArray('settings');

        OutputHelper::writeln(\sprintf('Starting server with following config: %s', \print_r([
            'host' => $host,
            'mode' => $mode,
            'port' => $port,
            'settings' => $settings,
            'sock_type' => $sockType,
        ], true)));

        $server = new Server($host, $port, $mode, $sockType);
        $server->set($settings);

        if (OptionHelper::getBoolean('use_default_callbacks')) {
            $this->registerDefaultCallbacks($server);
        }

        foreach (OptionHelper::getArray('callbacks') as $event => $fn) {
            $server->on($event, $fn);
        }

        if (OptionHelper::getBoolean('hot_reload_enabled')) {
            $this->hotReload(
                $server,
                OptionHelper::getArray('hot_reload_dirs'),
                OptionHelper::getArray('hot_reload_extensions')
            );
        }

        return $server;
    }

    /**
     * @param string[] $dirs
     * @param string[] $extensions
     */
    private function hotReload(Server $server, array $dirs, array $extensions): void
    {
        $fswatchCheckProcess = new SymfonyProcess(['which', 'fswatch']);
        $fswatchCheckProcess->run();
        $fswatchPath = $fswatchCheckProcess->getOutput();

        if ($fswatchPath === '') {
            OutputHelper::writeln('fswatch not found, hot reload disabled');

            return;
        }

        // Format and filter dirs
        $dirs = \array_filter(\array_map(static function (string $dir): ?string {
            $realpath = \realpath($dir);

            return \is_string($realpath) ? $realpath : null;
        }, $dirs));

        if (\count($dirs) < 1) {
            OutputHelper::writeln('No directories to watch, hot reload disabled');

            return;
        }

        if (\count($extensions) < 1) {
            OutputHelper::writeln('No extensions to watch, hot reload disabled');

            return;
        }

        OutputHelper::writeln('HotReload enabled');
        OutputHelper::writeln(\sprintf('Monitoring changes in following dirs: %s', \implode(', ', $dirs)));

        $server->addProcess(new SwooleProcess(static function () use ($dirs, $extensions, $server): void {
            $cmd = [
                'fswatch',
                '--monitor=poll_monitor',
                '-r',
            ];

            \array_push($cmd, ...$dirs);

            (new SymfonyProcess($cmd))
                ->setTimeout(null)
                ->run(static function ($type, $buffer) use ($extensions, $server): void {
                    foreach (\explode(\PHP_EOL, (string)$buffer) as $line) {
                        if (u($line)->endsWith($extensions)) {
                            OutputHelper::writeln(\sprintf('File %s updated', $line));
                            OutputHelper::writeln('Reloading server...');

                            $server->reload();

                            break;
                        }
                    }
                });
        }));
    }

    private function registerDefaultCallbacks(Server $server): void
    {
        $server->on(
            'workerStart',
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Starting worker %d', $workerId));
            }
        );

        $server->on(
            'workerStop',
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Stopping worker %d', $workerId));
            }
        );
    }
}
