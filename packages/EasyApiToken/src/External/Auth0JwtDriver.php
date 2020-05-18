<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Auth0\SDK\Helpers\Cache\CacheHandler;
use Auth0\SDK\JWTVerifier;
use EonX\EasyApiToken\External\Auth0\TokenGenerator;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;

final class Auth0JwtDriver implements JwtDriverInterface
{
    /**
     * @var string[]
     */
    private $allowedAlgos;

    /**
     * @var string
     */
    private $audienceForEncode;

    /**
     * @var string[]
     */
    private $authorizedIss;

    /**
     * Replace with PSR cache on upgrade to PHP-Auth0 7.
     *
     * @var null|\Auth0\SDK\Helpers\Cache\CacheHandler
     */
    private $cache;

    /**
     * @var null|string|resource
     */
    private $privateKey;

    /**
     * @var string[]
     */
    private $validAudiences;

    /**
     * Auth0JwtDriver constructor.
     *
     * @param string[] $validAudiences
     * @param string[] $authorizedIss
     * @param null|string|resource $privateKey
     * @param null|string[] $allowedAlgos
     * @param \Auth0\SDK\Helpers\Cache\CacheHandler|null $cache Optional Cache handler.
     */
    public function __construct(
        array $validAudiences,
        array $authorizedIss,
        $privateKey = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null,
        ?CacheHandler $cache = null
    ) {
        $this->validAudiences = $validAudiences;
        $this->authorizedIss = $authorizedIss;
        $this->privateKey = $privateKey;
        $this->audienceForEncode = $audienceForEncode ?? (string)\reset($validAudiences);
        $this->allowedAlgos = $allowedAlgos ?? ['HS256', 'RS256'];
        $this->cache = $cache;
    }

    public function decode(string $token)
    {
        $verifier = new JWTVerifier([
            'authorized_iss' => $this->authorizedIss,
            'cache' => $this->cache,
            'client_secret' => $this->privateKey,
            'supported_algs' => $this->allowedAlgos,
            'valid_audiences' => $this->validAudiences,
        ]);

        return $verifier->verifyAndDecode($token);
    }

    /**
     * @param mixed[] $input
     */
    public function encode($input): string
    {
        /** @var string $privateKey */
        $privateKey = $this->privateKey;

        $generator = new TokenGenerator($this->audienceForEncode, $privateKey);

        return $generator->generate(
            $input['scopes'] ?? [],
            $input['roles'] ?? [],
            $input['sub'] ?? null,
            $input['lifetime'] ?? null
        );
    }
}
