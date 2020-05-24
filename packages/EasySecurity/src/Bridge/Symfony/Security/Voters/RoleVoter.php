<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class RoleVoter extends Voter
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     */
    protected function supports($attribute, $subject): bool
    {
        return $this->securityContext->getAuthorizationMatrix()->isRole((string)$attribute);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->securityContext->hasRole((string)$attribute);
    }
}
