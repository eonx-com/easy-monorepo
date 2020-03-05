<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserFromJwtModifier extends AbstractContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\UserProviderInterface
     */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider, ?int $priority = null)
    {
        $this->userProvider = $userProvider;

        parent::__construct($priority);
    }

    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $userId = $this->getClaimSafely($token, 'sub');

        // If no userId given in token, skip
        if (empty($userId)) {
            return;
        }

        $context->setUser(
            $this->userProvider->getUser($userId, $this->getClaimSafely($token, ContextInterface::JWT_MANAGE_CLAIM, []))
        );
    }
}
