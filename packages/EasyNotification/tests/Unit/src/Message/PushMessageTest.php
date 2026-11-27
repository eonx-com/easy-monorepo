<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Message;

use EonX\EasyNotification\Exception\InvalidIdempotencyKeyException;
use EonX\EasyNotification\Message\PushMessage;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PushMessageTest extends AbstractUnitTestCase
{
    /**
     * @see testSetIdempotencyKeyThrowsExceptionIfKeyIsInvalid
     */
    public static function provideInvalidIdempotencyKeyData(): iterable
    {
        yield 'Empty' => [''];

        yield 'Whitespace only' => ['   '];

        yield 'Longer than 255 characters' => [\str_repeat('a', 256)];
    }

    public function testIdempotencyKeyIsNullByDefault(): void
    {
        $message = PushMessage::create('device', 'token');

        self::assertNull($message->getIdempotencyKey());
    }

    public function testSetIdempotencyKeyAcceptsKeyOf255Characters(): void
    {
        // Multibyte characters count as one, the same as in the notification service
        $idempotencyKey = \str_repeat('ü', 255);

        $message = PushMessage::create('device', 'token')->setIdempotencyKey($idempotencyKey);

        self::assertSame($idempotencyKey, $message->getIdempotencyKey());
    }

    public function testSetIdempotencyKeyRemovesKeyIfNull(): void
    {
        $message = PushMessage::create('device', 'token')->setIdempotencyKey('my-idempotency-key');

        $message->setIdempotencyKey(null);

        self::assertNull($message->getIdempotencyKey());
    }

    public function testSetIdempotencyKeySucceeds(): void
    {
        $message = PushMessage::createDoNotSend('device')->setIdempotencyKey('my-idempotency-key');

        self::assertSame('my-idempotency-key', $message->getIdempotencyKey());
    }

    #[DataProvider('provideInvalidIdempotencyKeyData')]
    public function testSetIdempotencyKeyThrowsExceptionIfKeyIsInvalid(string $idempotencyKey): void
    {
        $this->expectException(InvalidIdempotencyKeyException::class);

        PushMessage::create('device', 'token')->setIdempotencyKey($idempotencyKey);
    }
}
