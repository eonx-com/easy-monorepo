<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SnapStart\Subscriber;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Listener\BrefEventSubscriber;
use Closure;
use EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use UnexpectedValueException;

/**
 * Handles the AWS Lambda SnapStart lifecycle in the Bref function runtime:
 * - afterStartup(): warms the application up, right before Lambda takes the snapshot,
 * - beforeInvoke(): on the first invocation after the snapshot is restored, makes the execution environment unique.
 *
 * It never throws: a failure is logged, it does not fail the initialization or an invocation.
 *
 * @see https://docs.aws.amazon.com/lambda/latest/dg/snapstart.html
 */
final class SnapStartLifecycleSubscriber extends BrefEventSubscriber
{
    private const MARKER_FILENAME = 'easy-serverless-snap-start-initialized';

    /**
     * Whether this PHP process warms up, by marker path. The first subscriber of the process writes the marker, the
     * next ones still warm their application up, e.g. with Octane, where a process has two applications.
     *
     * @var array<string, bool>
     */
    private static array $warmUpByMarkerPath = [];

    private readonly string $markerPath;

    /**
     * The after restore hooks created before the snapshot, null when this process has no restore to handle.
     *
     * @var \EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface[]|null
     */
    private ?array $pendingAfterRestoreHooks = null;

    /**
     * Each warmer and hook can be given as a closure that creates it: one that cannot be created is then logged and
     * skipped, the next ones still run. The keys of the iterables name the ones that cannot be created in the logs.
     *
     * @param iterable<\EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface|\Closure> $warmers
     * @param iterable<\EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface|\Closure> $afterRestoreHooks
     */
    public function __construct(
        private readonly iterable $warmers,
        private readonly iterable $afterRestoreHooks,
        private readonly ?LoggerInterface $logger = null,
        ?string $markerPath = null,
    ) {
        $this->markerPath = $markerPath ?? \sys_get_temp_dir() . '/' . self::MARKER_FILENAME;
    }

    public function afterStartup(): void
    {
        // Bref starts a new PHP process in the same execution environment after BREF_LOOP_MAX invocations or a failed
        // one. The snapshot includes /tmp, so the marker limits the warm-up to the process in the snapshot
        self::$warmUpByMarkerPath[$this->markerPath] ??= \file_exists($this->markerPath) === false;

        if (self::$warmUpByMarkerPath[$this->markerPath] === false) {
            return;
        }

        $startedAt = \microtime(true);

        // Created before the warmers, so the connections opened to create them are closed by the warmers
        $this->pendingAfterRestoreHooks = $this->createAfterRestoreHooks();
        $warmerDurations = $this->runWarmers();
        $this->writeMarker();

        $this->log(LogLevel::INFO, 'SnapStart warm-up completed', [
            'duration_ms' => self::getDurationMs($startedAt),
            'warmers' => $warmerDurations,
        ]);
    }

