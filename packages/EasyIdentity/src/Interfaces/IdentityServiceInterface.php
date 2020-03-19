<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Interfaces;

interface IdentityServiceInterface
{
    public function createUser(IdentityUserInterface $user): IdentityUserInterface;

    /**
     * @return mixed
     */
    public function decodeToken(string $token);

    public function deleteUser(IdentityUserInterface $user): void;

    public function getUser(IdentityUserInterface $user): IdentityUserInterface;

    public function loginUser(IdentityUserInterface $user): IdentityUserInterface;

    /**
     * @param mixed[] $data
     */
    public function updateUser(IdentityUserInterface $user, array $data): IdentityUserInterface;
}
