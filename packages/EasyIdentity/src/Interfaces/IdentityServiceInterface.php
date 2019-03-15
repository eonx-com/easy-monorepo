<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityServiceInterface
{
    /**
     * Create user for given data.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function createUser(array $data): array;

    /**
     * Validate and decode given token and return decoded version.
     *
     * @param string $token
     *
     * @return mixed
     */
    public function decodeToken(string $token);

    /**
     * Delete user for given id.
     *
     * @param string $userId
     *
     * @return void
     */
    public function deleteUser(string $userId): void;

    /**
     * Get user information for given id.
     *
     * @param string $userId
     *
     * @return mixed[]
     */
    public function getUser(string $userId): array;

    /**
     * Login user for given data.
     *
     * @param mixed[] $data
     *
     * @return mixed
     */
    public function loginUser(array $data);

    /**
     * Login user for given username and password.
     *
     * @param string $username
     * @param string $password
     * @param null|mixed[] $data
     *
     * @return mixed
     */
    public function loginUserWithUsernamePassword(string $username, string $password, ?array $data = null);

    /**
     * Update user for given id with given data.
     *
     * @param string $userId
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function updateUser(string $userId, array $data): array;
}
