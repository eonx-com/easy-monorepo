<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\API\Management;

class ManagementApiClientFactory
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementTokenProvider
     */
    private $tokenProvider;

    /**
     * ManagementApiClientFactory constructor.
     *
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementTokenProvider $tokenProvider
     */
    public function __construct(Config $config, ManagementTokenProvider $tokenProvider)
    {
        $this->config = $config;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Create Auth0 Management API Client.
     *
     * @return \Auth0\SDK\API\Management
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function create(): Management
    {
        return new Management($this->tokenProvider->getToken(), $this->config->getDomain());
    }
}

\class_alias(
    ManagementApiClientFactory::class,
    \StepTheFkUp\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory::class,
    false
);
