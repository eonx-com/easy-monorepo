<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;

final class ProviderFromJwtDataResolver extends AbstractContextDataResolver
{
    /**
     * @var \EonX\EasySecurity\Interfaces\ProviderProviderInterface
     */
    private $providerProvider;

    /**
     * ProviderFromJwtDataResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ProviderProviderInterface $providerProvider
     * @param null|int $priority
     */
    public function __construct(ProviderProviderInterface $providerProvider, ?int $priority = null)
    {
        $this->providerProvider = $providerProvider;

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

        $providerId = $this->getClaimSafely($token, 'provider');

        // If no providerId given in token, skip
        if (empty($providerId)) {
            return $data;
        }

        $data->setProvider($this->providerProvider->getProvider($providerId));

        return $data;
    }
}
