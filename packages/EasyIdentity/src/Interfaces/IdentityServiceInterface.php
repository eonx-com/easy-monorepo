<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Interfaces;

interface IdentityServiceInterface
{
    /**
     * Create user for given data.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException
     */
    public function createUser(IdentityUserInterface $user): IdentityUserInterface;

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
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return void
     */
    public function deleteUser(IdentityUserInterface $user): void;

    /**
     * Get user information for given id.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     */
    public function getUser(IdentityUserInterface $user): IdentityUserInterface;

    /**
     * Login given user.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     */
    public function loginUser(IdentityUserInterface $user): IdentityUserInterface;

    /**
     * Update user for given id with given data.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed[] $data
     *
     * @return \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface
     */
    public function updateUser(IdentityUserInterface $user, array $data): IdentityUserInterface;
}

\class_alias(
    IdentityServiceInterface::class,
    \StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceInterface::class,
    false
);
