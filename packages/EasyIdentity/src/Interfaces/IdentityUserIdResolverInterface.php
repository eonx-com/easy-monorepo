<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityUserIdResolverInterface
{
    /**
     * Resolve the identity user id.
     *
     * @return string
     */
    public function getUserId(): string;
}
