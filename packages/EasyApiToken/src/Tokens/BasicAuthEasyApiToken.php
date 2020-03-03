<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;

final class BasicAuthEasyApiToken implements BasicAuthEasyApiTokenInterface
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

    /**
     * BasicAuthEasyApiToken constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $original
     */
    public function __construct(string $username, string $password, string $original)
    {
        $this->password = $password;
        $this->username = $username;
        $this->original = $original;
    }

    /**
     * Get original string token.
     *
     * @return string
     */
    public function getOriginalToken(): string
    {
        return $this->original;
    }

    /**
     * Get password from payload.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return [
            'password' => $this->password,
            'username' => $this->username
        ];
    }

    /**
     * Get username from payload.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
}
