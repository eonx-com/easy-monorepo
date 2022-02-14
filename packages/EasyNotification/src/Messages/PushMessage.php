<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class PushMessage extends AbstractMessage
{
    /**
     * @var null|string
     */
    private $device;

    /**
     * @var null|string
     */
    private $token;

    /**
     * @param null|mixed[] $body
     */
    public function __construct(?string $device = null, ?string $token = null, ?array $body = null)
    {
        $this->device = $device;
        $this->token = $token;

        parent::__construct($body);
    }

    /**
     * @param null|mixed[] $body
     */
    public static function create(?string $device = null, ?string $token = null, ?array $body = null): self
    {
        return new self($device, $token, $body);
    }

    public function device(string $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getType(): string
    {
        return self::TYPE_PUSH;
    }

    public function token(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
