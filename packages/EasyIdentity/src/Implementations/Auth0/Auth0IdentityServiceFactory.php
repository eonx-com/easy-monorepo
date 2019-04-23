<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class Auth0IdentityServiceFactory
{
    /**
     * Create the Auth0 Identity service.
     *
     * @param mixed[] $config
     * @param null|\GuzzleHttp\ClientInterface $client
     *
     * @return \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityService
     */
    public function create(array $config, ?ClientInterface $client = null): Auth0IdentityService
    {
        $config = new Config($config);
        $client = $client ?? new Client(['base_uri' => \sprintf('https://%s', $config->getDomain())]);

        $authFactory = new AuthenticationApiClientFactory($config);
        $managementFactory = new ManagementApiClientFactory($config, new ManagementTokenProvider($client, $config));
        $tokenVerifierFactory = new TokenVerifierFactory($config);

        return new Auth0IdentityService($authFactory, $config, $managementFactory, $tokenVerifierFactory);
    }
}

\class_alias(
    Auth0IdentityServiceFactory::class,
    'StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory',
    false
);
