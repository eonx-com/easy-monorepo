<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use LoyaltyCorp\EasyIdentity\Implementations\IdentityUserService;

final class Auth0IdentityServiceFactory
{
    /**
     * Create the Auth0 Identity service.
     *
     * @param mixed[] $configData
     * @param null|\GuzzleHttp\ClientInterface $client
     *
     * @return \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityService
     */
    public function create(array $configData, ?ClientInterface $client = null): Auth0IdentityService
    {
        $config = new Config($configData);
        $client = $client ?? new Client(['base_uri' => $this->createBaseUri($config)]);

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

    /**
     * Create base uri from config.
     *
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
     *
     * @return string
     */
    private function createBaseUri(Config $config): string
    {
        return $config->getDomain() === '' ? '' : \sprintf('https://%s', $config->getDomain());
    }
}

\class_alias(
    Auth0IdentityServiceFactory::class,
    \StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory::class,
    false
);
