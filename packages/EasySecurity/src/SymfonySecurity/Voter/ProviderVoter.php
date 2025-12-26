<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Voter;

use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends \Symfony\Component\Security\Core\Authorization\Voter\Voter<string, mixed>
 */
final class ProviderVoter extends Voter
{
    public function __construct(
        private readonly SecurityContextResolverInterface $securityContextResolver,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject instanceof ProviderRestrictedInterface === false) {
            return false;
        }

        return $subject->getRestrictedProviderUniqueId() !== null;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        if ($subject instanceof ProviderRestrictedInterface === false) {
            throw new InvalidArgumentException(\sprintf(
                'Subject must be instance of "%s", "%s" given.',
                ProviderRestrictedInterface::class,
                \get_debug_type($subject)
            ));
        }

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
