<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Runner;

use Carbon\CarbonImmutable;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasySwoole\Caching\Helper\CacheTableHelper;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use EonX\EasySwoole\Common\Helper\AppRuntimeHelper;
use EonX\EasySwoole\Common\Helper\ErrorResponseHelper;
use EonX\EasySwoole\Common\Helper\HttpFoundationHelper;
use EonX\EasySwoole\Common\Helper\OptionHelper;
use EonX\EasySwoole\Logging\Helper\OutputHelper;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Process as SwooleProcess;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Runtime\RunnerInterface;
use Throwable;

use function Symfony\Component\String\u;

final class EasySwooleRunner implements RunnerInterface
{
    private const SERVER_EVENTS = [
        AppRuntimeHelper::EVENT_AFTER_RELOAD,
        AppRuntimeHelper::EVENT_BEFORE_RELOAD,
        AppRuntimeHelper::EVENT_CLOSE,
        AppRuntimeHelper::EVENT_CONNECT,
        AppRuntimeHelper::EVENT_FINISH,
        AppRuntimeHelper::EVENT_MANAGER_START,
        AppRuntimeHelper::EVENT_MANAGER_STOP,
        AppRuntimeHelper::EVENT_PIPE_MESSAGE,
        AppRuntimeHelper::EVENT_RECEIVE,
        AppRuntimeHelper::EVENT_REQUEST,
        AppRuntimeHelper::EVENT_SHUTDOWN,
        AppRuntimeHelper::EVENT_TASK,
        AppRuntimeHelper::EVENT_WORKER_ERROR,
        AppRuntimeHelper::EVENT_WORKER_EXIT,
        AppRuntimeHelper::EVENT_WORKER_START,
        AppRuntimeHelper::EVENT_WORKER_STOP,
    ];

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
            AppRuntimeHelper::EVENT_REQUEST,
            static function (Request $request, Response $response) use ($app, $server, $responseChunkSize): void {
                $responded = false;
                $hfRequest = null;

                try {
                    $hfRequest = HttpFoundationHelper::fromSwooleRequest($request);
                    $hfRequest->attributes->set(RequestAttribute::EasySwooleEnabled->value, true);
                    $hfRequest->attributes->set(
                        RequestAttribute::EasySwooleRequestStartTime->value,
                        CarbonImmutable::now('UTC')
                    );
                    $hfRequest->attributes->set(
                        RequestAttribute::EasySwooleWorkerId->value,
                        $server->getWorkerId()
                    );

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

                    $responded = true;

                    if ($app instanceof TerminableInterface) {
                        $app->terminate($hfRequest, $hfResponse);
                    }

                    $isAppStateCompromised = $hfRequest->attributes
                        ->get(RequestAttribute::EasySwooleAppStateCompromised->value, false);

                    // Stop worker if app state compromised
                    if ($isAppStateCompromised) {
                        $server->stop($server->getWorkerId(), OptionHelper::getBoolean('worker_stop_wait_event'));
                    }

                    CacheTableHelper::tick();
                } catch (Throwable $throwable) {
                    // If something happens before the response was sent, we must respond not to let the client hang
                    $hasEasyErrorHandler = \interface_exists(ErrorHandlerInterface::class)
                        && $app instanceof KernelInterface
                        && $app->getContainer()
                            ->has(ErrorHandlerInterface::class);

                    // If eonx-com/easy-error-handler is installed and configured, let's report the error
                    if ($hasEasyErrorHandler) {
                        /** @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface|null $errorHandler */
                        $errorHandler = $app->getContainer()
                            ->get(ErrorHandlerInterface::class);

                        $errorHandler?->report($throwable);

                        if ($errorHandler !== null && $hfRequest !== null && $responded === false) {
                            $hfResponse = $errorHandler->render($hfRequest, $throwable);

                            HttpFoundationHelper::reflectHttpFoundationResponse(
                                $hfResponse,
                                $response,
                                $responseChunkSize
                            );

                            $responded = true;
                        }
                    }

                    if ($responded === false) {
                        ErrorResponseHelper::sendErrorResponse($throwable, $response);
                    }
                }
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
            if (\in_array($event, self::SERVER_EVENTS, true) === false) {
                continue;
            }

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
            AppRuntimeHelper::EVENT_WORKER_START,
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Starting worker %d', $workerId));
            }
        );

        $server->on(
            AppRuntimeHelper::EVENT_WORKER_STOP,
            static function (Server $server, int $workerId): void {
                OutputHelper::writeln(\sprintf('Stopping worker %d', $workerId));
            }
        );
    }
}
