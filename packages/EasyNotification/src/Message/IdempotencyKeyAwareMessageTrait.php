<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Exception\InvalidIdempotencyKeyException;

trait IdempotencyKeyAwareMessageTrait
{
    private ?string $idempotencyKey = null;

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function setIdempotencyKey(?string $idempotencyKey): self
    {
        // The notification service drops a message with an invalid key without retrying it
        if ($idempotencyKey !== null && \trim($idempotencyKey) === '') {
            throw new InvalidIdempotencyKeyException('Idempotency key must not be empty.');
        }

        if (
            $idempotencyKey !== null
            && \mb_strlen($idempotencyKey) > IdempotencyKeyAwareMessageInterface::IDEMPOTENCY_KEY_MAX_LENGTH
        ) {
            throw new InvalidIdempotencyKeyException(\sprintf(
                'Idempotency key must not be longer than %d characters.',
                IdempotencyKeyAwareMessageInterface::IDEMPOTENCY_KEY_MAX_LENGTH
            ));
        }

        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }
}