    public function beforeInvoke(
        callable|Handler|RequestHandlerInterface $handler,
        mixed $event,
        Context $context,
    ): void {
        if ($this->pendingAfterRestoreHooks === null) {
            return;
        }

        $afterRestoreHooks = $this->pendingAfterRestoreHooks;
        $this->pendingAfterRestoreHooks = null;
        $startedAt = \microtime(true);

        // The Bref process is a single PHP request, so the Mersenne Twister seeded before the snapshot is never seeded
        // again, and every restored execution environment would replay the same mt_rand(), rand(), shuffle(),
        // array_rand() and str_shuffle() sequence.
        // See https://docs.aws.amazon.com/lambda/latest/dg/snapstart-uniqueness.html
        \mt_srand();

        $afterRestoreHookClasses = [];

        foreach ($afterRestoreHooks as $afterRestoreHook) {
            $afterRestoreHookClasses[] = $afterRestoreHook::class;

            try {
                $afterRestoreHook->afterRestore();
            } catch (Throwable $throwable) {
                $this->log(LogLevel::ERROR, 'SnapStart after restore hook failed', [
                    'exception' => $throwable,
                    'hook' => $afterRestoreHook::class,
                ]);
            }
        }

        $this->log(LogLevel::INFO, 'SnapStart restore completed', [
            'after_restore_hooks' => $afterRestoreHookClasses,
            'duration_ms' => self::getDurationMs($startedAt),
        ]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private static function create(mixed $item, string $class): object
    {
        $object = $item instanceof Closure ? $item() : $item;

        if ($object instanceof $class === false) {
            throw new UnexpectedValueException(\sprintf(
                'Expected an instance of "%s", got "%s".',
                $class,
                \get_debug_type($object)
            ));
        }

        return $object;
    }

    private static function getDurationMs(float $startedAt): int
    {
        return (int)\round((\microtime(true) - $startedAt) * 1000);
    }

    /**
     * @return \EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface[]
     */
    private function createAfterRestoreHooks(): array
    {
        $afterRestoreHooks = [];

        try {
            foreach ($this->afterRestoreHooks as $key => $item) {
                try {
                    $afterRestoreHooks[] = self::create($item, AfterRestoreHookInterface::class);
                } catch (Throwable $throwable) {
                    $this->log(LogLevel::ERROR, 'SnapStart after restore hook could not be created', [
                        'exception' => $throwable,
                        'hook' => $key,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            // A lazy iterator cannot go on once it failed to create a hook
            $this->log(LogLevel::ERROR, 'SnapStart after restore hooks could not be resolved', [
                'exception' => $throwable,
            ]);
        }

        return $afterRestoreHooks;
    }

    /**
     * A logger that fails, e.g. a log file that cannot be written, must not fail the initialization or an invocation.
     */
    private function log(string $level, string $message, array $context): void
    {
        try {
            $this->logger?->log($level, $message, $context);
        } catch (Throwable $throwable) {
            \error_log(\sprintf('%s (the logger failed: %s)', $message, $throwable->getMessage()));
        }
    }

    /**
     * Returns the duration of each warmer, in milliseconds, by iterable key when it is a string, by class otherwise.
     *
     * @return array<string, int>
     */
    private function runWarmers(): array
    {
        $durations = [];

        try {
            foreach ($this->warmers as $key => $item) {
                $startedAt = \microtime(true);

                try {
                    $warmer = self::create($item, SnapStartWarmerInterface::class);
                } catch (Throwable $throwable) {
                    $this->log(LogLevel::ERROR, 'SnapStart warmer could not be created', [
                        'exception' => $throwable,
                        'warmer' => $key,
                    ]);

                    continue;
                }

                // The service id or the abstract, so two warmers of the same class have their own duration
                $name = \is_string($key) ? $key : $warmer::class;

                try {
                    $warmer->warm();
                } catch (Throwable $throwable) {
                    $this->log(LogLevel::WARNING, 'SnapStart warmer failed', [
                        'exception' => $throwable,
                        'warmer' => $name,
                    ]);
                }

                $durations[$name] = self::getDurationMs($startedAt);
            }
        } catch (Throwable $throwable) {
            // A lazy iterator cannot go on once it failed to create a warmer
            $this->log(LogLevel::ERROR, 'SnapStart warmers could not be resolved', [
                'exception' => $throwable,
            ]);
        }

        return $durations;
    }

    /**
     * The local error handler captures the warning, so the global one cannot throw it as an exception and fail the
     * initialization: Symfony's ErrorHandler throws warnings in debug mode.
     */
    private function writeMarker(): void
    {
        $error = null;
        \set_error_handler(static function (int $type, string $message) use (&$error): bool {
            $error = $message;

            return true;
        });

        try {
            $written = \touch($this->markerPath);
        } finally {
            \restore_error_handler();
        }

        if ($written) {
            return;
        }

        // The only effect is that the next PHP processes of the execution environments warm the application up again
        $this->log(LogLevel::WARNING, 'SnapStart initialization marker could not be written', [
            'error' => $error,
            'path' => $this->markerPath,
        ]);
    }
}
