<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

use EonX\EasySwoole\Helpers\HttpFoundationHelper;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Swoole\Constant;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Runtime\RunnerInterface;

final class EasySwooleRunner implements RunnerInterface
{
    private const DEFAULT_OPTIONS = [
        'host' => '0.0.0.0',
        'port' => 8080,
        'mode' => \SWOOLE_BASE,
        'sock_type' => \SWOOLE_SOCK_TCP,
        'settings' => [],
        'callbacks' => [],
        'use_default_callbacks' => true,
    ];

    /**
     * @param mixed[] $options
     */
    public function __construct(
        private readonly HttpKernelInterface $app,
        private readonly array $options
    ) {
    }

    public function run(): int
    {
        $app = $this->app;
        $server = $this->createSwooleHttpServer();

        $server->on(
            Constant::EVENT_REQUEST,
            static function (Request $request, Response $response) use ($app, $server): void {
                $hfRequest = HttpFoundationHelper::fromSwooleRequest($request);
                $hfRequest->attributes->set(RequestAttributesInterface::EASY_SWOOLE_ENABLED, true);

                $hfResponse = $app->handle($hfRequest);
                HttpFoundationHelper::reflectHttpFoundationResponse($hfResponse, $response);

                if ($app instanceof TerminableInterface) {
                    $app->terminate($hfRequest, $hfResponse);
                }

                // Stop worker if app state compromised
                if ($hfRequest->attributes->get(RequestAttributesInterface::EASY_SWOOLE_APP_STATE_COMPROMISED, false)) {
                    $server->stop($server->getWorkerId());
                }
            }
        );

        $server->start();

        return 0;
    }

    private function createSwooleHttpServer(): Server
    {
        $server = new Server(
            $this->getOption('host', 'SWOOLE_HOST'),
            (int)$this->getOption('port', 'SWOOLE_PORT'),
            (int)$this->getOption('mode', 'SWOOLE_MODE'),
            (int)$this->getOption('sock_type', 'SWOOLE_SOCK_TYPE')
        );

        $server->set($this->getOption('settings', 'SWOOLE_SETTINGS'));

        if ($this->getOption('use_default_callbacks', 'SWOOLE_USE_DEFAULT_CALLBACKS')) {
            $this->registerDefaultCallbacks($server);
        }

        foreach ($this->getOption('callbacks', 'SWOOLE_CALLBACKS') as $event => $fn) {
            $server->on($event, $fn);
        }

        return $server;
    }

    private function getOption(string $option, string $env): mixed
    {
        return $this->options[$option] ?? $_SERVER[$env] ?? $_ENV[$env] ?? self::DEFAULT_OPTIONS[$option];
    }

    private function registerDefaultCallbacks(Server $server): void
    {
        $writeFn = static function (string $message): void {
            $stream = \fopen('php://stdout', 'w+');
            \fwrite($stream, $message);
            \fclose($stream);
        };

        $server->on(
            Constant::EVENT_WORKER_START,
            static function (Server $server, int $workerId) use ($writeFn): void {
                $writeFn(\sprintf('Starting worker %d' . \PHP_EOL, $workerId));
            }
        );

        $server->on(
            Constant::EVENT_WORKER_STOP,
            static function (Server $server, int $workerId) use ($writeFn): void {
                $writeFn(\sprintf('Stopping worker %d' . \PHP_EOL, $workerId));
            }
        );
    }
}