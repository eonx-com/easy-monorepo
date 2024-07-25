<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\Type;

final class PushMessage extends AbstractMessage
{
    private const DO_NOT_SEND_TOKEN = 'do_not_send_token';

    public function __construct(
        private ?string $device = null,
        private ?string $token = null,
        ?array $body = null,
    ) {
        parent::__construct($body);
    }

    public static function create(?string $device = null, ?string $token = null, ?array $body = null): self
    {
        return new self($device, $token, $body);
    }

    public static function createDoNotSend(?string $device = null, ?array $body = null): self
    {
        return new self($device, self::DO_NOT_SEND_TOKEN, $body);
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

    public function getType(): Type
    {
        return Type::Push;
    }

    public function token(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
