<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Common\Serializer;

use EonX\EasyBatch\Common\Exception\BatchItemMessageSignatureInvalidException;
use EonX\EasyBatch\Common\Serializer\MessageSerializer;
use EonX\EasyBatch\Common\Signer\HmacMessageSigner;
use EonX\EasyBatch\Tests\Stub\Message\UnserializeTrackingMessageStub;
use EonX\EasyBatch\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MessageSerializerTest extends AbstractUnitTestCase
{
    protected function setUp(): void
    {
        UnserializeTrackingMessageStub::reset();

        parent::setUp();
    }

    /**
     * @see testItThrowsExceptionForInvalidSignature
     */
    public static function provideInvalidSignatureMessages(): iterable
    {
        $body = \addslashes(\serialize(new UnserializeTrackingMessageStub()));
        $tamperedBody = \str_replace('s:5:\"value\"', 's:5:\"other\"', $body);
        $foreignSigner = new HmacMessageSigner('foreign-secret');
        $signer = new HmacMessageSigner('secret');

        yield 'Foreign secret' => ['v1:' . $foreignSigner->sign($body) . ':' . $body];
        yield 'Tampered body' => ['v1:' . $signer->sign($body) . ':' . $tamperedBody];
        yield 'No signature separator' => ['v1:' . $body];
        yield 'Empty signature' => ['v1::' . $body];
    }

    /**
     * @see testItSerializesAndUnserializesSignedMessage
     */
    public static function provideMessageValues(): iterable
    {
        yield 'UTF-8 body' => ['value'];
        yield 'Base64 body' => ["\xB1\x31"];
    }

    public function testItAcceptsMessageSignedWithPreviousSecret(): void
    {
        $message = new MessageSerializer(new HmacMessageSigner('old-secret'))
            ->serialize(new UnserializeTrackingMessageStub());
        $sut = new MessageSerializer(new HmacMessageSigner(['new-secret', 'old-secret']));

        $result = $sut->unserialize($message);

        self::assertEquals(new UnserializeTrackingMessageStub(), $result);
    }

    public function testItAcceptsUnsignedMessageWhenUnsignedMessagesAreAllowed(): void
    {
        $sut = new MessageSerializer(signer: new HmacMessageSigner('secret'), allowUnsignedMessages: true);

        $result = $sut->unserialize(\addslashes(\serialize(new UnserializeTrackingMessageStub('legacy'))));

        self::assertEquals(new UnserializeTrackingMessageStub('legacy'), $result);
    }

    #[DataProvider('provideMessageValues')]
    public function testItSerializesAndUnserializesSignedMessage(string $value): void
    {
        $sut = new MessageSerializer(new HmacMessageSigner('secret'));

        $serialized = $sut->serialize(new UnserializeTrackingMessageStub($value));
        $result = $sut->unserialize($serialized);

        self::assertMatchesRegularExpression('/^v1:[a-f0-9]{64}:/', $serialized);
        self::assertInstanceOf(UnserializeTrackingMessageStub::class, $result);
        self::assertSame($value, $result->getValue());
    }

    #[DataProvider('provideInvalidSignatureMessages')]
    public function testItThrowsExceptionForInvalidSignature(string $message): void
    {
        $sut = new MessageSerializer(new HmacMessageSigner('secret'));

        try {
            $sut->unserialize($message);
            self::fail('Exception was not thrown.');
        } catch (BatchItemMessageSignatureInvalidException $exception) {
            self::assertSame('Batch item message signature is invalid.', $exception->getMessage());
        }

        self::assertFalse(UnserializeTrackingMessageStub::isUnserialized());
    }

    public function testItThrowsExceptionForUnsignedMessageByDefault(): void
    {
        $sut = new MessageSerializer(new HmacMessageSigner('secret'));

        try {
            $sut->unserialize(\addslashes(\serialize(new UnserializeTrackingMessageStub())));
            self::fail('Exception was not thrown.');
        } catch (BatchItemMessageSignatureInvalidException $exception) {
            self::assertSame('Batch item message signature is missing.', $exception->getMessage());
        }

        self::assertFalse(UnserializeTrackingMessageStub::isUnserialized());
    }
}
