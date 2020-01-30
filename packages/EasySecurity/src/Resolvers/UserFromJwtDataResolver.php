<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;

final class UserFromJwtDataResolver extends AbstractContextDataResolver
{
    /**
     * @var \EonX\EasySecurity\Interfaces\UserProviderInterface
     */
    private $userProvider;

    /**
     * UserFromJwtDataResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\UserProviderInterface $userProvider
     * @param null|int $priority
     */
    public function __construct(UserProviderInterface $userProvider, ?int $priority = null)
    {
        $this->userProvider = $userProvider;

        parent::__construct($priority);
    }

    /**
     * Resolve context data.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function resolve(ContextResolvingDataInterface $data): ContextResolvingDataInterface
    {
        $token = $data->getApiToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return $data;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $userId = $this->getClaimSafely($token, 'sub');

        // If no userId given in token, skip
        if (empty($userId)) {
            return $data;
        }

        $data->setUser(
            $this->userProvider->getUser($userId, $this->getClaimSafely($token, ContextInterface::JWT_MANAGE_CLAIM, []))
        );

        return $data;
    }
}
