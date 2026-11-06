<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Serializer;

use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\ObjectEncryptor;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptor;
use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasher;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Encryptable\Normalizer\HashNormalizer;
use EonX\EasyEncryption\Encryptable\Serializer\EncryptableAwareMessengerSerializer;
use EonX\EasyEncryption\Encryptable\Signer\HmacMessengerEnvelopeSigner;
use EonX\EasyEncryption\Encryptable\Signer\MessengerEnvelopeSignerInterface;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Stub\Message\MessageStub;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

final class EncryptableAwareMessengerSerializerTest extends AbstractSymfonyTestCase
{
    private const string NEW_KEY = 'new-signing-key';

    private const string OLD_KEY = 'old-signing-key';

    private const string SIGNATURE_HEADER = 'easy_encryption_signature';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setAppSecret('0123456789abcdef0123456789abcdef');
    }

    public function testDecodeFailsWhenBodyIsTampered(): void
    {
        $sut = $this->createSerializer();
        $encoded = $sut->encode(new Envelope(new MessageStub()));
        $encoded['body'] .= 'x';

        $this->expectException(MessageDecodingFailedException::class);
        $this->expectExceptionMessage('Message signature is invalid.');

        $sut->decode($encoded);
    }

    public function testDecodeFailsWhenDescriptiveHeaderIsTampered(): void
    {
        $sut = $this->createSerializer();
        $encoded = $sut->encode(new Envelope(new MessageStub()));
        $encoded['headers']['type'] = self::class;

        $this->expectException(MessageDecodingFailedException::class);
        $this->expectExceptionMessage('Message signature is invalid.');

        $sut->decode($encoded);
    }

    public function testDecodeFailsWhenSignatureIsMissingAndUnsignedAreRejected(): void
    {
        $sut = $this->createSerializer(allowUnsignedMessages: false);
        $encoded = new PhpSerializer()
->encode(new Envelope(new MessageStub()));

        $this->expectException(MessageDecodingFailedException::class);
        $this->expectExceptionMessage('Message signature is missing.');

        $sut->decode($encoded);
    }

    public function testDecodeRejectsSignatureFromAnotherDomain(): void
    {
        $sut = $this->createSerializer();
        $encoded = $sut->encode(new Envelope(new MessageStub()));
        $encoded['headers'][self::SIGNATURE_HEADER] = \hash_hmac('sha512', $encoded['body'], self::NEW_KEY);

        $this->expectException(MessageDecodingFailedException::class);
        $this->expectExceptionMessage('Message signature is invalid.');

        $sut->decode($encoded);
    }

    public function testDecodeRejectsSignatureFromRotatedOutKey(): void
    {
        $encodedWithOldKey = $this->createSerializer(signer: new HmacMessengerEnvelopeSigner(self::OLD_KEY))
            ->encode(new Envelope(new MessageStub()));
        $sut = $this->createSerializer(signer: new HmacMessengerEnvelopeSigner([self::NEW_KEY]));

        $this->expectException(MessageDecodingFailedException::class);
        $this->expectExceptionMessage('Message signature is invalid.');

        $sut->decode($encodedWithOldKey);
    }

    public function testDecodeSucceedsWithUnsignedMessageWhenAllowed(): void
    {
        $encoded = new PhpSerializer()
->encode(new Envelope(new MessageStub(content: 'drained')));
        $sut = $this->createSerializer(allowUnsignedMessages: true);

        $message = $sut->decode($encoded)
            ->getMessage();

        self::assertInstanceOf(MessageStub::class, $message);
        self::assertSame('drained', $message->content);
    }

    public function testDecodeVerifiesMessageSignedWithRotatedOutKeyDuringRotation(): void
    {
        $encodedWithOldKey = $this->createSerializer(signer: new HmacMessengerEnvelopeSigner(self::OLD_KEY))
            ->encode(new Envelope(new MessageStub(content: 'queued-before-rotation')));
        // During rotation the new key signs, but the old key is kept last so in-flight messages still verify
        $sut = $this->createSerializer(signer: new HmacMessengerEnvelopeSigner([self::NEW_KEY, self::OLD_KEY]));

        $message = $sut->decode($encodedWithOldKey)
            ->getMessage();

        self::assertInstanceOf(MessageStub::class, $message);
        self::assertSame('queued-before-rotation', $message->content);
    }

    public function testEncodeDecodeSucceedsWithFullyEncryptedMessage(): void
    {
        $sut = $this->createSerializer(fullyEncryptedMessages: [MessageStub::class]);

        $encoded = $sut->encode(new Envelope(new MessageStub(content: 'top-secret')));

        self::assertSame('full', $encoded['headers']['encryption_type']);
        self::assertArrayHasKey(self::SIGNATURE_HEADER, $encoded['headers']);
        self::assertStringNotContainsString('top-secret', $encoded['body']);

        $message = $sut->decode($encoded)
            ->getMessage();

        self::assertInstanceOf(MessageStub::class, $message);
        self::assertSame('top-secret', $message->content);
    }

    public function testEncodeDecodeSucceedsWithPartiallyEncryptedMessage(): void
    {
        $sut = $this->createSerializer();

        $encoded = $sut->encode(new Envelope(new EncryptableEntityStub(email: 'jane@example.com')));

        self::assertSame('partial', $encoded['headers']['encryption_type']);
        self::assertStringNotContainsString('jane@example.com', $encoded['body']);

        $message = $sut->decode($encoded)
            ->getMessage();

        self::assertInstanceOf(EncryptableEntityStub::class, $message);
        self::assertSame('jane@example.com', $message->getEmail());
    }

    public function testEncodeDecodeSucceedsWithPlainMessage(): void
    {
        $sut = $this->createSerializer();

        $encoded = $sut->encode(new Envelope(new MessageStub(content: 'hello')));

        self::assertArrayHasKey(self::SIGNATURE_HEADER, $encoded['headers']);
        self::assertSame(MessageStub::class, $encoded['headers']['type']);

        $message = $sut->decode($encoded)
            ->getMessage();

        self::assertInstanceOf(MessageStub::class, $message);
        self::assertSame('hello', $message->content);
    }

    /**
     * @param string[] $fullyEncryptedMessages
     */
    private function createSerializer(
        bool $allowUnsignedMessages = true,
        array $fullyEncryptedMessages = [],
        ?MessengerEnvelopeSignerInterface $signer = null,
    ): EncryptableAwareMessengerSerializer {
        $encryptor = $this->getKernel()
            ->getContainer()
            ->get(EncryptorInterface::class);
        $stringEncryptor = new StringEncryptor($encryptor, 'app', 16224);
        $metadata = new EncryptableMetadata();
        $fieldHasher = new EncryptableFieldHasher(
            new HmacSha512HashCalculator(self::NEW_KEY),
            $metadata,
            new HashNormalizer(),
            []
        );

        return new EncryptableAwareMessengerSerializer(
            $stringEncryptor,
            new ObjectEncryptor($stringEncryptor, $fieldHasher),
            $metadata,
            new PhpSerializer(),
            $signer ?? new HmacMessengerEnvelopeSigner([self::NEW_KEY]),
            $fullyEncryptedMessages,
            $allowUnsignedMessages
        );
    }
}
