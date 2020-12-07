<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use EonX\EasyIdentity\Implementations\IdentityUserService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class Auth0IdentityServiceFactory
{
    /**
     * @param mixed[] $configData
     */
    public function create(array $configData, ?ClientInterface $client = null): Auth0IdentityService
    {
        $config = new Config($configData);
        $client = $client ?? new Client([
            'base_uri' => $this->createBaseUri($config),
        ]);

        $authFactory = new AuthenticationApiClientFactory($config);
        $identityUserService = new IdentityUserService();
        $managementFactory = new ManagementApiClientFactory($config, new ManagementTokenProvider($client, $config));
        $tokenVerifierFactory = new TokenVerifierFactory($config);

        return new Auth0IdentityService(
            $authFactory,
            $config,
            $identityUserService,
            $managementFactory,
            $tokenVerifierFactory
        );
    }

    private function createBaseUri(Config $config): string
    {
        return $config->getDomain() === '' ? '' : \sprintf('https://%s', $config->getDomain());
    }
}
