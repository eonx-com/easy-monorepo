<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends \Symfony\Component\Security\Core\Authorization\Voter\Voter<string, mixed>
 */
final class RoleVoter extends Voter
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $securityContextResolver;

    public function __construct(SecurityContextResolverInterface $securityContextResolver)
    {
        $this->securityContextResolver = $securityContextResolver;
    }

    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     */
    protected function supports($attribute, $subject): bool
    {
        return $this->securityContextResolver
            ->resolveContext()
            ->getAuthorizationMatrix()
            ->isRole((string)$attribute);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->securityContextResolver
            ->resolveContext()
            ->hasRole((string)$attribute);
    }
}
