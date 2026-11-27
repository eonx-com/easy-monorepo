<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

interface IdempotencyKeyAwareMessageInterface
{
    public const IDEMPOTENCY_KEY_MAX_LENGTH = 255;

    public function getIdempotencyKey(): ?string;

    /**
     * The notification service delivers messages with the same key only once per recipient
     * (device for push, topic for real time), null removes the key.
     *
     * @throws \EonX\EasyNotification\Exception\InvalidIdempotencyKeyException
     */
    public function setIdempotencyKey(?string $idempotencyKey): self;
}
