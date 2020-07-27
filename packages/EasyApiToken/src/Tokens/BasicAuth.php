<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\BasicAuthInterface;

final class BasicAuth implements BasicAuthInterface
{
    /**
     * @var string
     */
    private $original;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $username;

    public function __construct(string $username, string $password, string $original)
    {
        $this->password = $password;
        $this->username = $username;
        $this->original = $original;
    }

    public function getOriginalToken(): string
    {
        return $this->original;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return mixed[]
     */
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

\class_alias(BasicAuth::class, BasicAuthEasyApiToken::class);
