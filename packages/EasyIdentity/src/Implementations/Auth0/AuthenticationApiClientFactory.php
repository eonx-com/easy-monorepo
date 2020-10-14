<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\API\Authentication;

final class AuthenticationApiClientFactory
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function create(): Authentication
    {
        return new Authentication(
            $this->config->getDomain(),
            $this->config->getClientId(),
            $this->config->getClientSecret()
        );
    }
}
