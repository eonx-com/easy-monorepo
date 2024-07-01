<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Bundle;

use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnMessagesLimitSubscriber;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnTimeLimitSubscriber;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;

final class EasyAsyncBundleTest extends AbstractUnitTestCase
{
    public function testMessengerConfigWithEmptyConfig(): void
    {
        self::assertFalse(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/messages_and_time_limits
     */
    public function testMessengerConfigWithMessagesAndTimeLimits(): void
    {
        self::bootKernel(['environment' => 'messages_and_time_limits']);

        self::assertTrue(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/messages_limit
     */
    public function testMessengerConfigWithMessagesLimit(): void
    {
        self::bootKernel(['environment' => 'messages_limit']);

        self::assertTrue(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertFalse(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }

    /**
     * @see packages/EasyAsync/tests/Fixture/app/config/packages/time_limit
     */
    public function testMessengerConfigWithTimeLimit(): void
    {
        self::bootKernel(['environment' => 'time_limit']);

        self::assertFalse(self::getContainer()->has(StopWorkerOnMessagesLimitSubscriber::class));
        self::assertTrue(self::getContainer()->has(StopWorkerOnTimeLimitSubscriber::class));
    }
}
