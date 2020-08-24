<?php

declare(strict_types=1);

namespace EonX\EasyLogging;

use EoneoPay\Externals\HttpClient\Exceptions\InvalidApiResponseException;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface as ExternalsLoggerInterface;
use EonX\EasyLogging\Bugsnag\BugsnagHandler;
use EonX\EasyLogging\ContextModifiers\EntityValidationFailedExceptionContextModifier;
use EonX\EasyLogging\Formatters\SumoJsonFormatter;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use EonX\EasyLogging\Interfaces\LoggerInterface;
use Laravel\Lumen\Application;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Throwable;

/**
 * @deprecated since 2.4, will be removed in 3.0. No need to decorated monolog logger.
 */
final class Logger implements LoggerInterface
{
    /**
     * @var string
     */
    public const APPLICATION = 'manage';

    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $appLogger;

    /**
     * @var string[]
     */
    private $bugsnagDoNotReport = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $emergencyLogger;

    /**
     * @var \EonX\EasyLogging\Interfaces\ExceptionContextModifierInterface[]
     */
    private $exceptionContextModifiers = [];

    /**
     * @var \Monolog\Handler\HandlerInterface[]
     */
    private $handlers = [];

    /**
     * @var \Monolog\Processor\ProcessorInterface[]
     */
    private $processors;

    public function __construct()
    {
        $this->handlers[] = $this->getHandler();

        if (\class_exists('App\\Exceptions\\EntityValidationFailedException')) {
            $this->exceptionContextModifiers = [new EntityValidationFailedExceptionContextModifier()];
        }
    }

    public function addHandler(HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function alert($message, ?array $context = null): void
    {
        $this->log('alert', $message, $context ?? []);
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function critical($message, ?array $context = null): void
    {
        $this->log('critical', $message, $context ?? []);
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function debug($message, ?array $context = null): void
    {
        $this->log('debug', $message, $context ?? []);
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function emergency($message, ?array $context = null): void
    {
        $this->log('emergency', $message, $context ?? []);
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function error($message, ?array $context = null): void
    {
        $this->log('error', $message, $context ?? []);
    }

    /**
     * @param null|mixed[] $context
     */
    public function exception(Throwable $exception, ?string $level = null, ?array $context = null): void
    {
        $context = $context ?? [];
        $context['exception_class'] = \get_class($exception);
        $context['file'] = $exception->getFile();
        $context['line'] = $exception->getLine();
        $context['stack_trace'] = $exception->getTraceAsString();

        $level = $level ?? 'error';

        if ($exception instanceof InvalidApiResponseException) {
            $response = $exception->getResponse();

            $context['response'] = [
                'headers' => $response->getHeaders(),
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ];
        }

        foreach ($this->exceptionContextModifiers as $modifier) {
            $context = $modifier->modifyContext($exception, $context);
        }

        $this->log($level, \sprintf('Exception caught: %s', $exception->getMessage()), $context);
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function info($message, ?array $context = null): void
    {
        $this->log('info', $message, $context ?? []);
    }

    /**
     * @param string $level
     * @param string $message
     * @param null|mixed[] $context
     */
    public function log($level, $message, ?array $context = null): void
    {
        try {
            $callable = [$this->getLogger(), $level];

            if (\is_callable($callable) === true) {
                $callable($message, $context ?? []);
            }
        } catch (\Throwable $exception) {
            /** @noinspection ForgottenDebugOutputInspection This is only a fallback if logger is unavailable */
            \error_log($exception->getMessage());
        }
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function notice($message, ?array $context = null): void
    {
        $this->log('notice', $message, $context ?? []);
    }

    public function setApp(Application $app): void
    {
        $serviceIds = [
            ExternalsLoggerInterface::class,
            'logger',
            PsrLoggerInterface::class,
            LoggerInterface::class,
        ];

        foreach ($serviceIds as $serviceId) {
            $app->instance($serviceId, $this);
        }

        $this->app = $app;
    }

    /**
     * @param string[] $doNotReport
     */
    public function setBugsnagDoNotReport(array $doNotReport): void
    {
        $this->bugsnagDoNotReport = $doNotReport;
    }

    /**
     * @param \EonX\EasyLogging\Interfaces\ExceptionContextModifierInterface[] $modifiers
     */
    public function setExceptionContextModifiers(array $modifiers): void
    {
        $this->exceptionContextModifiers = $modifiers;
    }

    /**
     * @param \Monolog\Processor\ProcessorInterface[] $processors
     */
    public function setProcessors(array $processors): void
    {
        $this->processors = $processors;
    }

    /**
     * @param string $message
     * @param null|mixed[] $context
     */
    public function warning($message, ?array $context = null): void
    {
        $this->log('warning', $message, $context ?? []);
    }

    private function getAppLogger(): PsrLoggerInterface
    {
        if ($this->appLogger !== null) {
            return $this->appLogger;
        }

        $handlers = $this->handlers;

        // Add Bugsnag handler only if enabled
        if ($this->app->make('config')->get('bugsnag.enabled', false)) {
            $bugsnag = new BugsnagHandler($this->app->get(ExternalLogClientInterface::class));

            $this->handlers[] = $bugsnag->setDoNotReport($this->bugsnagDoNotReport);
        }

        $processors = $this->processors ?? $this->getDefaultProcessors();

        return $this->appLogger = new MonologLogger(self::APPLICATION, $handlers, $processors);
    }

    /**
     * @return \Monolog\Processor\ProcessorInterface[]
     */
    private function getDefaultProcessors(): array
    {
        return [
            new WebProcessor(),
            new IntrospectionProcessor(MonologLogger::DEBUG, [
                'App\\Exceptions\\',
                'App\\Externals\\Libraries\\Logger',
                'Aws\\',
                'Elasticsearch\\',
                'EoneoPay\\Externals\\',
                'EoneoPay\\Framework\\',
                'EonX\\EasyLogging\\',
                'GuzzleHttp\\',
                'Illuminate\\',
                'Laravel\\',
                'React\\',
                'Symfony\\',
            ]),
        ];
    }

    private function getEmergencyLogger(): PsrLoggerInterface
    {
        if ($this->emergencyLogger !== null) {
            return $this->emergencyLogger;
        }

        return $this->emergencyLogger = new MonologLogger(
            self::APPLICATION,
            $this->handlers,
            $this->processors ?? $this->getDefaultProcessors()
        );
    }

    private function getHandler(): HandlerInterface
    {
        $handler = new StreamHandler('php://stderr');
        $handler->setFormatter(new SumoJsonFormatter());

        return $handler;
    }

    private function getLogger(): PsrLoggerInterface
    {
        return $this->app === null ? $this->getEmergencyLogger() : $this->getAppLogger();
    }
}
