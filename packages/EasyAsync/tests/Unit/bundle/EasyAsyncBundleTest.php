<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Bundle;

use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersClearMiddleware;
use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersSanityCheckMiddleware;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnMessagesLimitSubscriber;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnTimeLimitSubscriber;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;

final class EasyAsyncBundleTest extends AbstractUnitTestCase
{
    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/middleware_clear_disabled
     */
    public function testMessengerConfigWithDoctrineManagersClearMiddlewareDisabled(): void
    {
        self::bootKernel(['environment' => 'middleware_clear_disabled']);

        self::assertFalse(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertTrue(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/middleware_sanity_check_disabled
     */
    public function testMessengerConfigWithDoctrineManagersSanityCheckMiddlewareDisabled(): void
    {
        self::bootKernel(['environment' => 'middleware_sanity_check_disabled']);

        self::assertTrue(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertFalse(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
    }

    public function testMessengerConfigWithEmptyConfig(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertTrue(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/messages_and_time_limits
     */
    public function testMessengerConfigWithMessagesAndTimeLimits(): void
    {
        self::bootKernel(['environment' => 'messages_and_time_limits']);

        self::assertTrue(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertTrue(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/messages_limit
     */
    public function testMessengerConfigWithMessagesLimit(): void
    {
        self::bootKernel(['environment' => 'messages_limit']);

        self::assertTrue(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertTrue(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/middleware_disabled
     */
    public function testMessengerConfigWithMessengerMiddlewareDisabled(): void
    {
        self::bootKernel(['environment' => 'middleware_disabled']);

        self::assertFalse(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertFalse(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/middleware_disabled_with_child_enabled
     */
    public function testMessengerConfigWithMessengerMiddlewareDisabledAndChildEnabled(): void
    {
        self::bootKernel(['environment' => 'middleware_disabled_with_child_enabled']);

        self::assertFalse(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertFalse(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/time_limit
     */
    public function testMessengerConfigWithTimeLimit(): void
    {
        self::bootKernel(['environment' => 'time_limit']);

        self::assertTrue(self::getContainer()->has(DoctrineManagersClearMiddleware::class));
        self::assertTrue(self::getContainer()->has(DoctrineManagersSanityCheckMiddleware::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }
}
