<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Serializer;

use EonX\EasyBatch\Common\Exception\BatchItemMessageSignatureInvalidException;
use EonX\EasyBatch\Common\Signer\MessageSignerInterface;
use UnexpectedValueException;

final readonly class MessageSerializer implements MessageSerializerInterface
{
    private const string V1_SIGNATURE_PREFIX = 'v1:';

    public function __construct(
        private MessageSignerInterface $signer,
        private bool $allowUnsignedMessages = false,
    ) {}

    public function serialize(object $message): string
    {
        $body = \addslashes(\serialize($message));

        if (\preg_match('//u', $body) === false) {
            $body = \base64_encode($body);
        }

        return self::V1_SIGNATURE_PREFIX . $this->signer->sign($body) . ':' . $body;
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemMessageSignatureInvalidException
     */
    public function unserialize(string $message): object
    {
        if (\str_starts_with($message, self::V1_SIGNATURE_PREFIX) === false) {
            if ($this->allowUnsignedMessages === false) {
                throw new BatchItemMessageSignatureInvalidException('Batch item message signature is missing.');
            }

            return $this->doUnserialize($message);
        }

        $parts = \explode(':', \substr($message, \strlen(self::V1_SIGNATURE_PREFIX)), 2);

        if (\count($parts) !== 2 || $this->signer->verify($parts[1], $parts[0]) === false) {
            throw new BatchItemMessageSignatureInvalidException('Batch item message signature is invalid.');
        }

        return $this->doUnserialize($parts[1]);
    }

    private function doUnserialize(string $message): object
    {
        if (\str_ends_with($message, '}') === false) {
            /** @var string $message */
            $message = \base64_decode($message, true);
        }

        $result = \unserialize(\stripslashes($message));

        if (\is_object($result) === false) {
            throw new UnexpectedValueException('Failed to unserialize message.');
        }

        return $result;
    }
}
