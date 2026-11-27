<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Subscribers;

use ArrayObject;
use Bref\Context\Context;
use EonX\EasyServerless\Laravel\Subscribers\LazySnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\Tests\Stub\ServiceProvider\FailingBootServiceProviderStub;
use EonX\EasyServerless\Tests\Stub\ServiceProvider\TagSnapStartServicesOnBootServiceProviderStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Container\Container;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

final class LazySnapStartLifecycleSubscriberTest extends AbstractLumenTestCase
{
    private string $markerPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markerPath = \sys_get_temp_dir() . '/easy-serverless-snap-start-test-' . \bin2hex(\random_bytes(8));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->markerPath);
        \mt_srand();

        parent::tearDown();
    }

    public function testAfterStartupBootsLumenApplicationBeforeWarmingUp(): void
    {
        /** @var \ArrayObject<int, string> $calls */
        $calls = new ArrayObject();
        $app = $this->getApplication([]);
        $app->instance(SnapStartWarmerStub::class, new SnapStartWarmerStub($calls));
        $app->instance(AfterRestoreHookStub::class, new AfterRestoreHookStub($calls));
        // Tagged on boot, so they are known only once the Lumen application is booted
        $app->register(TagSnapStartServicesOnBootServiceProviderStub::class);
        $app->singleton(
            SnapStartLifecycleSubscriber::class,
            fn (Container $app): SnapStartLifecycleSubscriber => new SnapStartLifecycleSubscriber(
                warmers: $app->tagged(SnapStartTag::Warmer->value),
                afterRestoreHooks: $app->tagged(SnapStartTag::AfterRestoreHook->value),
                markerPath: $this->markerPath
            )
        );
        $sut = new LazySnapStartLifecycleSubscriber($app);

        $sut->afterStartup();

        self::assertSame(['warmer'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
    }

    public function testAfterStartupLogsErrorAndWarmsUpWhenLumenApplicationCannotBoot(): void
    {
        /** @var \ArrayObject<int, string> $calls */
        $calls = new ArrayObject();
        $expectedContext = [
            'exception' => new RuntimeException(FailingBootServiceProviderStub::EXCEPTION_MESSAGE),
        ];
        $logHandler = new TestHandler();
        $app = $this->getApplication([]);
        $app->instance(LoggerInterface::class, new Logger('test', [$logHandler]));
        $app->instance(SnapStartWarmerStub::class, new SnapStartWarmerStub($calls));
        $app->tag([SnapStartWarmerStub::class], [SnapStartTag::Warmer->value]);
        $app->register(FailingBootServiceProviderStub::class);
        $app->singleton(
            SnapStartLifecycleSubscriber::class,
            fn (Container $app): SnapStartLifecycleSubscriber => new SnapStartLifecycleSubscriber(
                warmers: $app->tagged(SnapStartTag::Warmer->value),
                afterRestoreHooks: $app->tagged(SnapStartTag::AfterRestoreHook->value),
                markerPath: $this->markerPath
            )
        );
        $sut = new LazySnapStartLifecycleSubscriber($app);

        $sut->afterStartup();

        self::assertCount(1, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart could not boot the Lumen application', $logHandler->getRecords()[0]->message);
        self::assertEquals($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame(['warmer'], $calls->getArrayCopy());
        self::assertFileExists($this->markerPath);
    }

    #[RunInSeparateProcess]
    public function testAfterStartupLogsErrorWhenSubscriberCannotBeCreated(): void
    {
        // The fallback subscriber writes the default marker, removed in tearDown
        $this->markerPath = \sys_get_temp_dir() . '/easy-serverless-snap-start-initialized';
        (new Filesystem())->remove($this->markerPath);
        $exception = new RuntimeException('Subscriber cannot be created.');
        $expectedContext = [
            'exception' => $exception,
        ];
        $logHandler = new TestHandler();
        $app = $this->getApplication([]);
        $app->instance(LoggerInterface::class, new Logger('test', [$logHandler]));
        $app->bind(SnapStartLifecycleSubscriber::class, static fn (): never => throw $exception);
        $sut = new LazySnapStartLifecycleSubscriber($app);

        $sut->afterStartup();

        self::assertCount(1, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart lifecycle subscriber could not be created', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertFileExists($this->markerPath);
    }

    public function testBeforeInvokeDoesNotResolveSubscriberBeforeAfterStartup(): void
    {
        /** @var \ArrayObject<int, string> $calls */
        $calls = new ArrayObject();
        $app = $this->getApplication([]);
        $app->instance(AfterRestoreHookStub::class, new AfterRestoreHookStub($calls));
        $app->tag([AfterRestoreHookStub::class], [SnapStartTag::AfterRestoreHook->value]);
        $app->singleton(
            SnapStartLifecycleSubscriber::class,
            fn (Container $app): SnapStartLifecycleSubscriber => new SnapStartLifecycleSubscriber(
                warmers: $app->tagged(SnapStartTag::Warmer->value),
                afterRestoreHooks: $app->tagged(SnapStartTag::AfterRestoreHook->value),
                markerPath: $this->markerPath
            )
        );
        $sut = new LazySnapStartLifecycleSubscriber($app);

        $sut->beforeInvoke(static fn (): null => null, [], Context::fake());

        self::assertFalse($app->resolved(SnapStartLifecycleSubscriber::class));
        self::assertSame([], $calls->getArrayCopy());
        self::assertFileDoesNotExist($this->markerPath);
    }

    #[RunInSeparateProcess]
    public function testBeforeInvokeReseedsMtRandWhenSubscriberCannotBeCreated(): void
    {
        // The fallback subscriber writes the default marker, removed in tearDown
        $this->markerPath = \sys_get_temp_dir() . '/easy-serverless-snap-start-initialized';
        (new Filesystem())->remove($this->markerPath);
        $app = $this->getApplication([]);
        $app->instance(LoggerInterface::class, new NullLogger());
        $app->bind(
            SnapStartLifecycleSubscriber::class,
            static fn (): never => throw new RuntimeException('Subscriber cannot be created.')
        );
        $sut = new LazySnapStartLifecycleSubscriber($app);
        $sut->afterStartup();
        \mt_srand(42);
        $fixedSeedSequence = [\mt_rand(), \mt_rand(), \mt_rand()];
        \mt_srand(42);

        $sut->beforeInvoke(static fn (): null => null, [], Context::fake());

        self::assertNotSame($fixedSeedSequence, [\mt_rand(), \mt_rand(), \mt_rand()]);
    }

    public function testBeforeInvokeRunsAfterRestoreHooksAfterWarmUp(): void
    {
        /** @var \ArrayObject<int, string> $calls */
        $calls = new ArrayObject();
        $app = $this->getApplication([]);
        $app->instance(SnapStartWarmerStub::class, new SnapStartWarmerStub($calls));
        $app->instance(AfterRestoreHookStub::class, new AfterRestoreHookStub($calls));
        // Tagged on boot, so they are known only once the Lumen application is booted
        $app->register(TagSnapStartServicesOnBootServiceProviderStub::class);
        $app->singleton(
            SnapStartLifecycleSubscriber::class,
            fn (Container $app): SnapStartLifecycleSubscriber => new SnapStartLifecycleSubscriber(
                warmers: $app->tagged(SnapStartTag::Warmer->value),
                afterRestoreHooks: $app->tagged(SnapStartTag::AfterRestoreHook->value),
                markerPath: $this->markerPath
            )
        );
        $sut = new LazySnapStartLifecycleSubscriber($app);
        $sut->afterStartup();

        $sut->beforeInvoke(static fn (): null => null, [], Context::fake());

        self::assertSame(['warmer', 'hook'], $calls->getArrayCopy());
    }
}
