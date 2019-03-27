<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;

final class BasicAuthApiToken implements BasicAuthApiTokenInterface
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
     * BasicAuthApiToken constructor.
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
