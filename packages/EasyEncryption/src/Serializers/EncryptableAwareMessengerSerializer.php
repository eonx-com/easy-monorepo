<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Serializers;

use EonX\EasyEncryption\Encryptors\ObjectEncryptorInterface;
use EonX\EasyEncryption\Encryptors\StringEncryptorInterface;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use EonX\EasyEncryption\Metadata\EncryptableMetadataInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use UnexpectedValueException;

final class EncryptableAwareMessengerSerializer implements SerializerInterface
{
    private const ENCRYPTION_TYPE_FULL = 'full';

    private const ENCRYPTION_TYPE_PARTIAL = 'partial';

    private const ENVELOPE_HEADER_ENCRYPTABLE_FIELD_NAMES = 'encryptable_field_names';

    private const ENVELOPE_HEADER_ENCRYPTED = 'encrypted';

    private const ENVELOPE_HEADER_ENCRYPTION_TYPE = 'encryption_type';

    private const ENVELOPE_HEADER_TYPE = 'type';

    public function __construct(
        private StringEncryptorInterface $stringEncryptor,
        private ObjectEncryptorInterface $objectEncryptor,
        private EncryptableMetadataInterface $encryptableMetadata,
        private SerializerInterface $serializer,
        private array $fullyEncryptedMessages,
    ) {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $isEncrypted = (bool)($encodedEnvelope['headers'][self::ENVELOPE_HEADER_ENCRYPTED] ?? false);
        $encryptionType = $encodedEnvelope['headers'][self::ENVELOPE_HEADER_ENCRYPTION_TYPE] ?? null;

        if ($isEncrypted === false) {
            return $this->serializer->decode($encodedEnvelope);
        }

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

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();

        if (
            $message instanceof EncryptableInterface &&
            \in_array($message::class, $this->fullyEncryptedMessages, true)
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
            self::ENVELOPE_HEADER_ENCRYPTED => false,
            self::ENVELOPE_HEADER_TYPE => $message::class,
        ];

        if ($message instanceof EncryptableInterface) {
            $this->objectEncryptor->encrypt($message);
            $headers[self::ENVELOPE_HEADER_ENCRYPTED] = true;
            $headers[self::ENVELOPE_HEADER_ENCRYPTION_TYPE] = self::ENCRYPTION_TYPE_PARTIAL;
            $headers[self::ENVELOPE_HEADER_ENCRYPTABLE_FIELD_NAMES] = \json_encode(
                $this->encryptableMetadata->getEncryptableFieldNames($message::class)
            );
        }

        $encodedEnvelope = $this->serializer->encode($envelope);

        if (\in_array($message::class, $this->fullyEncryptedMessages, true)) {
            $encodedBody = $encodedEnvelope['body']
                ?? throw new UnexpectedValueException('Encoded envelope should have a "body" value.');
            $encodedEnvelope['body'] = $this->stringEncryptor->encrypt((string)$encodedBody)->value;
            $headers[self::ENVELOPE_HEADER_ENCRYPTED] = true;
            $headers[self::ENVELOPE_HEADER_ENCRYPTION_TYPE] = self::ENCRYPTION_TYPE_FULL;
        }

        $encodedEnvelope['headers'] ??= [];
        $encodedEnvelope['headers'] = [...$encodedEnvelope['headers'], ...$headers];

        return $encodedEnvelope;
    }
}
