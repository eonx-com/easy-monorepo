<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\API\Management;

class ManagementApiClientFactory
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider
     */
    private $tokenProvider;

    /**
     * ManagementApiClientFactory constructor.
     *
     * @param \EonX\EasyIdentity\Implementations\Auth0\Config $config
     * @param \EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider $tokenProvider
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
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function create(): Management
    {
        return new Management($this->tokenProvider->getToken(), $this->config->getDomain());
    }
}
