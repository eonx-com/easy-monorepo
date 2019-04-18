<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tokens;

use StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;

final class BasicAuthEasyApiToken implements BasicAuthEasyApiTokenInterface
{
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
     */
    public function __construct(string $username, string $password)
    {
        $this->password = $password;
        $this->username = $username;
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
     * Get password from payload.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
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

\class_alias(
    BasicAuthEasyApiToken::class,
    'LoyaltyCorp\EasyApiToken\Tokens\BasicAuthEasyApiToken',
    false
);
