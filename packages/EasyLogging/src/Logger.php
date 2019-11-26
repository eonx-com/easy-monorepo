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
use Exception;
use Laravel\Lumen\Application;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Throwable;

final class Logger implements LoggerInterface
{
    /** @var string */
    public const APPLICATION = 'manage';

    /** @var \Laravel\Lumen\Application */
    private $app;

    /** @var \Psr\Log\LoggerInterface */
    private $appLogger;

    /** @var string[] */
    private $bugsnagDoNotReport = [];

    /** @var \Psr\Log\LoggerInterface */
    private $emergencyLogger;

    /** @var \EonX\EasyLogging\Interfaces\ExceptionContextModifierInterface[] */
    private $exceptionContextModifiers = [];

    /** @var \Monolog\Handler\HandlerInterface[] */
    private $handlers = [];

    /** @var \Monolog\Processor\ProcessorInterface[] */
    private $processors;

    /**
     * Logger constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->handlers[] = $this->getHandler();

        if (\class_exists('App\\Exceptions\\EntityValidationFailedException')) {
            $this->exceptionContextModifiers = [new EntityValidationFailedExceptionContextModifier()];
        }
    }

    /**
     * Add monolog handler.
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     *
     * @return void
     */
    public function addHandler(HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, ?array $context = null): void
    {
        $this->log('alert', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, ?array $context = null): void
    {
        $this->log('critical', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, ?array $context = null): void
    {
        $this->log('debug', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, ?array $context = null): void
    {
        $this->log('emergency', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, ?array $context = null): void
    {
        $this->log('error', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
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
                'content' => $response->getContent()
            ];
        }

        foreach ($this->exceptionContextModifiers as $modifier) {
            $context = $modifier->modifyContext($exception, $context);
        }

        $this->log($level, \sprintf('Exception caught: %s', $exception->getMessage()), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, ?array $context = null): void
    {
        $this->log('info', $message, $context ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, ?array $context = null): void
    {
        try {
            $callable = [$this->getLogger(), $level];

            if (\is_callable($callable) === true) {
                $callable($message, $context ?? []);
            }
        } catch (Exception $exception) {
            /** @noinspection ForgottenDebugOutputInspection This is only a fallback if logger is unavailable */
            \error_log($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, ?array $context = null): void
    {
        $this->log('notice', $message, $context ?? []);
    }

    /**
     * Set app.
     *
     * @param \Laravel\Lumen\Application $app
     *
     * @return void
     */
    public function setApp(Application $app): void
    {
        $app->instance(ExternalsLoggerInterface::class, $this);
        $app->alias(ExternalsLoggerInterface::class, 'logger');

        $this->app = $app;
    }

    /**
     * Set exception classes not to report to bugsnag.
     *
     * @param string[] $doNotReport
     *
     * @return void
     */
    public function setBugsnagDoNotReport(array $doNotReport): void
    {
        $this->bugsnagDoNotReport = $doNotReport;
    }

    /**
     * Set exception context modifiers.
     *
     * @param \EonX\EasyLogging\Interfaces\ExceptionContextModifierInterface[] $modifiers
     *
     * @return void
     */
    public function setExceptionContextModifiers(array $modifiers): void
    {
        $this->exceptionContextModifiers = $modifiers;
    }

    /**
     * Set processors.
     *
     * @param \Monolog\Processor\ProcessorInterface[] $processors
     *
     * @return void
     */
    public function setProcessors(array $processors): void
    {
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, ?array $context = null): void
    {
        $this->log('warning', $message, $context ?? []);
    }

    /**
     * Get logger that requires app for DI.
     *
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \Exception
     */
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
     * Get default processors.
     *
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
                'GuzzleHttp\\',
                'Illuminate\\',
                'Laravel\\',
                'React\\',
                'Symfony\\'
            ])
        ];
    }

    /**
     * Get emergency logger that doesn't need app.
     *
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \Exception
     */
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

    /**
     * Get stream handler.
     *
     * @return \Monolog\Handler\HandlerInterface
     *
     * @throws \Exception
     */
    private function getHandler(): HandlerInterface
    {
        $handler = new StreamHandler('php://stderr');
        $handler->setFormatter(new SumoJsonFormatter());

        return $handler;
    }

    /**
     * Get logger.
     *
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \Exception
     */
    private function getLogger(): PsrLoggerInterface
    {
        if ($this->app === null) {
            return $this->getEmergencyLogger();
        }

        return $this->getAppLogger();
    }
}
