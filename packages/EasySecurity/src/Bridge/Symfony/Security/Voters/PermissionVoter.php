<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends \Symfony\Component\Security\Core\Authorization\Voter\Voter<string, mixed>
 */
final class PermissionVoter extends Voter
{
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->securityContextResolver
            ->resolveContext()
            ->getAuthorizationMatrix()
            ->isPermission($attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->securityContextResolver
            ->resolveContext()
            ->hasPermission($attribute);
    }
}
