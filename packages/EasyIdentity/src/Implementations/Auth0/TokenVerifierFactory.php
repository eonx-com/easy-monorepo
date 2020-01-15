<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use Auth0\SDK\JWTVerifier;

class TokenVerifierFactory
{
    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * TokenVerifierFactory constructor.
     *
     * @param \EonX\EasyIdentity\Implementations\Auth0\Config $config
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
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
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
