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

    public function __construct(Config $config, ManagementTokenProvider $tokenProvider)
    {
        $this->config = $config;
        $this->tokenProvider = $tokenProvider;
    }

    public function create(): Management
    {
        return new Management($this->tokenProvider->getToken(), $this->config->getDomain());
    }
}
