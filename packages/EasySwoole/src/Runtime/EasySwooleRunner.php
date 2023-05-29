<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use EonX\EasySwoole\Helpers\CacheTableHelper;
use EonX\EasySwoole\Helpers\HttpFoundationHelper;
use EonX\EasySwoole\Helpers\OptionHelper;
use EonX\EasySwoole\Helpers\OutputHelper;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Swoole\Constant;
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
    /**
     * @param mixed[] $options
     */
    public function __construct(
        private readonly HttpKernelInterface $app,
        array $options
    ) {
        OptionHelper::setOptions($options);
    }

    public function run(): int
    {
        $app = $this->app;
        $server = $this->createSwooleHttpServer();
        $responseChunkSize = OptionHelper::getInteger('response_chunk_size', 'SWOOLE_RESPONSE_CHUNK_SIZE');

        CacheTableHelper::createCacheTables(OptionHelper::getArray('cache_tables', 'SWOOLE_CACHE_TABLES'));

        $server->on(
            Constant::EVENT_REQUEST,
            static function (Request $request, Response $response) use ($app, $server, $responseChunkSize): void {
                $hfRequest = HttpFoundationHelper::fromSwooleRequest($request);
                $hfRequest->attributes->set(RequestAttributesInterface::EASY_SWOOLE_ENABLED, true);

                // Surround handle with output buffering to support echo, var_dump, etc
                \ob_start();
                $hfResponse = $app->handle($hfRequest);
                $bufferedOutput = \ob_get_contents();
                \ob_end_clean();

                HttpFoundationHelper::reflectHttpFoundationResponse(
                    $hfResponse,
                    $response,
                    $responseChunkSize,
                    \is_string($bufferedOutput) && $bufferedOutput !== '' ? $bufferedOutput : null
                );

                if ($app instanceof TerminableInterface) {
                    $app->terminate($hfRequest, $hfResponse);
                }

                // Stop worker if app state compromised
                if ($hfRequest->attributes->get(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, false)) {
                    $server->stop($server->getWorkerId(), true);
                }

                CacheTableHelper::onRequest();
            }
        );

        $server->start();

        return 0;
    }

    private function createSwooleHttpServer(): Server
    {
        $server = new Server(
            OptionHelper::getString('host', 'SWOOLE_HOST'),
            OptionHelper::getInteger('port', 'SWOOLE_PORT'),
            OptionHelper::getInteger('mode', 'SWOOLE_MODE'),
            OptionHelper::getInteger('sock_type', 'SWOOLE_SOCK_TYPE')
        );

        $server->set(OptionHelper::getArray('settings', 'SWOOLE_SETTINGS'));

        if (OptionHelper::getBoolean('use_default_callbacks', 'SWOOLE_USE_DEFAULT_CALLBACKS')) {
            $this->registerDefaultCallbacks($server);
        }

        foreach (OptionHelper::getArray('callbacks', 'SWOOLE_CALLBACKS') as $event => $fn) {
            $server->on($event, $fn);
        }

        if (OptionHelper::getBoolean('hot_reload_enabled', 'SWOOLE_HOT_RELOAD_ENABLED')) {
            $this->hotReload(
                $server,
                OptionHelper::getArray('hot_reload_dirs', 'SWOOLE_HOT_RELOAD_DIRS'),
                OptionHelper::getArray('hot_reload_extensions', 'SWOOLE_HOT_RELOAD_EXTENSIONS')
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
            Constant::EVENT_WORKER_START,
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Starting worker %d', $workerId));
            }
        );

        $server->on(
            Constant::EVENT_WORKER_STOP,
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Stopping worker %d', $workerId));
            }
        );
    }
}
