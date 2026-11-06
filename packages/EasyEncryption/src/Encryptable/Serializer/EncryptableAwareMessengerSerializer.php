<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Serializer;

use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\ObjectEncryptorInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptorInterface;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadataInterface;
use EonX\EasyEncryption\Encryptable\Signer\MessengerEnvelopeSignerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use UnexpectedValueException;

final readonly class EncryptableAwareMessengerSerializer implements SerializerInterface
{
    private const string ENCRYPTION_TYPE_FULL = 'full';

    private const string ENCRYPTION_TYPE_PARTIAL = 'partial';

    private const string ENVELOPE_HEADER_ENCRYPTABLE_FIELD_NAMES = 'encryptable_field_names';

    private const string ENVELOPE_HEADER_ENCRYPTION_TYPE = 'encryption_type';

    private const string ENVELOPE_HEADER_SIGNATURE = 'easy_encryption_signature';

    private const string ENVELOPE_HEADER_TYPE = 'type';

    /**
     * Separates this HMAC domain from any other use of the same key (e.g. field hashing).
     */
    private const string SIGNATURE_DOMAIN = 'easy-encryption:messenger:v1';

    private const array SIGNED_HEADERS = [
        self::ENVELOPE_HEADER_ENCRYPTABLE_FIELD_NAMES,
        self::ENVELOPE_HEADER_ENCRYPTION_TYPE,
        self::ENVELOPE_HEADER_TYPE,
    ];

    public function __construct(
        private StringEncryptorInterface $stringEncryptor,
        private ObjectEncryptorInterface $objectEncryptor,
        private EncryptableMetadataInterface $encryptableMetadata,
        private SerializerInterface $serializer,
        private MessengerEnvelopeSignerInterface $signer,
        private array $fullyEncryptedMessages,
        private bool $allowUnsignedMessages = true,
    ) {}

    public function decode(array $encodedEnvelope): Envelope
    {
        $signature = $encodedEnvelope['headers'][self::ENVELOPE_HEADER_SIGNATURE] ?? null;

        // The signature must be verified before the body reaches the inner serializer's native unserialize(),
        // which is what keeps a forged transport payload from reaching a gadget chain
        if (\is_string($signature)) {
            if ($this->signer->verify($this->signedPayload($encodedEnvelope), $signature) === false) {
                throw new MessageDecodingFailedException('Message signature is invalid.');
            }

            return $this->doDecode($encodedEnvelope);
        }

        if ($this->allowUnsignedMessages === false) {
            throw new MessageDecodingFailedException('Message signature is missing.');
        }

        return $this->doDecode($encodedEnvelope);
    }

    /**
     * @return array{body: string, headers: array<string, string>}
     */
    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();

        if (
            $message instanceof EncryptableInterface
            && \in_array($message::class, $this->fullyEncryptedMessages, true)
        ) {
            throw new UnexpectedValueException(
                \sprintf(
                    'The %s message should not be fully encrypted because it implements the %s interface.',
                    $message::class,
                    EncryptableInterface::class
                )
            );
        }

        $headers = [
            self::ENVELOPE_HEADER_TYPE => $message::class,
        ];

        if ($message instanceof EncryptableInterface) {
            $this->objectEncryptor->encrypt($message);
            $headers[self::ENVELOPE_HEADER_ENCRYPTION_TYPE] = self::ENCRYPTION_TYPE_PARTIAL;
            $headers[self::ENVELOPE_HEADER_ENCRYPTABLE_FIELD_NAMES] = (string)\json_encode(
                $this->encryptableMetadata->getEncryptableFieldNames($message::class)
            );
        }

        $encodedEnvelope = $this->serializer->encode($envelope);

        if (\in_array($message::class, $this->fullyEncryptedMessages, true)) {
            $encodedBody = $encodedEnvelope['body']
                ?? throw new UnexpectedValueException('Encoded envelope should have a "body" value.');
            $encodedEnvelope['body'] = $this->stringEncryptor->encrypt((string)$encodedBody)->value;
            $headers[self::ENVELOPE_HEADER_ENCRYPTION_TYPE] = self::ENCRYPTION_TYPE_FULL;
        }

        $encodedEnvelope['headers'] ??= [];
        $encodedEnvelope['headers'] = [...$encodedEnvelope['headers'], ...$headers];
        $encodedEnvelope['headers'][self::ENVELOPE_HEADER_SIGNATURE] = $this->signer->sign(
            $this->signedPayload($encodedEnvelope)
        );

        return $encodedEnvelope;
    }

    /**
     * @param array{body: string, headers?: array<string, string>} $encodedEnvelope
     */
    private function doDecode(array $encodedEnvelope): Envelope
    {
        $encryptionType = $encodedEnvelope['headers'][self::ENVELOPE_HEADER_ENCRYPTION_TYPE] ?? null;

        if ($encryptionType === self::ENCRYPTION_TYPE_FULL) {
            $encryptedBody = $encodedEnvelope['body']
                ?? throw new MessageDecodingFailedException('Encoded envelope should have a "body" value.');
            $encodedEnvelope['body'] = $this->stringEncryptor->decrypt($encryptedBody);
        }

        $envelope = $this->serializer->decode($encodedEnvelope);
        $message = $envelope->getMessage();

        if ($message instanceof EncryptableInterface) {
            $this->objectEncryptor->decrypt($message);
        }

        return $envelope;
    }

    private function signedPayload(array $encodedEnvelope): string
    {
        $headers = $encodedEnvelope['headers'] ?? [];
        $signedHeaders = [];

        foreach (self::SIGNED_HEADERS as $name) {
            if (\array_key_exists($name, $headers)) {
                $signedHeaders[$name] = $headers[$name];
            }
        }

        \ksort($signedHeaders);

        return self::SIGNATURE_DOMAIN . "\0" . \serialize([$encodedEnvelope['body'] ?? '', $signedHeaders]);
    }
}
