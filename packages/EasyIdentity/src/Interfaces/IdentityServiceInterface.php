<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityServiceInterface
{
    /**
     * Create user for given data.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdHolderInterface $userIdHolder
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException
     */
    public function createUser(IdentityUserIdHolderInterface $userIdHolder, array $data): array;

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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdHolderInterface $userIdHolder
     *
     * @return void
     */
    public function deleteUser(IdentityUserIdHolderInterface $userIdHolder): void;

    /**
     * Get user information for given id.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdHolderInterface $userIdHolder
     *
     * @return mixed[]
     */
    public function getUser(IdentityUserIdHolderInterface $userIdHolder): array;

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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdHolderInterface $userIdHolder
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function updateUser(IdentityUserIdHolderInterface $userIdHolder, array $data): array;
}
