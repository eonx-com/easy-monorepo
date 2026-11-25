<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\SnapStart\Subscriber;

use ArrayObject;
use Bref\Context\Context;
use EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Generator;
use LogicException;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Psr\Log\AbstractLogger;
use RuntimeException;
use stdClass;
use Stringable;
use UnexpectedValueException;

final class SnapStartLifecycleSubscriberTest extends AbstractUnitTestCase
{
    private string $markerPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markerPath = \sys_get_temp_dir() . '/' . \uniqid('easy-serverless-snap-start-test-', true);
    }

    protected function tearDown(): void
    {
        foreach ([$this->markerPath, $this->markerPath . '.error.log'] as $path) {
            if (\file_exists($path)) {
                \unlink($path);
            }
        }

        \mt_srand();

        parent::tearDown();
    }

    public function testAfterStartupCreatesAfterRestoreHooksFromClosuresBeforeRunningWarmers(): void
    {
        $calls = new ArrayObject();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub($calls, 'warmer')],
            afterRestoreHooks: [
                'hook' => static function () use ($calls): AfterRestoreHookStub {
                    $calls->append('hook created');

                    return new AfterRestoreHookStub($calls, 'hook');
                },
            ],
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['hook created', 'warmer'], $calls->getArrayCopy());
    }

    public function testAfterStartupCreatesWarmersFromClosuresWhenRunningThem(): void
    {
        $calls = new ArrayObject();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                'first' => static function () use ($calls): SnapStartWarmerStub {
                    $calls->append('first created');

                    return new SnapStartWarmerStub($calls, 'first');
                },
                'second' => static function () use ($calls): SnapStartWarmerStub {
                    $calls->append('second created');

                    return new SnapStartWarmerStub($calls, 'second');
                },
            ],
            afterRestoreHooks: [],
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['first created', 'first', 'second created', 'second'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
    }

    public function testAfterStartupDoesNothingWhenMarkerExists(): void
    {
        \touch($this->markerPath);
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: (static function (): Generator {
                yield throw new LogicException('The warmers must not be iterated.');
            })(),
            afterRestoreHooks: (static function (): Generator {
                yield throw new LogicException('The after restore hooks must not be iterated.');
            })(),
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame([], $logHandler->getRecords());
        self::assertFileExists($this->markerPath);
    }

    public function testAfterStartupLogsCompletion(): void
    {
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub()],
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertCount(1, $logHandler->getRecords());
        self::assertSame(Level::Info, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[0]->message);
        self::assertSame(['duration_ms', 'warmers'], \array_keys($logHandler->getRecords()[0]->context));
        self::assertIsInt($logHandler->getRecords()[0]->context['duration_ms']);
        self::assertIsArray($logHandler->getRecords()[0]->context['warmers']);
        self::assertSame(
            [SnapStartWarmerStub::class],
            \array_keys($logHandler->getRecords()[0]->context['warmers'])
        );
        self::assertIsInt($logHandler->getRecords()[0]->context['warmers'][SnapStartWarmerStub::class]);
    }

    public function testAfterStartupLogsFailureWhenAfterRestoreHooksCannotBeResolved(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('After restore hook cannot be created.');
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub($calls, 'warmer')],
            afterRestoreHooks: (static function () use ($calls, $exception): Generator {
                yield new AfterRestoreHookStub($calls, 'hook');

                throw $exception;
            })(),
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['warmer'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart after restore hooks could not be resolved', $logHandler->getRecords()[0]->message);
        self::assertSame(['exception' => $exception], $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
    }

    public function testAfterStartupLogsFailureWhenMarkerCannotBeWritten(): void
    {
        $logHandler = new TestHandler();
        $markerPath = $this->markerPath . '/marker';
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $markerPath,
        );

        $sut->afterStartup();

        self::assertFileDoesNotExist($markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertTrue($logHandler->hasWarning('SnapStart initialization marker could not be written'));
        self::assertSame(['error', 'path'], \array_keys($logHandler->getRecords()[0]->context));
        self::assertIsString($logHandler->getRecords()[0]->context['error']);
        self::assertStringContainsString('Unable to create file', $logHandler->getRecords()[0]->context['error']);
        self::assertSame($markerPath, $logHandler->getRecords()[0]->context['path']);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
    }

    public function testAfterStartupLogsFailureWhenWarmersCannotBeResolved(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('Warmer cannot be created.');
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: (static function () use ($calls, $exception): Generator {
                yield new SnapStartWarmerStub($calls, 'first');

                throw $exception;
            })(),
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['first'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmers could not be resolved', $logHandler->getRecords()[0]->message);
        self::assertSame(['exception' => $exception], $logHandler->getRecords()[0]->context);
        self::assertSame(Level::Info, $logHandler->getRecords()[1]->level);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        self::assertIsArray($logHandler->getRecords()[1]->context['warmers']);
        self::assertSame(
            [SnapStartWarmerStub::class],
            \array_keys($logHandler->getRecords()[1]->context['warmers'])
        );
    }

    public function testAfterStartupLogsWarmerFailureAndRunsNextWarmers(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('Warmer failure.');
        $expectedContext = [
            'exception' => $exception,
            'warmer' => SnapStartWarmerStub::class,
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                new SnapStartWarmerStub($calls, 'failing', $exception),
                new SnapStartWarmerStub($calls, 'next'),
            ],
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['failing', 'next'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Warning, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmer failed', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
    }

    public function testAfterStartupLogsWarmerThatCannotBeCreatedAndRunsNextWarmers(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('Warmer cannot be created.');
        $expectedContext = [
            'exception' => $exception,
            'warmer' => 'failing',
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                'first' => new SnapStartWarmerStub($calls, 'first'),
                'failing' => static fn (): never => throw $exception,
                'closing' => static fn (): SnapStartWarmerStub => new SnapStartWarmerStub($calls, 'closing'),
            ],
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['first', 'closing'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmer could not be created', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame(Level::Info, $logHandler->getRecords()[1]->level);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        self::assertIsArray($logHandler->getRecords()[1]->context['warmers']);
        self::assertSame(['first', 'closing'], \array_keys($logHandler->getRecords()[1]->context['warmers']));
    }

    public function testAfterStartupLogsWarmerThatIsNotSnapStartWarmerAndRunsNextWarmers(): void
    {
        $calls = new ArrayObject();
        $expectedContext = [
            'exception' => new UnexpectedValueException(\sprintf(
                'Expected an instance of "%s", got "stdClass".',
                SnapStartWarmerInterface::class
            )),
            'warmer' => 'not_a_warmer',
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                'not_a_warmer' => static fn (): stdClass => new stdClass(),
                'closing' => new SnapStartWarmerStub($calls, 'closing'),
            ],
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['closing'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmer could not be created', $logHandler->getRecords()[0]->message);
        self::assertEquals($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
    }

    public function testAfterStartupResolvesAfterRestoreHooksWithoutRunningThem(): void
    {
        $calls = new ArrayObject();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub($calls, 'warmer')],
            afterRestoreHooks: (static function () use ($calls): Generator {
                $calls->append('hooks resolved');

                yield new AfterRestoreHookStub($calls, 'hook');
            })(),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['hooks resolved', 'warmer'], $calls->getArrayCopy());
    }

    public function testAfterStartupRunsWarmersInOrderAndWritesMarker(): void
    {
        $calls = new ArrayObject();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                new SnapStartWarmerStub($calls, 'first'),
                new SnapStartWarmerStub($calls, 'second'),
                new SnapStartWarmerStub($calls, 'third'),
            ],
            afterRestoreHooks: [],
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['first', 'second', 'third'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
    }

    public function testAfterStartupWarmsUpEverySubscriberOfProcessWithSameMarkerPath(): void
    {
        $calls = new ArrayObject();
        $firstSubscriber = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub($calls, 'first warmer')],
            afterRestoreHooks: [],
            markerPath: $this->markerPath,
        );
        $firstSubscriber->afterStartup();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [new SnapStartWarmerStub($calls, 'second warmer')],
            afterRestoreHooks: [],
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['first warmer', 'second warmer'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
    }

    #[RunInSeparateProcess]
    public function testAfterStartupWritesToErrorLogWhenLoggerFails(): void
    {
        $calls = new ArrayObject();
        $errorLogPath = $this->markerPath . '.error.log';
        \ini_set('error_log', $errorLogPath);
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [
                new SnapStartWarmerStub($calls, 'failing', new RuntimeException('Warmer failed.')),
                new SnapStartWarmerStub($calls, 'next'),
            ],
            afterRestoreHooks: [],
            logger: new class() extends AbstractLogger {
                public function log(mixed $level, string|Stringable $message, array $context = []): void
                {
                    throw new RuntimeException('Logger failed.');
                }
            },
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertSame(['failing', 'next'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
        self::assertStringContainsString(
            'SnapStart warmer failed (the logger failed: Logger failed.)',
            (string)\file_get_contents($errorLogPath)
        );
        self::assertStringContainsString(
            'SnapStart warm-up completed (the logger failed: Logger failed.)',
            (string)\file_get_contents($errorLogPath)
        );
    }

    public function testBeforeInvokeDoesNothingOnNextInvocations(): void
    {
        $calls = new ArrayObject();
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [new AfterRestoreHookStub($calls, 'hook')],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));
        $logHandler->clear();
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
        self::assertSame(['hook'], $calls->getArrayCopy());
        self::assertSame([], $logHandler->getRecords());
    }

    public function testBeforeInvokeDoesNothingWhenWarmUpWasSkipped(): void
    {
        \touch($this->markerPath);
        $calls = new ArrayObject();
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [new AfterRestoreHookStub($calls, 'hook')],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
        self::assertSame([], $calls->getArrayCopy());
        self::assertSame([], $logHandler->getRecords());
    }

    public function testBeforeInvokeDoesNothingWithoutWarmUp(): void
    {
        $calls = new ArrayObject();
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [new AfterRestoreHookStub($calls, 'hook')],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
        self::assertSame([], $calls->getArrayCopy());
        self::assertSame([], $logHandler->getRecords());
    }

    public function testBeforeInvokeLogsAfterRestoreHookFailureAndRunsNextHooks(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('After restore hook failure.');
        $expectedContext = [
            'exception' => $exception,
            'hook' => AfterRestoreHookStub::class,
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [
                new AfterRestoreHookStub($calls, 'failing', $exception),
                new AfterRestoreHookStub($calls, 'next'),
            ],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        $logHandler->clear();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['failing', 'next'], $calls->getArrayCopy());
        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart after restore hook failed', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart restore completed', $logHandler->getRecords()[1]->message);
    }

    public function testBeforeInvokeLogsRestoreCompletion(): void
    {
        $expectedAfterRestoreHooks = [AfterRestoreHookStub::class, AfterRestoreHookStub::class];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [
                new AfterRestoreHookStub(),
                new AfterRestoreHookStub(),
            ],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        $logHandler->clear();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertCount(1, $logHandler->getRecords());
        self::assertSame(Level::Info, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart restore completed', $logHandler->getRecords()[0]->message);
        self::assertSame(['after_restore_hooks', 'duration_ms'], \array_keys($logHandler->getRecords()[0]->context));
        self::assertSame($expectedAfterRestoreHooks, $logHandler->getRecords()[0]->context['after_restore_hooks']);
        self::assertIsInt($logHandler->getRecords()[0]->context['duration_ms']);
    }

    public function testBeforeInvokeReseedsMtRandOnFirstInvocation(): void
    {
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [],
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertNotSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
    }

    public function testBeforeInvokeReseedsMtRandWhenAfterRestoreHooksCouldNotBeResolved(): void
    {
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: (static function (): Generator {
                yield throw new RuntimeException('After restore hook cannot be created.');
            })(),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertNotSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
    }

    public function testBeforeInvokeRunsAfterRestoreHooksAfterReseedingMtRand(): void
    {
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        $afterRestoreHookSequence = new ArrayObject();
        $afterRestoreHook = new class($afterRestoreHookSequence) implements AfterRestoreHookInterface {
            /**
             * @param \ArrayObject<int, int> $sequence
             */
            public function __construct(
                private readonly ArrayObject $sequence,
            ) {
            }

            public function afterRestore(): void
            {
                $this->sequence->exchangeArray([\mt_rand(), \mt_rand(), \mt_rand()]);
            }
        };
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [$afterRestoreHook],
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertCount(3, $afterRestoreHookSequence);
        self::assertNotSame($fixedSeedSequence, $afterRestoreHookSequence->getArrayCopy());
    }

    public function testBeforeInvokeRunsAfterRestoreHooksInOrderOnFirstInvocation(): void
    {
        $calls = new ArrayObject();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [
                new AfterRestoreHookStub($calls, 'first'),
                new AfterRestoreHookStub($calls, 'second'),
                new AfterRestoreHookStub($calls, 'third'),
            ],
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['first', 'second', 'third'], $calls->getArrayCopy());
    }

    public function testBeforeInvokeRunsAfterRestoreHooksOfEverySubscriberWithSameMarkerPath(): void
    {
        $calls = new ArrayObject();
        $firstSubscriber = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [new AfterRestoreHookStub($calls, 'first hook')],
            markerPath: $this->markerPath,
        );
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [new AfterRestoreHookStub($calls, 'second hook')],
            markerPath: $this->markerPath,
        );
        $firstSubscriber->afterStartup();
        $sut->afterStartup();
        $firstSubscriber->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['first hook', 'second hook'], $calls->getArrayCopy());
    }

    #[RunInSeparateProcess]
    public function testBeforeInvokeRunsAfterRestoreHooksWhenLoggerFails(): void
    {
        $calls = new ArrayObject();
        \ini_set('error_log', $this->markerPath . '.error.log');
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [
                new AfterRestoreHookStub($calls, 'failing', new RuntimeException('Hook failed.')),
                new AfterRestoreHookStub($calls, 'next'),
            ],
            logger: new class() extends AbstractLogger {
                public function log(mixed $level, string|Stringable $message, array $context = []): void
                {
                    throw new RuntimeException('Logger failed.');
                }
            },
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['failing', 'next'], $calls->getArrayCopy());
    }

    public function testBeforeInvokeRunsNextAfterRestoreHooksWhenAfterRestoreHookCannotBeCreated(): void
    {
        $calls = new ArrayObject();
        $exception = new RuntimeException('After restore hook cannot be created.');
        $expectedAfterRestoreHooks = [AfterRestoreHookStub::class, AfterRestoreHookStub::class];
        $expectedContext = [
            'exception' => $exception,
            'hook' => 'failing',
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: [
                'first' => static fn (): AfterRestoreHookStub => new AfterRestoreHookStub($calls, 'first'),
                'failing' => static fn (): never => throw $exception,
                'last' => new AfterRestoreHookStub($calls, 'last'),
            ],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['first', 'last'], $calls->getArrayCopy());
        self::assertCount(3, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart after restore hook could not be created', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        self::assertSame('SnapStart restore completed', $logHandler->getRecords()[2]->message);
        self::assertSame($expectedAfterRestoreHooks, $logHandler->getRecords()[2]->context['after_restore_hooks']);
    }

    public function testBeforeInvokeRunsNextAfterRestoreHooksWhenObjectIsNotAfterRestoreHook(): void
    {
        $calls = new ArrayObject();
        /** @var iterable<\EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface> $afterRestoreHooks */
        $afterRestoreHooks = [
            'not_a_hook' => new stdClass(),
            'hook' => new AfterRestoreHookStub($calls, 'hook'),
        ];
        $expectedContext = [
            'exception' => new UnexpectedValueException(\sprintf(
                'Expected an instance of "%s", got "stdClass".',
                AfterRestoreHookInterface::class
            )),
            'hook' => 'not_a_hook',
        ];
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: [],
            afterRestoreHooks: $afterRestoreHooks,
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $sut->afterStartup();

        $sut->beforeInvoke(static fn (): null => null, [], new Context('id', 0, '', ''));

        self::assertSame(['hook'], $calls->getArrayCopy());
        self::assertCount(3, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart after restore hook could not be created', $logHandler->getRecords()[0]->message);
        self::assertEquals($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        self::assertSame('SnapStart restore completed', $logHandler->getRecords()[2]->message);
        self::assertSame([AfterRestoreHookStub::class], $logHandler->getRecords()[2]->context['after_restore_hooks']);
    }
}
