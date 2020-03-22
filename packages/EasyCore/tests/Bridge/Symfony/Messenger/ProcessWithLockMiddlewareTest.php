<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Messenger;

use EonX\EasyCore\Bridge\Symfony\Messenger\ProcessWithLockMiddleware;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\MessengerMiddlewareStub;
use EonX\EasyCore\Tests\Stubs\LockServiceStub;
use EonX\EasyCore\Tests\Stubs\LockStub;
use EonX\EasyCore\Tests\Stubs\WithLockDataStub;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessWithLockMiddlewareTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerHandle(): iterable
    {
        $withData = new WithLockDataStub();
        $withData->setResource('resource');
        $withData->setTtl(60.0);

        yield 'Acquire lock' => [
            new Envelope($withData, [new ConsumedByWorkerStamp()]),
            new LockStub(true),
            false,
        ];

        yield 'Do Not Acquire lock' => [
            new Envelope($withData, [new ConsumedByWorkerStamp()]),
            new LockStub(false),
            true,
        ];

        yield 'Skipped because envelope does not have worker stamp' => [
            new Envelope($withData),
            new LockStub(false),
            false,
        ];

        yield 'Skipped because message does not implement interface' => [
            new Envelope(new \stdClass(), [new ConsumedByWorkerStamp()]),
            new LockStub(false),
            false,
        ];
    }

    /**
     * @dataProvider providerHandle
     */
    public function testProcessWithJobLog(Envelope $envelope, LockStub $lock, bool $sameEnvelope): void
    {
        $middleware = new ProcessWithLockMiddleware();
        $middleware->setLockService(new LockServiceStub($lock));

        $iterator = new \ArrayIterator([
            $middleware,
            new MessengerMiddlewareStub(static function (): Envelope {
                return new Envelope(new \stdClass());
            }),
        ]);

        $newEnvelope = $iterator->current()->handle($envelope, new StackMiddleware($iterator));

        self::assertEquals($sameEnvelope, \spl_object_hash($envelope) === \spl_object_hash($newEnvelope));
    }
}
