<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated Since 2.4, will be removed in 3.0.
 */
final class ProviderFromJwtModifier extends AbstractFromJwtContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\ProviderProviderInterface
     */
    private $providerProvider;

    public function __construct(
        ProviderProviderInterface $providerProvider,
        ?string $jwtClaim = null,
        ?int $priority = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null
    ) {
        $this->providerProvider = $providerProvider;

        parent::__construct($jwtClaim, $priority, $jwtClaimFetcher);
    }

    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */

        $providerId = $this->getMainClaim($token)['provider'] ?? null;

        // If no providerId given in token, skip
        if (empty($providerId)) {
            return;
        }

        $context->setProvider($this->providerProvider->getProvider($providerId));
    }
}
