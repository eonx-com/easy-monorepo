<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\JWTVerifier;

class TokenVerifierFactory
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * TokenVerifierFactory constructor.
     *
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Create Auth0 Token Verifier for given information.
     *
     * @return \Auth0\SDK\JWTVerifier
     *
     * @throws \Auth0\SDK\Exception\CoreException
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function create(): JWTVerifier
    {
        return new JWTVerifier([
            'client_secret' => $this->config->getClientSecret(),
            'supported_algs' => ['RS256'],
            'valid_audiences' => [$this->config->getClientId()],
            'authorized_iss' => [\sprintf('https://%s/', $this->config->getDomain())]
        ]);
    }
}

\class_alias(
    TokenVerifierFactory::class,
    \StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory::class,
    false
);
