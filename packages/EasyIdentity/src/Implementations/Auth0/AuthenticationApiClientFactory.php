<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\API\Authentication;

class AuthenticationApiClientFactory
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * AuthenticationApiClientFactory constructor.
     *
     * @param \EonX\EasyIdentity\Implementations\Auth0\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Create Auth0 Management API Client for given information.
     *
     * @return \Auth0\SDK\API\Authentication
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function create(): Authentication
    {
        return new Authentication(
            $this->config->getDomain(),
            $this->config->getClientId(),
            $this->config->getClientSecret()
        );
    }
}


