<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

final readonly class BasicAuth implements ApiTokenInterface
{
    public function __construct(
        private string $username,
        private string $password,
        private string $original,
    ) {
    }

    public function getOriginalToken(): string
    {
        return $this->original;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPayload(): array
    {
        return [
            'password' => $this->password,
            'username' => $this->username,
        ];
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
