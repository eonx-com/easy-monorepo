<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityUserIdHolderInterface
{
    /**
     * Resolve the identity user id.
     *
     * @return string
     */
    public function getIdentityUserId(): string;

    /**
     * Set the identity user id.
     *
     * @param string $userId
     *
     * @return void
     */
    public function setIdentityUserId(string $userId): void;
}
