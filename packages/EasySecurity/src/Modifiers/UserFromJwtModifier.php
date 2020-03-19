<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserFromJwtModifier extends AbstractFromJwtContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\UserProviderInterface
     */
    private $userProvider;

    public function __construct(
        UserProviderInterface $userProvider,
        ?string $jwtClaim = null,
        ?int $priority = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null
    ) {
        $this->userProvider = $userProvider;

        parent::__construct($jwtClaim, $priority, $jwtClaimFetcher);
    }

    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $userId = $this->getClaim($token, 'sub');

        // If no userId given in token, skip
        if (empty($userId)) {
            return;
        }

        $context->setUser($this->userProvider->getUser($userId, $this->getMainClaim($token)));
    }
}
