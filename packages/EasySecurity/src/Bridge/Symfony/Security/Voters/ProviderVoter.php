<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security\Voters;

use EonX\EasySecurity\Interfaces\ProviderRestrictedInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends \Symfony\Component\Security\Core\Authorization\Voter\Voter<string, mixed>
 */
final class ProviderVoter extends Voter
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
        if (($subject instanceof ProviderRestrictedInterface) === false) {
            return false;
        }

        /** @var \EonX\EasySecurity\Interfaces\ProviderRestrictedInterface $subject */

        return $subject->getRestrictedProviderUniqueId() !== null;
    }

    /**
     * @param string $attribute
     * @param \EonX\EasySecurity\Interfaces\ProviderRestrictedInterface $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $provider = $this->securityContextResolver
            ->resolveContext()
            ->getProvider();

        if ($provider === null) {
            // AccessDenied if no provider on context
            return false;
        }

        return $provider->getUniqueId() === $subject->getRestrictedProviderUniqueId();
    }
}
