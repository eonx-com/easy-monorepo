<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Doctrine;

use EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber;
use EonX\EasyEntityChange\Exceptions\InvalidDispatcherException;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use EonX\EasyEntityChange\Tests\Stubs\EventDispatcherStub;

final class EntityChangeSubscriberTest extends AbstractTestCase
{
    /**
     * Test events subscription.
     *
     * @return void
     */
    public function testSubscribedEvents(): void
    {
        $dispatcherStub = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcherStub);

        static::assertSame(['onFlush', 'postFlush'], $subscriber->getSubscribedEvents());
    }

    /**
     * Test events subscription.
     *
     * @return void
     *
     * @throws \EonX\EasyEntityChange\Exceptions\InvalidDispatcherException
     */
    public function testThrowOnInvalidDispatch(): void
    {
        $this->expectException(InvalidDispatcherException::class);
        $this->expectExceptionMessage('exceptions.services.entitychange.doctrine.invalid_dispatcher');

        $dispatcherStub = new EventDispatcherStub();
        // Simulate a misconfigured dispatcher
        $dispatcherStub->addReturn(null);

        $subscriber = new EntityChangeSubscriber($dispatcherStub);

        $subscriber->postFlush();
    }
}
