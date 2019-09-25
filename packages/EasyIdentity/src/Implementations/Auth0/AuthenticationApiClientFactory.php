<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\API\Authentication;

class AuthenticationApiClientFactory
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * AuthenticationApiClientFactory constructor.
     *
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
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
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
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

\class_alias(
    AuthenticationApiClientFactory::class,
    \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory::class,
    false
);
