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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     *
     * @return void
     */
    public function deleteUser(IdentityUserIdResolverInterface $userIdResolver): void;

    /**
     * Get user information for given id.
     *
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     *
     * @return mixed[]
     */
    public function getUser(IdentityUserIdResolverInterface $userIdResolver): array;

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
     * @param \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface $userIdResolver
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function updateUser(IdentityUserIdResolverInterface $userIdResolver, array $data): array;
}
